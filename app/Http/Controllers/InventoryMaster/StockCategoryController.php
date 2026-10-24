<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\StockCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
            ->with('parent')
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($request->under, function ($query, $parentId) {
                if ($parentId === 'primary') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $parentId);
                }
            })
            ->orderBy('name')
            ->get();

        // Every stock category of this company, used to fill the "Under" filter dropdown
        $underOptions = StockCategory::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-master.stock-category.list-stock-category', compact('user', 'stockCategories', 'underOptions'));
    }

    // Add Stock Category page: the form to create a new stock category
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $parentOptions = StockCategory::where('company_id', session('company_id'))->orderBy('name')->get();

        return view('inventory-master.stock-category.stock-category', compact('user', 'parentOptions'));
    }

    // Save the new stock category
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stock_categories')->where('company_id', $companyId),
            ],
            // Empty = Primary. Only a stock category of this company can be the parent
            'parent_id' => ['nullable', Rule::exists('stock_categories', 'id')->where('company_id', $companyId)],
        ], [
            'name.unique' => 'A stock category with this name already exists.',
        ]);

        StockCategory::create([
            'company_id' => $companyId,
            'parent_id'  => $request->parent_id,
            'name'       => $request->name,
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

        // A stock category can't be placed under itself, so leave it out of the options
        $parentOptions = StockCategory::where('company_id', $companyId)
            ->where('id', '!=', $stockCategory->id)
            ->orderBy('name')
            ->get();

        return view('inventory-master.stock-category.edit-stock-category', compact('user', 'stockCategory', 'parentOptions'));
    }

    // Save changes to an existing stock category
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $stockCategory = StockCategory::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stock_categories')->where('company_id', $companyId)->ignore($stockCategory->id),
            ],
            'parent_id' => ['nullable', Rule::exists('stock_categories', 'id')->where('company_id', $companyId)],
        ], [
            'name.unique' => 'A stock category with this name already exists.',
        ]);

        // A stock category can't go under itself or under one of its own sub-categories (that would make a loop),
        // so climb up from the new parent and make sure this category is not on the way
        $parent = StockCategory::where('company_id', $companyId)->find($request->parent_id);

        for ($above = $parent; $above; $above = $above->parent) {
            if ($above->id == $stockCategory->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A stock category can\'t be placed under itself or one of its own sub-categories.',
                ]);
            }
        }

        $stockCategory->update([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('stock-categories.index')->with('success', 'Stock category updated successfully.');
    }

    // Delete a stock category
    public function destroy($id)
    {
        $stockCategory = StockCategory::where('company_id', session('company_id'))->findOrFail($id);

        if ($stockCategory->children()->exists()) {
            return redirect()->route('stock-categories.index')->with('error', 'Move or delete the stock categories under this category first.');
        }

        if ($stockCategory->items()->exists()) {
            return redirect()->route('stock-categories.index')->with('error', 'Move or delete the items in this stock category first.');
        }

        $stockCategory->delete();

        return redirect()->route('stock-categories.index')->with('success', 'Stock category deleted successfully.');
    }
}
