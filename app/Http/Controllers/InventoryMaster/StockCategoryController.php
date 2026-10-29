<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use App\Models\StockGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StockCategoryController extends Controller
{
    // List Stock Categories page: shows all stock categories of the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $stockCategories = StockCategory::where('company_id', $companyId)
            ->with('stockGroup')
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($request->stock_group, function ($query, $stockGroupId) {
                $query->where('stock_group_id', $stockGroupId);
            })
            ->orderBy('name')
            ->get();

        // Every stock group of this company, used to fill the "Stock Group" filter dropdown
        $stockGroupOptions = StockGroup::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-master.stock-category.list-stock-category', compact('user', 'stockCategories', 'stockGroupOptions'));
    }

    // Add Stock Category page: the form to create a new stock category
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $stockGroupOptions = StockGroup::where('company_id', session('company_id'))->orderBy('name')->get();

        return view('inventory-master.stock-category.stock-category', compact('user', 'stockGroupOptions'));
    }

    // Save the new stock category
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate($this->rules($companyId), $this->messages());

        StockCategory::create([
            'company_id'     => $companyId,
            'stock_group_id' => $request->stock_group_id,
            'name'           => $request->name,
        ]);

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

        $stockGroupOptions = StockGroup::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-master.stock-category.edit-stock-category', compact('user', 'stockCategory', 'stockGroupOptions'));
    }

    // Save changes to an existing stock category
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $stockCategory = StockCategory::where('company_id', $companyId)->findOrFail($id);

        $request->validate($this->rules($companyId, $stockCategory), $this->messages());

        $stockCategory->update([
            'name'           => $request->name,
            'stock_group_id' => $request->stock_group_id,
            // Categories no longer sit under other categories, so an old one is taken out from under its parent
            'parent_id'      => null,
        ]);

        return redirect()->route('stock-categories.index')->with('success', 'Stock category updated successfully.');
    }

    // Delete a stock category
    public function destroy($id)
    {
        $stockCategory = StockCategory::where('company_id', session('company_id'))->findOrFail($id);

        // Only old data can have these (categories used to sit under other categories): edit them to free this one
        if ($stockCategory->children()->exists()) {
            return redirect()->route('stock-categories.index')->with('error', 'Edit the stock categories that were under this category first.');
        }

        if ($stockCategory->items()->exists()) {
            return redirect()->route('stock-categories.index')->with('error', 'Move or delete the items in this stock category first.');
        }

        $stockCategory->delete();

        return redirect()->route('stock-categories.index')->with('success', 'Stock category deleted successfully.');
    }

    // Rules of the Add / Edit form. $stockCategory: the one being edited, so its own name doesn't count as taken
    private function rules($companyId, $stockCategory = null)
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stock_categories')->where('company_id', $companyId)->ignore($stockCategory?->id),
            ],
            // Every stock category belongs to a stock group of this company
            'stock_group_id' => ['required', Rule::exists('stock_groups', 'id')->where('company_id', $companyId)],
        ];
    }

    private function messages()
    {
        return [
            'name.unique'             => 'A stock category with this name already exists.',
            'stock_group_id.required' => 'Select the stock group this category belongs to.',
        ];
    }
}
