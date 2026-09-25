<?php

namespace App\Http\Controllers\AccountMaster;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LedgerController extends Controller
{
    // List Ledgers page: shows all ledgers of the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $ledgers = Ledger::where('company_id', $companyId)
            ->with('group')
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($request->group, function ($query, $groupId) {
                $query->where('group_id', $groupId);
            })
            ->orderBy('name')
            ->get();

        // Every group of this company, used to fill the "Group" filter dropdown
        $groupOptions = Group::where('company_id', $companyId)->orderBy('name')->get();

        return view('account-master.ledger.list-ledger', compact('user', 'ledgers', 'groupOptions'));
    }

    // Add Ledger page: the form to create a new ledger
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $groupOptions = Group::where('company_id', $companyId)->orderBy('name')->get();

        return view('account-master.ledger.ledger', compact('user', 'groupOptions'));
    }

    // Save the new ledger
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ledgers')->where('company_id', $companyId),
            ],
            'group_id'        => 'required|exists:groups,id',
            'address'         => 'nullable|string',
            'contact'         => 'nullable|string|max:255',
            'gst_no'          => 'nullable|string|size:15',
            'pan_no'          => 'nullable|string|size:10',
            'gst_percent'     => 'nullable|numeric|min:0|max:100',
            'hsn_code'        => 'nullable|string|max:8',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_type'    => 'required|in:Dr,Cr',
        ], [
            'name.unique' => 'A ledger with this name already exists.',
        ]);

        $group = Group::where('company_id', $companyId)->findOrFail($request->group_id);

        Ledger::create(array_merge(
            ['company_id' => $companyId],
            $this->ledgerData($request, $group)
        ));

        return redirect()->route('list-ledger')->with('success', 'Ledger added successfully.');
    }

    // Edit Ledger page: the form to edit an existing ledger
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $ledger = Ledger::where('company_id', $companyId)->findOrFail($id);

        $groupOptions = Group::where('company_id', $companyId)->orderBy('name')->get();

        return view('account-master.ledger.edit-ledger', compact('user', 'ledger', 'groupOptions'));
    }

    // Save changes to an existing ledger
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $ledger = Ledger::where('company_id', $companyId)->findOrFail($id);

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('ledgers')->where('company_id', $companyId)->ignore($ledger->id),
            ],
            'group_id'        => 'required|exists:groups,id',
            'address'         => 'nullable|string',
            'contact'         => 'nullable|string|max:255',
            'gst_no'          => 'nullable|string|size:15',
            'pan_no'          => 'nullable|string|size:10',
            'gst_percent'     => 'nullable|numeric|min:0|max:100',
            'hsn_code'        => 'nullable|string|max:8',
            'opening_balance' => 'nullable|numeric|min:0',
            'opening_type'    => 'required|in:Dr,Cr',
        ], [
            'name.unique' => 'A ledger with this name already exists.',
        ]);

        $group = Group::where('company_id', $companyId)->findOrFail($request->group_id);

        $ledger->update($this->ledgerData($request, $group));

        return redirect()->route('list-ledger')->with('success', 'Ledger updated successfully.');
    }

    // Delete a ledger
    public function destroy($id)
    {
        $companyId = session('company_id');

        $ledger = Ledger::where('company_id', $companyId)->findOrFail($id);

        $ledger->delete();

        return redirect()->route('list-ledger')->with('success', 'Ledger deleted successfully.');
    }

    /**
     * The values to save for a ledger (used by both store and update).
     * Party groups (Sundry Debtors etc.) keep Address, Contact, GST No. and PAN No.;
     * every other group keeps GST % and HSN Code. The other form's fields are left empty.
     */
    private function ledgerData(Request $request, Group $group)
    {
        $isParty = $group->isPartyGroup();

        return [
            'group_id'        => $group->id,
            'name'            => $request->name,

            // Party form
            'address'         => $isParty ? $request->address : null,
            'contact'         => $isParty ? $request->contact : null,
            'gst_no'          => $isParty && $request->gst_no ? strtoupper($request->gst_no) : null,
            'pan_no'          => $isParty && $request->pan_no ? strtoupper($request->pan_no) : null,

            // Other form
            'gst_percent'     => $isParty ? null : $request->gst_percent,
            'hsn_code'        => $isParty ? null : $request->hsn_code,

            // Both forms
            'opening_balance' => $request->opening_balance ?? 0,
            'opening_type'    => $request->opening_type,
        ];
    }
}
