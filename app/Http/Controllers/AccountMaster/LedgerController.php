<?php

namespace App\Http\Controllers\AccountMaster;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\State;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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

        // Address fields: empty billing, shipping "Same as Billing" ticked
        $states = State::list();
        $billing = [];
        $shipping = [];
        $sameAsBilling = true;

        return view('account-master.ledger.ledger', compact('user', 'groupOptions', 'states', 'billing', 'shipping', 'sameAsBilling'));
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

        $addresses = $this->addressRows($request, $group, $companyId);

        // Ledger and its addresses are saved together: if one fails, nothing is saved
        DB::transaction(function () use ($request, $group, $companyId, $addresses) {
            $ledger = Ledger::create(array_merge(
                ['company_id' => $companyId],
                $this->ledgerData($request, $group)
            ));

            $ledger->addresses()->createMany($addresses);
        });

        return redirect()->route('ledgers.index')->with('success', 'Ledger added successfully.');
    }

    // Edit Ledger page: the form to edit an existing ledger
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $ledger = Ledger::where('company_id', $companyId)->with('addresses')->findOrFail($id);

        $groupOptions = Group::where('company_id', $companyId)->orderBy('name')->get();

        // Saved addresses for the address fields
        $states = State::list();
        $fields = ['address', 'state_id', 'district_id', 'city', 'pin_code'];

        $billingRow = $ledger->addresses->firstWhere('type', 'Billing');
        $billing = $billingRow ? $billingRow->only($fields) : [];

        $shipping = $ledger->addresses->where('type', 'Shipping')
            ->map(fn ($row) => $row->only($fields))
            ->values()
            ->all();

        // "Same as Billing" is ticked when there is no shipping address yet,
        // or the only one is a copy of the billing address
        $sameAsBilling = !$shipping || (count($shipping) == 1 && $billing == $shipping[0]);

        if ($sameAsBilling) {
            $shipping = [];
        }

        return view('account-master.ledger.edit-ledger', compact('user', 'ledger', 'groupOptions', 'states', 'billing', 'shipping', 'sameAsBilling'));
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

        $addresses = $this->addressRows($request, $group, $companyId);

        // Old addresses are replaced by the ones in the form, together with the ledger
        DB::transaction(function () use ($request, $group, $ledger, $addresses) {
            $ledger->update($this->ledgerData($request, $group));

            $ledger->addresses()->delete();
            $ledger->addresses()->createMany($addresses);
        });

        return redirect()->route('ledgers.index')->with('success', 'Ledger updated successfully.');
    }

    // Delete a ledger
    public function destroy($id)
    {
        $companyId = session('company_id');

        $ledger = Ledger::where('company_id', $companyId)->findOrFail($id);

        // A ledger that is on the Debit or Credit side of any voucher has to stay
        $used = Voucher::where('dr_ledger_id', $ledger->id)->orWhere('cr_ledger_id', $ledger->id)->exists();

        if ($used) {
            return redirect()->route('ledgers.index')->with('error', 'This ledger is used in vouchers, so it can\'t be deleted.');
        }

        $ledger->delete();

        return redirect()->route('ledgers.index')->with('success', 'Ledger deleted successfully.');
    }

    /**
     * The values to save for a ledger (used by both store and update).
     * Party groups (Sundry Debtors etc.) keep Contact, GST No. and PAN No.;
     * every other group keeps GST % and HSN Code. The other form's fields are left empty.
     */
    private function ledgerData(Request $request, Group $group)
    {
        $isParty = $group->isPartyGroup();

        $gstNo = $request->gst_no ? strtoupper($request->gst_no) : null;
        $panNo = $request->pan_no ? strtoupper($request->pan_no) : null;

        // Characters 3 to 12 of a GST No. are the PAN, so a full GST No. decides the PAN
        if ($gstNo && strlen($gstNo) == 15) {
            $panNo = substr($gstNo, 2, 10);
        }

        return [
            'group_id'        => $group->id,
            'name'            => $request->name,

            // Party form (addresses are saved separately, see addressRows)
            'contact'         => $isParty ? $request->contact : null,
            'gst_no'          => $isParty ? $gstNo : null,
            'pan_no'          => $isParty ? $panNo : null,

            // Other form
            'gst_percent'     => $isParty ? null : $request->gst_percent,
            'hsn_code'        => $isParty ? null : $request->hsn_code,

            // Both forms
            'opening_balance' => $request->opening_balance ?? 0,
            'opening_type'    => $request->opening_type,
        ];
    }

    /**
     * Check the Billing / Shipping address fields and return the rows to save.
     * Only party groups have addresses. A block left fully empty is skipped;
     * a block that is partly filled must be filled completely. With "Same as
     * Billing" ticked (or no shipping address filled), the billing address is
     * saved as the shipping one too.
     */
    private function addressRows(Request $request, Group $group, $companyId)
    {
        if (!$group->isPartyGroup()) {
            return [];
        }

        // The filled blocks: error key => [type, values]
        $blocks = [];

        $billing = $request->input('billing');
        if ($this->isFilled($billing)) {
            $blocks['billing'] = ['Billing', $billing];
        }

        $hasShipping = false;

        if (!$request->boolean('same_as_billing')) {
            foreach ((array) $request->input('shipping', []) as $i => $row) {
                if ($this->isFilled($row)) {
                    $blocks["shipping.$i"] = ['Shipping', $row];
                    $hasShipping = true;
                }
            }
        }

        // "Same as Billing" ticked, or no shipping address filled: billing is the shipping address too
        if (!$hasShipping && isset($blocks['billing'])) {
            $blocks['billing-copy'] = ['Shipping', $billing];
        }

        // Every field is required in a filled block
        $labels = [
            'address'     => 'Address',
            'state_id'    => 'State',
            'district_id' => 'District',
            'city'        => 'City',
            'pin_code'    => 'Pin Code',
        ];

        $errors = [];

        foreach ($blocks as $key => [$type, $row]) {
            if ($key == 'billing-copy') {
                continue;
            }

            foreach ($labels as $field => $label) {
                if (trim($row[$field] ?? '') === '') {
                    $errors["$key.$field"] = "$label is required.";
                }
            }

            $pin = trim($row['pin_code'] ?? '');
            if ($pin !== '' && !preg_match('/^[0-9]{6}$/', $pin)) {
                $errors["$key.pin_code"] = 'Pin Code must be 6 digits.';
            }

            if (mb_strlen(trim($row['city'] ?? '')) > 100) {
                $errors["$key.city"] = 'City may not be longer than 100 characters.';
            }

            // The district must be in the state
            if (!empty($row['state_id']) && !empty($row['district_id'])) {
                $valid = DB::table('districts')
                    ->where('id', $row['district_id'])
                    ->where('state_id', $row['state_id'])
                    ->exists();

                if (!$valid) {
                    $errors["$key.district_id"] = 'Please select the State and District again.';
                }
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $rows = [];

        foreach ($blocks as [$type, $row]) {
            $rows[] = [
                'company_id'  => $companyId,
                'type'        => $type,
                'address'     => trim($row['address']),
                'state_id'    => $row['state_id'],
                'district_id' => $row['district_id'],
                'city'        => trim($row['city']),
                'pin_code'    => trim($row['pin_code']),
            ];
        }

        return $rows;
    }

    // Has the user typed or picked anything in this address block?
    private function isFilled($row)
    {
        if (!is_array($row)) {
            return false;
        }

        foreach (['address', 'state_id', 'district_id', 'city', 'pin_code'] as $field) {
            if (trim($row[$field] ?? '') !== '') {
                return true;
            }
        }

        return false;
    }
}
