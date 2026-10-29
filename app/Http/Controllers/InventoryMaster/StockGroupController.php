<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\StockGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StockGroupController extends Controller
{
    // List Stock Groups page: shows all stock groups of the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $stockGroups = StockGroup::where('company_id', $companyId)
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

        // Every stock group of this company, used to fill the "Under" filter dropdown
        $underOptions = StockGroup::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-master.stock-group.list-stock-group', compact('user', 'stockGroups', 'underOptions'));
    }

    // Add Stock Group page: the form to create a new stock group
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $parentOptions = StockGroup::where('company_id', session('company_id'))->orderBy('name')->get();

        return view('inventory-master.stock-group.stock-group', compact('user', 'parentOptions'));
    }

    // Save the new stock group
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stock_groups')->where('company_id', $companyId),
            ],
            // Empty = Primary. Only a stock group of this company can be the parent
            'parent_id' => ['nullable', Rule::exists('stock_groups', 'id')->where('company_id', $companyId)],
        ], [
            'name.unique' => 'A stock group with this name already exists.',
        ]);

        StockGroup::create([
            'company_id' => $companyId,
            'parent_id'  => $request->parent_id,
            'name'       => $request->name,
        ]);

        return redirect()->route('stock-groups.index')->with('success', 'Stock group added successfully.');
    }

    // Edit Stock Group page: the form to edit an existing stock group
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $stockGroup = StockGroup::where('company_id', $companyId)->findOrFail($id);

        // A stock group can't be placed under itself, so leave it out of the options
        $parentOptions = StockGroup::where('company_id', $companyId)
            ->where('id', '!=', $stockGroup->id)
            ->orderBy('name')
            ->get();

        return view('inventory-master.stock-group.edit-stock-group', compact('user', 'stockGroup', 'parentOptions'));
    }

    // Save changes to an existing stock group
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $stockGroup = StockGroup::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('stock_groups')->where('company_id', $companyId)->ignore($stockGroup->id),
            ],
            'parent_id' => ['nullable', Rule::exists('stock_groups', 'id')->where('company_id', $companyId)],
        ], [
            'name.unique' => 'A stock group with this name already exists.',
        ]);

        // A stock group can't go under itself or under one of its own sub-groups (that would make a loop),
        // so climb up from the new parent and make sure this group is not on the way
        $parent = StockGroup::where('company_id', $companyId)->find($request->parent_id);

        for ($above = $parent; $above; $above = $above->parent) {
            if ($above->id == $stockGroup->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A stock group can\'t be placed under itself or one of its own sub-groups.',
                ]);
            }
        }

        $stockGroup->update([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('stock-groups.index')->with('success', 'Stock group updated successfully.');
    }

    // Delete a stock group
    public function destroy($id)
    {
        $stockGroup = StockGroup::where('company_id', session('company_id'))->findOrFail($id);

        if ($stockGroup->children()->exists()) {
            return redirect()->route('stock-groups.index')->with('error', 'Move or delete the stock groups under this group first.');
        }

        if ($stockGroup->stockCategories()->exists()) {
            return redirect()->route('stock-groups.index')->with('error', 'Move or delete the stock categories in this stock group first.');
        }

        if ($stockGroup->items()->exists()) {
            return redirect()->route('stock-groups.index')->with('error', 'Move or delete the items under this stock group first.');
        }

        $stockGroup->delete();

        return redirect()->route('stock-groups.index')->with('success', 'Stock group deleted successfully.');
    }
}
