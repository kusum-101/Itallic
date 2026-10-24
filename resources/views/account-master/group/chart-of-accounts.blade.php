@extends('layouts.app')

@section('title', 'Chart of Accounts')

@section('content')

  <!-- Page heading -->
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('dashboardUser')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Chart of Accounts</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">{{ count($rows) }} groups · {{ $totalLedgers }} ledgers</p>
    </div>
    <a href="{{ route('groups.create') }}" class="flex h-9 items-center gap-1.5 rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      <i data-lucide="plus" class="size-4"></i> Add Group
    </a>
  </div>

  <div class="mt-4 rounded-xl border border-border bg-card">
    <!-- Toolbar: search + open / close all -->
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-border p-3">
      <div class="flex h-9 w-full items-center gap-2 rounded-lg border border-input bg-background px-3 sm:w-64">
        <i data-lucide="search" class="size-4 shrink-0 text-muted-foreground"></i>
        <input type="text" id="chart-search" autocomplete="off" placeholder="Search group" class="h-full w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground">
      </div>
      <div class="flex gap-2">
        <button type="button" data-expand-all class="h-9 rounded-lg border border-input px-3 text-sm font-medium text-foreground transition-colors hover:bg-accent">Expand all</button>
        <button type="button" data-collapse-all class="h-9 rounded-lg border border-input px-3 text-sm font-medium text-foreground transition-colors hover:bg-accent">Collapse all</button>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="chart-table min-w-full text-sm">
        <thead>
          <tr>
            <th>Group Name</th>
            <th>Under Group</th>
            <th>Report</th>
            <th>Nature Of Group</th>
            <th class="text-end">Ledgers</th>
            <th class="w-10"></th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody id="chart-rows">
          @foreach ($rows as $row)
            @php
              $group = $row['group'];
              // Assets and Liabilities show in the Balance Sheet, Income and Expenses in Profit & Loss
              $report = in_array($group->nature, ['Assets', 'Liabilities']) ? 'Balance Sheet' : 'Profit & Loss';
            @endphp
            {{-- data-parent: the row above it in the tree, used to open / close sub-groups --}}
            <tr data-id="{{ $group->id }}" data-parent="{{ $group->parent_id }}" data-name="{{ strtolower($group->name) }}" class="{{ $row['depth'] ? 'hidden' : '' }}">
              <td>
                {{-- Each level goes 1.25rem further right --}}
                <span class="{{ $row['depth'] ? 'text-foreground' : 'font-medium text-foreground' }}" style="padding-left: {{ $row['depth'] * 1.25 }}rem">
                  {{ $group->name }}@if ($row['children']) <span class="text-muted-foreground">({{ $row['children'] }})</span>@endif
                </span>
                @unless ($group->is_primary)
                  <span class="ms-1 rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">Custom</span>
                @endunless
              </td>
              <td class="text-muted-foreground">{{ $row['parent']->name ?? 'Primary' }}</td>
              <td class="uppercase text-muted-foreground">{{ $report }}</td>
              <td class="uppercase text-muted-foreground">{{ $group->nature }}</td>
              <td class="text-end tabular-nums">
                @if ($group->ledgers_count)
                  <a href="{{ route('ledgers.index', ['group' => $group->id]) }}" class="text-primary hover:underline" title="See these ledgers">{{ $group->ledgers_count }}</a>
                @else
                  <span class="text-muted-foreground">0</span>
                @endif
              </td>
              <td>
                @if ($row['children'])
                  <button type="button" data-toggle-row class="chart-toggle" aria-expanded="false" aria-label="Show sub-groups of {{ $group->name }}">
                    <i data-lucide="chevron-down" class="size-4"></i>
                  </button>
                @endif
              </td>
              <td class="whitespace-nowrap text-end">
                <a href="{{ route('groups.create', ['parent' => $group->id]) }}" class="text-primary hover:underline">Add subgroup</a>
                @unless ($group->is_primary)
                  <span class="px-1 text-muted-foreground/50">|</span>
                  <a href="{{ route('groups.edit', $group->id) }}" class="text-primary hover:underline">Edit</a>
                @endunless
              </td>
            </tr>
          @endforeach
          <tr id="chart-empty" class="hidden">
            <td colspan="7" class="py-6 text-center text-muted-foreground">No group found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  (function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('#chart-rows tr[data-id]'));
    var byId = {};
    rows.forEach(function (row) { byId[row.dataset.id] = row; });

    // The ids of the groups whose sub-groups are open
    var open = {};

    // A row shows when every group above it is open (top-level rows always show)
    function isShown(row) {
      var parent = byId[row.dataset.parent];
      while (parent) {
        if (!open[parent.dataset.id]) return false;
        parent = byId[parent.dataset.parent];
      }
      return true;
    }

    function render() {
      rows.forEach(function (row) {
        row.classList.toggle('hidden', !isShown(row));

        var button = row.querySelector('[data-toggle-row]');
        if (button) {
          button.setAttribute('aria-expanded', open[row.dataset.id] ? 'true' : 'false');
        }
      });
      document.getElementById('chart-empty').classList.add('hidden');
    }

    // Click the arrow: open / close that group's sub-groups
    document.getElementById('chart-rows').addEventListener('click', function (e) {
      var button = e.target.closest('[data-toggle-row]');
      if (!button) return;

      var id = button.closest('tr').dataset.id;
      open[id] = !open[id];
      render();
    });

    document.querySelector('[data-expand-all]').addEventListener('click', function () {
      search.value = '';
      rows.forEach(function (row) { open[row.dataset.id] = true; });
      render();
    });

    document.querySelector('[data-collapse-all]').addEventListener('click', function () {
      search.value = '';
      open = {};
      render();
    });

    // Search: show the groups whose name has the text, with the groups above them
    var search = document.getElementById('chart-search');

    search.addEventListener('input', function () {
      var text = search.value.trim().toLowerCase();

      if (!text) {
        render();
        return;
      }

      var show = {};
      rows.forEach(function (row) {
        if (row.dataset.name.indexOf(text) === -1) return;

        // This row and every row above it
        var current = row;
        while (current) {
          show[current.dataset.id] = true;
          current = byId[current.dataset.parent];
        }
      });

      rows.forEach(function (row) { row.classList.toggle('hidden', !show[row.dataset.id]); });
      document.getElementById('chart-empty').classList.toggle('hidden', Object.keys(show).length > 0);
    });
  })();
</script>
@endpush
