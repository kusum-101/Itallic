<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = [];

    // Units every new company starts with: symbol => [full name, decimal places, UQC]
    public const DEFAULTS = [
        'Nos'  => ['Numbers', 0, 'NOS'],
        'Pcs'  => ['Pieces', 0, 'PCS'],
        'Box'  => ['Box', 0, 'BOX'],
        'Kg'   => ['Kilogram', 3, 'KGS'],
        'Mtr'  => ['Metre', 2, 'MTR'],
        'Sqft' => ['Square Feet', 2, 'SQF'],
    ];

    // GST Unit Quantity Codes (UQC): code => name. A unit picks one of these (or none = Not Applicable)
    public const UQCS = [
        'BAG' => 'BAGS',
        'BAL' => 'BALE',
        'BDL' => 'BUNDLES',
        'BKL' => 'BUCKLES',
        'BOU' => 'BILLION OF UNITS',
        'BOX' => 'BOX',
        'BTL' => 'BOTTLES',
        'BUN' => 'BUNCHES',
        'CAN' => 'CANS',
        'CBM' => 'CUBIC METERS',
        'CCM' => 'CUBIC CENTIMETERS',
        'CMS' => 'CENTIMETERS',
        'CTN' => 'CARTONS',
        'DOZ' => 'DOZENS',
        'DRM' => 'DRUMS',
        'GGK' => 'GREAT GROSS',
        'GMS' => 'GRAMMES',
        'GRS' => 'GROSS',
        'GYD' => 'GROSS YARDS',
        'KGS' => 'KILOGRAMS',
        'KLR' => 'KILOLITRE',
        'KME' => 'KILOMETRE',
        'LTR' => 'LITRES',
        'MLT' => 'MILILITRE',
        'MTR' => 'METERS',
        'MTS' => 'METRIC TON',
        'NOS' => 'NUMBERS',
        'OTH' => 'OTHERS',
        'PAC' => 'PACKS',
        'PCS' => 'PIECES',
        'PRS' => 'PAIRS',
        'QTL' => 'QUINTAL',
        'ROL' => 'ROLLS',
        'SET' => 'SETS',
        'SQF' => 'SQUARE FEET',
        'SQM' => 'SQUARE METERS',
        'SQY' => 'SQUARE YARDS',
        'TBS' => 'TABLETS',
        'TGM' => 'TEN GROSS',
        'THD' => 'THOUSANDS',
        'TON' => 'TONNES',
        'TUB' => 'TUBES',
        'UGS' => 'US GALLONS',
        'UNT' => 'UNITS',
        'YDS' => 'YARDS',
    ];

    // The company this unit belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The items counted in this unit
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Compound unit only: the bigger unit, e.g. Box in "Box of 12 Pcs"
    public function firstUnit()
    {
        return $this->belongsTo(Unit::class, 'first_unit_id');
    }

    // Compound unit only: the smaller unit, e.g. Pcs in "Box of 12 Pcs"
    public function secondUnit()
    {
        return $this->belongsTo(Unit::class, 'second_unit_id');
    }

    public function isCompound()
    {
        return $this->type == 'Compound';
    }

    /**
     * The unit quantities are really counted (and saved) in.
     * A simple unit is itself; a compound unit counts in its second (smaller) unit,
     * so "1 Box 6 Pcs" of "Box of 12 Pcs" is saved as 18.
     */
    public function quantityUnit()
    {
        return $this->isCompound() ? $this->secondUnit : $this;
    }

    /**
     * Help text for typing a quantity of a compound unit (empty for a simple unit), e.g.
     * "1 Box = 12 Pcs, so type the quantity and rate in Pcs (e.g. 2 Box = 24 Pcs)."
     */
    public function quantityHint()
    {
        if (!$this->isCompound()) {
            return '';
        }

        $first = $this->firstUnit->symbol;
        $second = $this->secondUnit->symbol;
        $conversion = (float) $this->conversion;

        return "1 $first = $conversion $second, so type the quantity and rate in $second"
            . " (e.g. 2 $first = " . ($conversion * 2) . " $second).";
    }

    // Symbol of a compound unit, e.g. "Box of 12 Pcs". (float) drops the extra zeros: 12.0000 -> 12
    public static function compoundSymbol(Unit $first, $conversion, Unit $second)
    {
        return $first->symbol . ' of ' . (float) $conversion . ' ' . $second->symbol;
    }

    /**
     * A quantity of this unit as text.
     * Simple: "12.500 Kg" (with the unit's decimal places).
     * Compound: the quantity is in the second unit, shown split into both:
     *   18 of "Box of 12 Pcs" -> "1 Box 6 Pcs",  24 -> "2 Box",  5 -> "5 Pcs"
     */
    public function formatQuantity($quantity)
    {
        if (!$this->isCompound()) {
            return number_format($quantity, $this->decimals) . ' ' . $this->symbol;
        }

        $first = $this->firstUnit;
        $second = $this->secondUnit;
        $conversion = (float) $this->conversion;

        // Whole Boxes, and the Pcs left over
        $whole = floor($quantity / $conversion);
        $rest = round($quantity - $whole * $conversion, $second->decimals);

        // Rounding can leave a full Box in the rest, e.g. 11.99999 of 12
        if ($rest >= $conversion) {
            $whole++;
            $rest = 0;
        }

        $parts = [];

        if ($whole > 0) {
            $parts[] = number_format($whole) . ' ' . $first->symbol;
        }

        if ($rest > 0 || $whole == 0) {
            $parts[] = number_format($rest, $second->decimals) . ' ' . $second->symbol;
        }

        return implode(' ', $parts);
    }

    /**
     * Create the default units for a newly created company.
     * Does nothing if the company already has units.
     */
    public static function seedDefaults($companyId)
    {
        if (self::where('company_id', $companyId)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $symbol => [$name, $decimals, $uqc]) {
            self::create([
                'company_id' => $companyId,
                'symbol'     => $symbol,
                'name'       => $name,
                'uqc'        => $uqc,
                'decimals'   => $decimals,
            ]);
        }
    }
}
