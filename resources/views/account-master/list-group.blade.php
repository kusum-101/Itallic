@extends('layouts.app')

@section('title', 'List Groups')

@section('content')

  <!-- Page heading -->
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-foreground">List Groups</h1>
    <p class="mt-1 text-sm text-muted-foreground">Total : {{ $groups->count() }}</p>
  </div>

  <!-- Success / error message (e.g. after adding, editing or deleting a group) -->
  @if(session('success'))
  <div class="mt-4 rounded-lg border border-border bg-success/10 px-4 py-3">
    <p class="text-sm text-success">{{ session('success') }}</p>
  </div>
  @endif

  @if(session('error'))
  <div class="mt-4 rounded-lg border border-border bg-danger/10 px-4 py-3">
    <p class="text-sm text-danger">{{ session('error') }}</p>
  </div>
  @endif

  <!-- Filters: everything in a single line (scrolls sideways on narrow screens instead of wrapping) -->
  <form action="{{ route('list-group') }}" method="GET" class="mt-6 flex flex-nowrap items-center gap-2 overflow-x-auto rounded-xl border border-border bg-card p-4">

    <input
      type="text"
      name="name"
      value="{{ request('name') }}"
      placeholder="Group Name"
      class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <select name="under" class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Under</option>
      <option value="primary" @selected(request('under') == 'primary')>Primary</option>
      @foreach($underOptions as $option)
        <option value="{{ $option->id }}" @selected(request('under') == $option->id)>{{ $option->name }}</option>
      @endforeach
    </select>

    <select name="nature" class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Nature Group</option>
      <option value="Assets" @selected(request('nature') == 'Assets')>Assets</option>
      <option value="Liabilities" @selected(request('nature') == 'Liabilities')>Liabilities</option>
      <option value="Income" @selected(request('nature') == 'Income')>Income</option>
      <option value="Expenses" @selected(request('nature') == 'Expenses')>Expenses</option>
    </select>

    <button type="submit" class="h-10 shrink-0 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      Search
    </button>
    <a href="{{ route('list-group') }}" class="flex h-10 shrink-0 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
      Reset
    </a>
    <a href="{{ route('list-group.export', request()->query()) }}" class="flex h-10 shrink-0 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
      Export to Excel
    </a>
    <a href="{{ route('group') }}" class="flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      <i data-lucide="plus" class="size-4"></i> Add
    </a>

  </form>

  <!-- Groups table -->
  <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-card">
    <table class="w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">S.No.</th>
          <th class="px-4 py-3 text-start font-semibold">Group</th>
          <th class="px-4 py-3 text-start font-semibold">Under</th>
          <th class="px-4 py-3 text-start font-semibold">Nature of Group</th>
          <th class="px-4 py-3 text-start font-semibold">Created Date</th>
          <th class="px-4 py-3 text-start font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">

        {{-- One row for each group. If there is none, the @empty part runs --}}
        @forelse($groups as $group)
        <tr>
          <td class="px-4 py-3 text-muted-foreground">{{ $loop->iteration }}</td>
          <td class="px-4 py-3 font-medium text-foreground">{{ $group->name }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $group->parent->name ?? 'Primary' }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ is_null($group->parent_id) ? $group->nature : '' }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $group->created_at->format('d-m-Y h:i A') }}</td>

          <!-- Options for this group: 3-line button opens a small menu -->
          <td class="px-4 py-3">
            <div data-action-menu>
              <button type="button" data-action-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-label="Actions" class="flex size-8 items-center justify-center rounded-lg border border-input text-foreground transition-colors hover:bg-accent">
                <i data-lucide="menu" class="size-4"></i>
              </button>

              <div data-action-menu-panel role="menu" class="fixed z-50 hidden min-w-48 rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lg">
                <a href="{{ route('group.edit', $group->id) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="pencil" class="size-4"></i> Edit
                </a>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No groups found.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

@endsection
