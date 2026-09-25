<?php

namespace App\Http\Controllers\AccountMaster;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('groups')->where('company_id', session('company_id')),
            ],
            'parent_id' => 'required|exists:groups,id',
        ], [
            'name.unique' => 'A group with this name already exists.',
        ]);

        // A group's nature always matches the group it sits under
        $parent = Group::findOrFail($request->parent_id);

        Group::create([
            'company_id' => session('company_id'),
            'parent_id'  => $parent->id,
            'name'       => $request->name,
            'nature'     => $parent->nature,
            'is_primary' => false,
        ]);

        return redirect()->route('list-group')->with('success', 'Group added successfully.');
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
            return redirect()->route('list-group')->with('error', 'Default groups cannot be edited.');
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
            return redirect()->route('list-group')->with('error', 'Default groups cannot be edited.');
        }

        $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('groups')->where('company_id', $companyId)->ignore($group->id),
            ],
            'parent_id' => 'nullable|exists:groups,id',
            // Only a top-level (Primary) group needs its nature picked manually
            'nature' => 'required_without:parent_id|in:Assets,Liabilities,Income,Expenses',
        ], [
            'name.unique' => 'A group with this name already exists.',
        ]);

        // A sub-group's nature always matches the group it sits under
        $nature = $request->parent_id
            ? Group::findOrFail($request->parent_id)->nature
            : $request->nature;

        $group->update([
            'name'      => $request->name,
            'parent_id' => $request->parent_id,
            'nature'    => $nature,
        ]);

        return redirect()->route('list-group')->with('success', 'Group updated successfully.');
    }

    // Delete a group
    public function destroy($id)
    {
        $companyId = session('company_id');

        $group = Group::where('company_id', $companyId)->findOrFail($id);

        if ($group->is_primary) {
            return redirect()->route('list-group')->with('error', 'Default groups cannot be deleted.');
        }

        if ($group->children()->exists()) {
            return redirect()->route('list-group')->with('error', 'Move or delete the sub-groups under this group first.');
        }

        if ($group->ledgers()->exists()) {
            return redirect()->route('list-group')->with('error', 'Move or delete the ledgers under this group first.');
        }

        $group->delete();

        return redirect()->route('list-group')->with('success', 'Group deleted successfully.');
    }

    // Export the (filtered) group list to a CSV file that opens fine in Excel
    public function export(Request $request)
    {
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
