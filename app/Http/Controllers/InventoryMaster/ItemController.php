<?php

namespace App\Http\Controllers\InventoryMaster;

use App\Http\Controllers\Controller;
use App\Models\Godown;
use App\Models\Item;
use App\Models\StockCategory;
use App\Models\StockGroup;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    // List Items page: shows all items of the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $items = Item::where('company_id', $companyId)
            ->with('unit.firstUnit', 'unit.secondUnit', 'stockGroup', 'stockCategory')
            // Total opening quantity and value of each item (adds openings_sum_quantity, openings_sum_value)
            ->withSum('openings', 'quantity')
            ->withSum('openings', 'value')
            ->when($request->name, function ($query, $name) {
                $query->where('name', 'like', "%{$name}%");
            })
            ->when($request->stock_group, function ($query, $stockGroupId) {
                if ($stockGroupId === 'primary') {
                    $query->whereNull('stock_group_id');
                } else {
                    $query->where('stock_group_id', $stockGroupId);
                }
            })
            ->when($request->stock_category, function ($query, $stockCategoryId) {
                $query->where('stock_category_id', $stockCategoryId);
            })
            ->orderBy('name')
            ->get();

        // Filter dropdowns
        $stockGroupOptions = StockGroup::where('company_id', $companyId)->orderBy('name')->get();
        $stockCategoryOptions = StockCategory::where('company_id', $companyId)->orderBy('name')->get();

        return view('inventory-master.item.list-item', compact('user', 'items', 'stockGroupOptions', 'stockCategoryOptions'));
    }

    /**
     * Add Item page: the form to create a new item.
     * With ?copy=5 (the "Copy" option of an item, or "Copy details from" on this page)
     * the form starts filled with item 5's details and opening stock, like a copy of it.
     */
    public function create(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $options = $this->formOptions($companyId);

        // No saved item yet: the form (shared with the Edit page) is for a new item
        $item = null;

        // The item to copy from: only one of this company (anything else is ignored)
        $copyFrom = $request->copy
            ? Item::where('company_id', $companyId)->with('openings')->find($request->copy)
            : null;

        // Its opening stock, or one empty row
        $openings = $copyFrom ? $this->openingValues($copyFrom) : [];

        // "Copy details from" dropdown: every item of this company
        $copyOptions = Item::where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);

        return view('inventory-master.item.item', array_merge(compact('user', 'item', 'copyFrom', 'copyOptions', 'openings'), $options));
    }

    // Save the new item
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $this->validateItem($request, $companyId);

        $unit = Unit::where('company_id', $companyId)->findOrFail($request->unit_id);

        $openings = $this->openingRows($request, $unit, $companyId);

        // Item and its opening stock are saved together: if one fails, nothing is saved
        DB::transaction(function () use ($request, $companyId, $openings) {
            $item = Item::create(array_merge(
                ['company_id' => $companyId],
                $this->itemData($request)
            ));

            $item->openings()->createMany($openings);
        });

        return redirect()->route('items.index')->with('success', 'Item added successfully.');
    }

    // Edit Item page: the form to edit an existing item
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $item = Item::where('company_id', $companyId)->with('openings')->findOrFail($id);

        $options = $this->formOptions($companyId);

        $openings = $this->openingValues($item);

        // Editing, not copying
        $copyFrom = null;

        return view('inventory-master.item.item', array_merge(compact('user', 'item', 'copyFrom', 'openings'), $options));
    }

    // Save changes to an existing item
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $item = Item::where('company_id', $companyId)->findOrFail($id);

        $this->validateItem($request, $companyId, $item);

        // Vouchers keep this item's quantities in its unit (e.g. 18 Pcs): with another unit
        // those numbers would mean something else, so the unit stays once vouchers use the item
        if ($request->unit_id != $item->unit_id && $item->stockLines()->exists()) {
            throw ValidationException::withMessages([
                'unit_id' => 'This item is used in inventory vouchers, so its unit can\'t be changed.',
            ]);
        }

        $unit = Unit::where('company_id', $companyId)->findOrFail($request->unit_id);

        $openings = $this->openingRows($request, $unit, $companyId);

        // Old opening rows are replaced by the ones in the form, together with the item
        DB::transaction(function () use ($request, $item, $openings) {
            $item->update($this->itemData($request));

            $item->openings()->delete();
            $item->openings()->createMany($openings);
        });

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    // Delete an item (its opening stock goes with it: onDelete cascade)
    public function destroy($id)
    {
        $item = Item::where('company_id', session('company_id'))->findOrFail($id);

        // An item that is in an inventory voucher (Material Inward, ...) has to stay
        if ($item->stockLines()->exists()) {
            return redirect()->route('items.index')->with('error', 'This item is used in inventory vouchers, so it can\'t be deleted.');
        }

        $item->delete();

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    // An item's saved opening stock, as the rows of the form. (float) drops the extra zeros: 10.0000 -> 10
    private function openingValues(Item $item)
    {
        return $item->openings->map(function ($row) {
            return [
                'godown_id' => $row->godown_id,
                'quantity'  => (float) $row->quantity,
                'rate'      => (float) $row->rate,
            ];
        })->all();
    }

    // The dropdown lists of the Add / Edit form
    private function formOptions($companyId)
    {
        return [
            // Simple units first, then compound ones (with their two units, for the Opening Stock script)
            'units'           => Unit::where('company_id', $companyId)->with('firstUnit', 'secondUnit')
                ->orderBy('type', 'desc')->orderBy('symbol')->get(),
            'stockGroups'     => StockGroup::where('company_id', $companyId)->orderBy('name')->get(),
            'stockCategories' => StockCategory::where('company_id', $companyId)->orderBy('name')->get(),
            'godowns'         => Godown::where('company_id', $companyId)->orderBy('name')->get(),
        ];
    }

    // Check the item's own fields (used by both store and update). $item is given when editing
    private function validateItem(Request $request, $companyId, $item = null)
    {
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('items')->where('company_id', $companyId)->ignore($item->id ?? null),
            ],
            // Only this company's unit / stock group / category can be picked
            'unit_id'           => ['required', Rule::exists('units', 'id')->where('company_id', $companyId)],
            'stock_group_id'    => ['nullable', Rule::exists('stock_groups', 'id')->where('company_id', $companyId)],
            'stock_category_id' => ['nullable', Rule::exists('stock_categories', 'id')->where('company_id', $companyId)],
            'hsn_code'          => 'nullable|string|max:8',
            'gst_percent'       => 'nullable|numeric|min:0|max:100',
        ], [
            'name.unique'      => 'An item with this name already exists.',
            'unit_id.required' => 'Please select the unit.',
        ]);
    }

    // The values to save for an item (used by both store and update)
    private function itemData(Request $request)
    {
        // An item in a stock category goes under that category's stock group (the form fills it in too).
        // A category made before categories had groups has none: then the group picked on the form is kept
        $categoryGroupId = StockCategory::whereKey($request->stock_category_id)->value('stock_group_id');

        return [
            'name'              => $request->name,
            'unit_id'           => $request->unit_id,
            'stock_group_id'    => $categoryGroupId ?? $request->stock_group_id,
            'stock_category_id' => $request->stock_category_id,
            'hsn_code'          => $request->hsn_code,
            'gst_percent'       => $request->gst_percent,
        ];
    }

    /**
     * Check the Opening Stock rows of the form and return the rows to save.
     * A row left fully empty is skipped. In a filled row the Godown and
     * Quantity are needed; Rate can be left empty (0). Value = Quantity x Rate,
     * worked out here, not taken from the form.
     */
    private function openingRows(Request $request, Unit $unit, $companyId)
    {
        $godownIds = Godown::where('company_id', $companyId)->pluck('id')->all();

        // The unit the quantity is typed in: Kg for Kg, Pcs for "Box of 12 Pcs"
        $quantityUnit = $unit->quantityUnit();

        $errors = [];
        $rows = [];
        $usedGodowns = [];

        foreach ((array) $request->input('openings', []) as $i => $row) {
            $godownId = trim($row['godown_id'] ?? '');
            $quantity = trim($row['quantity'] ?? '');
            $rate = trim($row['rate'] ?? '');

            // Fully empty row: skip it
            if ($godownId === '' && $quantity === '' && $rate === '') {
                continue;
            }

            if (!in_array($godownId, $godownIds)) {
                $errors["openings.$i.godown_id"] = 'Please select the godown.';
            } elseif (in_array($godownId, $usedGodowns)) {
                $errors["openings.$i.godown_id"] = 'This godown is already in another row.';
            }
            $usedGodowns[] = $godownId;

            if (!is_numeric($quantity) || $quantity <= 0) {
                $errors["openings.$i.quantity"] = 'Quantity must be more than 0.';
            } elseif ($quantity > 99999999999) {
                // The biggest quantity the column can hold
                $errors["openings.$i.quantity"] = 'Quantity is too big.';
            } elseif (round($quantity, $quantityUnit->decimals) != $quantity) {
                // e.g. 2.5 Nos: Nos allows no decimal places
                $errors["openings.$i.quantity"] = $quantityUnit->symbol . ' allows only ' . $quantityUnit->decimals . ' digit(s) after the point.';
            }

            if ($rate !== '' && (!is_numeric($rate) || $rate < 0)) {
                $errors["openings.$i.rate"] = 'Rate must be 0 or more.';
            }

            // Stop here for this row if anything is wrong with it
            if (isset($errors["openings.$i.godown_id"]) || isset($errors["openings.$i.quantity"]) || isset($errors["openings.$i.rate"])) {
                continue;
            }

            $rate = $rate === '' ? 0 : round($rate, 2);
            $value = round($quantity * $rate, 2);

            // 9999999999999.99 is the biggest amount the columns can hold
            if ($value > 9999999999999.99) {
                $errors["openings.$i.rate"] = 'Quantity x Rate is too big.';
                continue;
            }

            $rows[] = [
                'godown_id' => $godownId,
                'quantity'  => $quantity,
                'rate'      => $rate,
                'value'     => $value,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $rows;
    }
}
