<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GodownController extends Controller
{
    // List Godowns page: shows all godowns of the current company, with a name filter
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $godowns = Godown::where('company_id', session('company_id'))
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->orderBy('name')
            ->get();

        return view('inventory-master.godown.list-godown', compact('user', 'godowns'));
    }

    // Add Godown page: the form to create a new godown
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        return view('inventory-master.godown.godown', compact('user'));
    }

    // Save the new godown
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('godowns')->where('company_id', $companyId),
            ],
            'address' => 'nullable|string|max:1000',
        ], [
            'name.unique' => 'A godown with this name already exists.',
        ]);

        Godown::create([
            'company_id' => $companyId,
            'name'       => $request->name,
            'address'    => $request->address,
            'is_primary' => false,
        ]);

        return redirect()->route('godowns.index')->with('success', 'Godown added successfully.');
    }

    // Edit Godown page: the form to edit an existing godown
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $godown = Godown::where('company_id', session('company_id'))->findOrFail($id);

        return view('inventory-master.godown.edit-godown', compact('user', 'godown'));
    }

    // Save changes to an existing godown (Main Location too: it can be renamed)
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $godown = Godown::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('godowns')->where('company_id', $companyId)->ignore($godown->id),
            ],
            'address' => 'nullable|string|max:1000',
        ], [
            'name.unique' => 'A godown with this name already exists.',
        ]);

        $godown->update([
            'name'    => $request->name,
            'address' => $request->address,
        ]);

        return redirect()->route('godowns.index')->with('success', 'Godown updated successfully.');
    }

    // Delete a godown
    public function destroy($id)
    {
        $godown = Godown::where('company_id', session('company_id'))->findOrFail($id);

        // Every company keeps its default godown
        if ($godown->is_primary) {
            return redirect()->route('godowns.index')->with('error', 'The default godown cannot be deleted.');
        }

        // A godown that has stock in it has to stay
        if ($godown->openings()->exists()) {
            return redirect()->route('godowns.index')->with('error', 'This godown has opening stock of items, so it can\'t be deleted.');
        }

        if ($godown->stockLines()->exists()) {
            return redirect()->route('godowns.index')->with('error', 'This godown is used in inventory vouchers, so it can\'t be deleted.');
        }

        $godown->delete();

        return redirect()->route('godowns.index')->with('success', 'Godown deleted successfully.');
    }
}
