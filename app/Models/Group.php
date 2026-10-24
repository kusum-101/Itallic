<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $guarded = [];

    // The company this group belongs to
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // The group this one sits under (null for a top-level/primary group)
    public function parent()
    {
        return $this->belongsTo(Group::class, 'parent_id');
    }

    // The sub-groups that sit under this group
    public function children()
    {
        return $this->hasMany(Group::class, 'parent_id');
    }

    // The ledgers that sit under this group
    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }

    // Default groups whose ledgers need party details (Address, Contact, GST No., PAN No.)
    public const PARTY_GROUPS = [
        'Capital Account',
        'Loans (Liability)',
        'Loans & Advances (Asset)',
        'Secured Loans',
        'Sundry Creditors',
        'Sundry Debtors',
        'Suspense A/c',
        'Unsecured Loans',
    ];

    /**
     * Does a ledger under this group need the party form?
     * A custom group follows the default group it sits under, so we climb
     * up through the parents until we reach a default group.
     */
    public function isPartyGroup()
    {
        $group = $this;

        while (!$group->is_primary && $group->parent) {
            $group = $group->parent;
        }

        return in_array($group->name, self::PARTY_GROUPS);
    }

    /**
     * Ids of the given default groups (e.g. ['Bank Accounts', 'Cash-in-Hand']) of a company,
     * and of every sub-group below them, at any depth.
     */
    public static function idsUnder($companyId, $groupNames)
    {
        $groups = self::where('company_id', $companyId)->get();

        // Start with the default groups themselves...
        $groupIds = $groups->where('is_primary', true)->whereIn('name', $groupNames)->pluck('id')->all();

        // ...then keep adding the groups that sit under them, one level at a time
        $found = $groupIds;

        while ($found) {
            $found = $groups->whereIn('parent_id', $found)->whereNotIn('id', $groupIds)->pluck('id')->all();
            $groupIds = array_merge($groupIds, $found);
        }

        return $groupIds;
    }

    /**
     * Create the default groups (Tally's standard chart of groups) for a
     * newly created company. Does nothing if the company already has groups.
     */
    public static function seedDefaults($companyId)
    {
        if (self::where('company_id', $companyId)->exists()) {
            return;
        }

        // Top-level groups: name => nature
        $primaryGroups = [
            'Capital Account'        => 'Liabilities',
            'Current Assets'         => 'Assets',
            'Current Liabilities'    => 'Liabilities',
            'Direct Expenses'        => 'Expenses',
            'Direct Incomes'         => 'Income',
            'Fixed Assets'           => 'Assets',
            'Indirect Expenses'      => 'Expenses',
            'Indirect Incomes'       => 'Income',
            'Investments'            => 'Assets',
            'Loans (Liability)'      => 'Liabilities',
            'Misc. Expenses (Asset)' => 'Assets',
            'Purchase Accounts'      => 'Expenses',
            'Sales Accounts'         => 'Income',
            'Suspense A/c'           => 'Liabilities',
        ];

        // Sub-groups: name => name of the parent group above
        $subGroups = [
            'Reserves & Surplus'       => 'Capital Account',
            'Bank Accounts'            => 'Current Assets',
            'Cash-in-Hand'             => 'Current Assets',
            'Deposits (Asset)'         => 'Current Assets',
            'Loans & Advances (Asset)' => 'Current Assets',
            'Stock-in-Hand'            => 'Current Assets',
            'Sundry Debtors'           => 'Current Assets',
            'Duties & Taxes'           => 'Current Liabilities',
            'Provisions'               => 'Current Liabilities',
            'Sundry Creditors'         => 'Current Liabilities',
            'Bank OD A/c'              => 'Loans (Liability)',
            'Secured Loans'            => 'Loans (Liability)',
            'Unsecured Loans'          => 'Loans (Liability)',
        ];

        // Insert the top-level groups first and remember each one's id
        $parentIds = [];

        foreach ($primaryGroups as $name => $nature) {
            $group = self::create([
                'company_id' => $companyId,
                'parent_id'  => null,
                'name'       => $name,
                'nature'     => $nature,
                'is_primary' => true,
            ]);

            $parentIds[$name] = $group->id;
        }

        // Now insert the sub-groups, pointing each one at its parent's id
        foreach ($subGroups as $name => $parentName) {
            self::create([
                'company_id' => $companyId,
                'parent_id'  => $parentIds[$parentName],
                'name'       => $name,
                'nature'     => $primaryGroups[$parentName],
                'is_primary' => true,
            ]);
        }
    }
}
