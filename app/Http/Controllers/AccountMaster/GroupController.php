<?php

namespace App\Http\Controllers\AccountMaster;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller
{
    // List Groups page: shows all groups of the current company, with filters
    public function index(Request $request)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $groups = Group::where('company_id', $companyId)
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
            ->when($request->nature, function ($query, $nature) {
                $query->where('nature', $nature);
            })
            ->orderBy('name')
            ->get();

        // Every group of this company, used to fill the "Under" filter dropdown
        $underOptions = Group::where('company_id', $companyId)->orderBy('name')->get();

        return view('account-master.group.list-group', compact('user', 'groups', 'underOptions'));
    }

    /**
     * Chart of Accounts page: every group of the company as a tree, the top-level
     * groups first (Liabilities, Assets, Income, Expenses) and each one's sub-groups under it.
     */
    public function chart()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        // With the number of ledgers in each group (adds ledgers_count)
        $groups = Group::where('company_id', $companyId)->withCount('ledgers')->orderBy('name')->get();

        // Balance Sheet groups come first, then Profit & Loss ones: Tally's order
        $natureOrder = ['Liabilities' => 1, 'Assets' => 2, 'Income' => 3, 'Expenses' => 4];

        $topGroups = $groups->whereNull('parent_id')
            ->sortBy(fn ($group) => [$natureOrder[$group->nature] ?? 9, $group->name])
            ->values();

        // The tree as one flat list of rows, each with its depth (0 = top-level), in display order
        $rows = [];

        foreach ($topGroups as $group) {
            $this->addChartRows($rows, $group, $groups, 0);
        }

        $totalLedgers = $groups->sum('ledgers_count');

        return view('account-master.group.chart-of-accounts', compact('user', 'rows', 'totalLedgers'));
    }

    // Add a group and, under it, all its sub-groups (at any depth) to the rows of the Chart of Accounts
    private function addChartRows(&$rows, $group, $groups, $depth)
    {
        $children = $groups->where('parent_id', $group->id)->values();

        $rows[] = [
            'group'    => $group,
            'parent'   => $groups->firstWhere('id', $group->parent_id),
            'depth'    => $depth,
            'children' => $children->count(),
        ];

        foreach ($children as $child) {
            $this->addChartRows($rows, $child, $groups, $depth + 1);
        }
    }

    // Add Group page: the form to create a new (custom) group
    public function create()
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $parentOptions = Group::where('company_id', $companyId)->orderBy('name')->get();

        return view('account-master.group.group', compact('user', 'parentOptions'));
    }

    // Save the new group
    public function store(Request $request)
    {
        $companyId = session('company_id');

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('groups')->where('company_id', $companyId),
            ],
            // Only a group of this company can be the parent
            'parent_id' => ['required', Rule::exists('groups', 'id')->where('company_id', $companyId)],
        ], [
            'name.unique' => 'A group with this name already exists.',
        ]);

        // A group's nature always matches the group it sits under
        $parent = Group::where('company_id', $companyId)->findOrFail($request->parent_id);

        Group::create([
            'company_id' => session('company_id'),
            'parent_id'  => $parent->id,
            'name'       => $request->name,
            'nature'     => $parent->nature,
            'is_primary' => false,
        ]);

        return redirect()->route('groups.index')->with('success', 'Group added successfully.');
    }

    // Edit Group page: the form to edit an existing group
    public function edit($id)
    {
        $user = User::find(session('user_id'));

        if (!$user) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $group = Group::where('company_id', $companyId)->findOrFail($id);

        if ($group->is_primary) {
            return redirect()->route('groups.index')->with('error', 'Default groups cannot be edited.');
        }

        // A group can't be placed under itself, so leave it out of the options
        $parentOptions = Group::where('company_id', $companyId)
            ->where('id', '!=', $group->id)
            ->orderBy('name')
            ->get();

        return view('account-master.group.edit-group', compact('user', 'group', 'parentOptions'));
    }

    // Save changes to an existing group
    public function update(Request $request, $id)
    {
        $companyId = session('company_id');

        $group = Group::where('company_id', $companyId)->findOrFail($id);

        if ($group->is_primary) {
            return redirect()->route('groups.index')->with('error', 'Default groups cannot be edited.');
        }

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('groups')->where('company_id', $companyId)->ignore($group->id),
            ],
            // Only a group of this company can be the parent
            'parent_id' => ['nullable', Rule::exists('groups', 'id')->where('company_id', $companyId)],
            // Only a top-level (Primary) group needs its nature picked manually
            'nature' => 'required_without:parent_id|in:Assets,Liabilities,Income,Expenses',
        ], [
            'name.unique' => 'A group with this name already exists.',
        ]);

        $parent = $request->parent_id
            ? Group::where('company_id', $companyId)->findOrFail($request->parent_id)
            : null;

        // A group can't go under itself or under one of its own sub-groups (that would make a loop),
        // so climb up from the new parent and make sure this group is not on the way
        for ($above = $parent; $above; $above = $above->parent) {
            if ($above->id == $group->id) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A group can\'t be placed under itself or one of its own sub-groups.',
                ]);
            }
        }

        // A sub-group's nature always matches the group it sits under
        $nature = $parent ? $parent->nature : $request->nature;

        $group->update([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
            'nature'    => $nature,
        ]);

        // The sub-groups below follow the new nature too, one level at a time
        $found = [$group->id];

        while ($found) {
            $query = Group::where('company_id', $companyId)->whereIn('parent_id', $found);
            $found = $query->pluck('id')->all();
            $query->update(['nature' => $nature]);
        }

        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    // Delete a group
    public function destroy($id)
    {
        $companyId = session('company_id');

        $group = Group::where('company_id', $companyId)->findOrFail($id);

        if ($group->is_primary) {
            return redirect()->route('groups.index')->with('error', 'Default groups cannot be deleted.');
        }

        if ($group->children()->exists()) {
            return redirect()->route('groups.index')->with('error', 'Move or delete the sub-groups under this group first.');
        }

        if ($group->ledgers()->exists()) {
            return redirect()->route('groups.index')->with('error', 'Move or delete the ledgers under this group first.');
        }

        $group->delete();

        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }

    // Export the (filtered) group list to a CSV file that opens fine in Excel
    public function export(Request $request)
    {
        if (!User::find(session('user_id'))) {
            return redirect()->route('login');
        }

        $companyId = session('company_id');

        $groups = Group::where('company_id', $companyId)
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
            ->when($request->nature, function ($query, $nature) {
                $query->where('nature', $nature);
            })
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($groups) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['S.No.', 'Group', 'Under', 'Nature of Group', 'Created Date']);

            foreach ($groups as $index => $group) {
                fputcsv($handle, [
                    $index + 1,
                    $group->name,
                    $group->parent->name ?? '-',
                    $group->nature,
                    $group->created_at->format('d-m-Y h:i A'),
                ]);
            }

            fclose($handle);
        }, 'groups.csv', ['Content-Type' => 'text/csv']);
    }
}
