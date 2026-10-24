<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UnitController extends Controller
{
    // List Units page: shows all units of the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $units = Unit::where('company_id', session('company_id'))
            ->with('secondUnit')
            // The search box looks in both the symbol (Kg) and the full name (Kilogram)
            ->when($request->name, function ($query, $name) {
                $query->where(function ($query) use ($name) {
                    $query->where('symbol', 'like', "%{$name}%")->orWhere('name', 'like', "%{$name}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->orderBy('type', 'desc')   // Simple first, then Compound
            ->orderBy('symbol')
            ->get();

        return view('inventory-master.unit.list-unit', compact('user', 'units'));
    }

    // Add Unit page: the form to create a new unit
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        // First / Second Unit dropdowns of a compound unit
        $simpleUnits = $this->simpleUnits(session('company_id'));

        return view('inventory-master.unit.unit', compact('user', 'simpleUnits'));
    }

    // Save the new unit
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate([
            'type' => 'required|in:Simple,Compound',
        ]);

        $data = $request->type == 'Compound'
            ? $this->compoundData($request, $companyId)
            : $this->simpleData($request, $companyId);

        Unit::create(array_merge(['company_id' => $companyId, 'type' => $request->type], $data));

        return redirect()->route('units.index')->with('success', 'Unit added successfully.');
    }

    // Edit Unit page: the form to edit an existing unit
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $unit = Unit::where('company_id', $companyId)->findOrFail($id);

        $simpleUnits = $this->simpleUnits($companyId);

        return view('inventory-master.unit.edit-unit', compact('user', 'unit', 'simpleUnits'));
    }

    // Save changes to an existing unit. Its type (Simple / Compound) stays as it is
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $unit = Unit::where('company_id', $companyId)->findOrFail($id);

        if ($unit->isCompound()) {
            $data = $this->compoundData($request, $companyId, $unit);

            // Items keep their stock in the second unit (e.g. 18 Pcs). With another
            // second unit that number would mean something else, so it can't change then.
            if ($data['second_unit_id'] != $unit->second_unit_id && $unit->items()->exists()) {
                throw ValidationException::withMessages([
                    'second_unit_id' => 'Items use this unit, so its second unit can\'t be changed.',
                ]);
            }

            $unit->update($data);
        } else {
            $unit->update($this->simpleData($request, $companyId, $unit));

            // Compound units made from this one have its symbol in their name ("Box of 12 Pcs")
            // and its decimal places (when it is their second unit): update them too
            $compounds = Unit::where('company_id', $companyId)
                ->where(function ($query) use ($unit) {
                    $query->where('first_unit_id', $unit->id)->orWhere('second_unit_id', $unit->id);
                })
                ->with('firstUnit', 'secondUnit')
                ->get();

            foreach ($compounds as $compound) {
                $compound->update([
                    'symbol'   => Unit::compoundSymbol($compound->firstUnit, $compound->conversion, $compound->secondUnit),
                    'decimals' => $compound->secondUnit->decimals,
                ]);
            }
        }

        return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
    }

    // Delete a unit
    public function destroy($id)
    {
        $unit = Unit::where('company_id', session('company_id'))->findOrFail($id);

        // A unit that an item is counted in has to stay (same as ledgers used in vouchers)
        if ($unit->items()->exists()) {
            return redirect()->route('units.index')->with('error', 'This unit is used by items, so it can\'t be deleted.');
        }

        // A simple unit that is part of a compound unit has to stay too
        $inCompound = Unit::where('first_unit_id', $unit->id)->orWhere('second_unit_id', $unit->id)->exists();

        if ($inCompound) {
            return redirect()->route('units.index')->with('error', 'This unit is part of a compound unit, so it can\'t be deleted.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('success', 'Unit deleted successfully.');
    }

    // This company's simple units, for the First / Second Unit dropdowns
    private function simpleUnits($companyId)
    {
        return Unit::where('company_id', $companyId)->where('type', 'Simple')->orderBy('symbol')->get();
    }

    // Check a Simple unit's fields and return the values to save. $unit is given when editing
    private function simpleData(Request $request, $companyId, $unit = null)
    {
        $request->validate([
            'symbol' => [
                'required', 'string', 'max:20',
                Rule::unique('units')->where('company_id', $companyId)->ignore($unit->id ?? null),
            ],
            'name'     => 'nullable|string|max:255',
            // Empty = Not Applicable
            'uqc'      => ['nullable', Rule::in(array_keys(Unit::UQCS))],
            'decimals' => 'required|integer|min:0|max:4',
        ], [
            'symbol.unique' => 'A unit with this symbol already exists.',
        ]);

        return [
            'symbol'   => $request->symbol,
            'name'     => $request->name,
            'uqc'      => $request->uqc,
            'decimals' => $request->decimals,
        ];
    }

    /**
     * Check a Compound unit's fields and return the values to save. $unit is given when editing.
     * Its symbol is made from the two units, e.g. "Box of 12 Pcs". It has no formal name
     * or UQC of its own, and its decimal places are the second unit's.
     */
    private function compoundData(Request $request, $companyId, $unit = null)
    {
        // Only this company's simple units can be joined
        $simpleUnit = Rule::exists('units', 'id')->where('company_id', $companyId)->where('type', 'Simple');

        $request->validate([
            'first_unit_id'  => ['required', $simpleUnit],
            // 4 digits after the point, like the conversion column
            'conversion'     => 'required|numeric|gt:0|max:99999999999|decimal:0,4',
            'second_unit_id' => ['required', $simpleUnit, 'different:first_unit_id'],
        ], [
            'first_unit_id.required'   => 'Please select the first unit.',
            'second_unit_id.required'  => 'Please select the second unit.',
            'second_unit_id.different' => 'The first and second unit can\'t be the same.',
            'conversion.gt'            => 'Conversion must be more than 0.',
        ]);

        $first = Unit::find($request->first_unit_id);
        $second = Unit::find($request->second_unit_id);
        $symbol = Unit::compoundSymbol($first, $request->conversion, $second);

        // The same compound unit can't be made twice
        $exists = Unit::where('company_id', $companyId)
            ->where('symbol', $symbol)
            ->where('id', '!=', $unit->id ?? 0)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'second_unit_id' => 'The unit "' . $symbol . '" already exists.',
            ]);
        }

        return [
            'symbol'         => $symbol,
            'name'           => null,
            'uqc'            => null,
            'decimals'       => $second->decimals,
            'first_unit_id'  => $first->id,
            'conversion'     => $request->conversion,
            'second_unit_id' => $second->id,
        ];
    }
}
