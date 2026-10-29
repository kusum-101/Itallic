<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockCategory;
use App\Models\StockGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockCategoryController extends Controller
{
    // List Stock Categories page: the categories of the current company as a tree
    // (each category with its sub-categories under it, opened with the arrow), with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        // A sub-category is in the same stock group as its parent, so this filter keeps whole branches
        $categories = StockCategory::where('company_id', $companyId)
            ->with('stockGroup')
            ->withCount('items')
            ->when($request->stock_group, function ($query, $stockGroupId) {
                $query->where('stock_group_id', $stockGroupId);
            })
            ->orderBy('name')
            ->get();

        // The tree as one flat list of rows, each with its depth (0 = top level), in display order.
        // Top level: categories directly under their stock group (or whose parent is not in the list)
        $ids = $categories->pluck('id')->all();
        $rows = [];

        foreach ($categories->filter(fn ($category) => !in_array($category->parent_id, $ids)) as $category) {
            $this->addTreeRows($rows, $category, $categories, 0);
        }

        // Name search: the matching categories, with the categories above them so the tree still reads right
        if ($request->name) {
            $byId = $categories->keyBy('id');
            $keep = [];

            foreach ($categories as $category) {
                if (stripos($category->name, $request->name) !== false) {
                    for ($above = $category; $above; $above = $byId->get($above->parent_id)) {
                        $keep[$above->id] = true;
                    }
                }
            }

            $rows = array_values(array_filter($rows, fn ($row) => isset($keep[$row['category']->id])));
        }

        // Every stock group of this company, used to fill the "Stock Group" filter dropdown
        $stockGroupOptions = StockGroup::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-master.stock-category.list-stock-category', compact('user', 'rows', 'stockGroupOptions'));
    }

    // Add a category and, under it, all its sub-categories (at any depth) to the rows of the list
    private function addTreeRows(&$rows, $category, $categories, $depth)
    {
        $children = $categories->where('parent_id', $category->id)->values();

        $rows[] = [
            'category' => $category,
            // The row above it in the tree (null at the top level)
            'parentId' => $depth ? $category->parent_id : null,
            'depth'    => $depth,
            'children' => $children->count(),
        ];

        foreach ($children as $child) {
            $this->addTreeRows($rows, $child, $categories, $depth + 1);
        }
    }

    // Add Stock Category page: the form to create a new stock category
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $underOptions = StockCategory::underOptions(session('company_id'));

        return view('inventory-master.stock-category.stock-category', compact('user', 'underOptions'));
    }

    // Save the new stock category
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate($this->rules($companyId), $this->messages());

        StockCategory::create(array_merge(
            ['company_id' => $companyId, 'name' => $request->name],
            $this->placeUnder($request->under, $companyId)
        ));

        return redirect()->route('stock-categories.index')->with('success', 'Stock category added successfully.');
    }

    // Edit Stock Category page: the form to edit an existing stock category
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $stockCategory = StockCategory::where('company_id', $companyId)->findOrFail($id);

        // It can't go under itself or one of its own sub-categories, so those are left out
        $underOptions = StockCategory::underOptions($companyId, $stockCategory);

        return view('inventory-master.stock-category.edit-stock-category', compact('user', 'stockCategory', 'underOptions'));
    }

    // Save changes to an existing stock category
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $stockCategory = StockCategory::where('company_id', $companyId)->findOrFail($id);

        $request->validate($this->rules($companyId, $stockCategory), $this->messages());

        $place = $this->placeUnder($request->under, $companyId);

        // Under itself or one of its own sub-categories would make a loop
        if (in_array($place['parent_id'], array_merge([$stockCategory->id], $stockCategory->descendantIds()))) {
            throw ValidationException::withMessages([
                'under' => 'A stock category can\'t be placed under itself or one of its own sub-categories.',
            ]);
        }

        DB::transaction(function () use ($stockCategory, $request, $place) {
            $stockCategory->update(array_merge(['name' => $request->name], $place));

            // Moved to another stock group: its sub-categories, and the items in all of them, move along
            $categoryIds = array_merge([$stockCategory->id], $stockCategory->descendantIds());

            StockCategory::whereIn('id', $categoryIds)->update(['stock_group_id' => $place['stock_group_id']]);
            Item::whereIn('stock_category_id', $categoryIds)->update(['stock_group_id' => $place['stock_group_id']]);
        });

        return redirect()->route('stock-categories.index')->with('success', 'Stock category updated successfully.');
    }

    // Delete a stock category
    public function destroy($id)
    {
        $stockCategory = StockCategory::where('company_id', session('company_id'))->findOrFail($id);

        if ($stockCategory->children()->exists()) {
            return redirect()->route('stock-categories.index')->with('error', 'Move or delete the sub-categories under this category first.');
        }

        if ($stockCategory->items()->exists()) {
            return redirect()->route('stock-categories.index')->with('error', 'Move or delete the items in this stock category first.');
        }

        $stockCategory->delete();

        return redirect()->route('stock-categories.index')->with('success', 'Stock category deleted successfully.');
    }

    /**
     * Where the "Under" choice puts the category:
     *   "group:2"    -> directly under stock group 2
     *   "category:5" -> a sub-category of category 5, in that category's stock group
     */
    private function placeUnder($under, $companyId)
    {
        [$type, $id] = explode(':', $under);

        if ($type == 'group') {
            $group = StockGroup::where('company_id', $companyId)->find($id);

            if (!$group) {
                throw ValidationException::withMessages(['under' => 'Select a stock group or category of this company.']);
            }

            return ['stock_group_id' => $group->id, 'parent_id' => null];
        }

        $parent = StockCategory::where('company_id', $companyId)->find($id);

        if (!$parent) {
            throw ValidationException::withMessages(['under' => 'Select a stock group or category of this company.']);
        }

        // A category made before categories had groups: it needs its group first
        if (!$parent->stock_group_id) {
            throw ValidationException::withMessages(['under' => $parent->name . ' has no stock group yet. Edit it and pick its group first.']);
        }

        return ['stock_group_id' => $parent->stock_group_id, 'parent_id' => $parent->id];
    }

    // Rules of the Add / Edit form. $stockCategory: the one being edited, so its own name doesn't count as taken
    private function rules($companyId, $stockCategory = null)
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stock_categories')->where('company_id', $companyId)->ignore($stockCategory?->id),
            ],
            // A stock group ("group:2") or, for a sub-category, a category ("category:5")
            'under' => ['required', 'regex:/^(group|category):[0-9]+$/'],
        ];
    }

    private function messages()
    {
        return [
            'name.unique'    => 'A stock category with this name already exists.',
            'under.required' => 'Select the stock group (or the category, for a sub-category) this goes under.',
            'under.regex'    => 'Select a stock group or category from the list.',
        ];
    }
}
