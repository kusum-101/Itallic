@extends('layouts.app')

@section('title', 'List Stock Categories')

@section('content')

  <!-- Page heading -->
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('dashboardUser')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">List Stock Categories</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Total : {{ count($rows) }}</p>
    </div>
    <a href="{{ route('stock-categories.create') }}" class="flex h-9 items-center gap-1.5 rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      <i data-lucide="plus" class="size-4"></i> Add
    </a>
  </div>

  <!-- Success / error message (e.g. after adding, editing or deleting a stock category) -->
  @if(session('success'))
  <div class="mt-4 rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--success) 10%, transparent)">
    <p class="text-sm text-success-text">{{ session('success') }}</p>
  </div>
  @endif

  @if(session('error'))
  <div class="mt-4 rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--destructive) 10%, transparent)">
    <p class="text-sm text-destructive-text">{{ session('error') }}</p>
  </div>
  @endif

  <!-- Filters: everything in a single line (scrolls sideways on narrow screens instead of wrapping) -->
  <form action="{{ route('stock-categories.index') }}" method="GET" class="mt-4 flex flex-nowrap items-center gap-2 overflow-x-auto list-toolbar">

    <input
      type="text"
      name="name"
      value="{{ request('name') }}"
      placeholder="Stock Category Name"
      class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <select name="stock_group" class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Stock Group</option>
      @foreach($stockGroupOptions as $option)
        <option value="{{ $option->id }}" @selected(request('stock_group') == $option->id)>{{ $option->name }}</option>
      @endforeach
    </select>

    <button type="submit" class="h-10 shrink-0 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      Search
    </button>
    <a href="{{ route('stock-categories.index') }}" class="flex h-10 shrink-0 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
      Reset
    </a>

  </form>

  <!-- Stock categories table: a tree, sub-categories open under their category with the arrow.
       A name search shows the matches with the categories above them, all opened -->
  <div class="list-table overflow-x-auto">
    <table class="chart-table w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="w-12 px-4 py-3">
            {{-- No aria-expanded here: .chart-toggle would turn the arrow sideways. The script shows one of the two icons.
                 Drawn here as SVG: the theme's icon set (assets/main-*.js) has no double arrows --}}
            <button type="button" id="stock-category-toggle-all" class="chart-toggle" title="Open all sub-categories">
              <svg data-when-closed xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4"><path d="m7 6 5 5 5-5"/><path d="m7 13 5 5 5-5"/></svg>
              <svg data-when-open xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden size-4"><path d="m17 11-5-5-5 5"/><path d="m17 18-5-5-5 5"/></svg>
            </button>
          </th>
          <th class="px-4 py-3 text-start font-semibold">Stock Category</th>
          <th class="px-4 py-3 text-start font-semibold">Stock Group</th>
          <th class="px-4 py-3 text-end font-semibold">Items</th>
          <th class="px-4 py-3 text-start font-semibold">Created Date</th>
          <th class="px-4 py-3 text-end font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody id="stock-category-rows" data-open-all="{{ request('name') ? '1' : '' }}" class="divide-y divide-border">

        {{-- One row for each stock category, in tree order. If there is none, the @empty part runs.
             data-parent: the row above it in the tree, used to open / close sub-categories --}}
        @forelse($rows as $row)
        @php($stockCategory = $row['category'])
        <tr data-id="{{ $stockCategory->id }}" data-parent="{{ $row['parentId'] }}" class="{{ $row['depth'] && !request('name') ? 'hidden' : '' }}">
          <td class="px-4 py-3">
            @if($row['children'])
              <button type="button" data-toggle-row class="chart-toggle" aria-expanded="false" aria-label="Show sub-categories of {{ $stockCategory->name }}">
                <i data-lucide="chevron-down" class="size-4"></i>
              </button>
            @endif
          </td>
          <td class="px-4 py-3">
            {{-- Each level goes 1.25rem further right --}}
            <span class="{{ $row['depth'] ? 'text-foreground' : 'font-medium text-foreground' }}" style="padding-left: {{ $row['depth'] * 1.25 }}rem">
              {{ $stockCategory->name }}@if($row['children']) <span class="text-muted-foreground">({{ $row['children'] }})</span>@endif
            </span>
          </td>
          <td class="px-4 py-3 text-muted-foreground">{{ $stockCategory->stockGroup->name ?? '-' }}</td>
          <td class="px-4 py-3 text-end tabular-nums">
            @if($stockCategory->items_count)
              <a href="{{ route('items.index', ['stock_category' => $stockCategory->id]) }}" class="text-primary hover:underline" title="See these items">{{ $stockCategory->items_count }}</a>
            @else
              <span class="text-muted-foreground">0</span>
            @endif
          </td>
          <td class="px-4 py-3 text-muted-foreground">{{ $stockCategory->created_at->format('d-m-Y h:i A') }}</td>

          <!-- This category's links: Add sub-category | Edit | Delete -->
          <td class="px-4 py-3">
            <div class="row-actions">
                {{-- Only a category with a stock group can have sub-categories (they go in its group) --}}
                @if($stockCategory->stock_group_id)
                  <a href="{{ route('stock-categories.create', ['under' => 'category:' . $stockCategory->id]) }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                    <i data-lucide="plus" class="size-4"></i> Add sub-category
                  </a>
                @endif
                <a href="{{ route('stock-categories.edit', $stockCategory->id) }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="pencil" class="size-4"></i> Edit
                </a>
                <button type="button"
                  data-delete-stock-category
                  data-url="{{ route('stock-categories.destroy', $stockCategory->id) }}"
                  data-name="{{ $stockCategory->name }}"
                  class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-destructive-text transition-colors hover:bg-accent">
                  <i data-lucide="trash-2" class="size-4"></i> Delete
                </button>
              </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No stock categories found.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <!-- Delete warning popup: opened by a row's "Delete" option, asks before deleting -->
  <div id="delete-stock-category-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-stock-category-title">
    <form id="delete-stock-category-form" method="POST" class="w-full max-w-md rounded-xl border border-border bg-card p-6 text-foreground shadow-lg">
      @csrf
      @method('DELETE')

      <div class="flex items-start gap-3">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full text-destructive-text" style="background: color-mix(in oklch, var(--destructive) 12%, transparent)">
          <i data-lucide="triangle-alert" class="size-5"></i>
        </div>
        <div>
          <h2 id="delete-stock-category-title" class="text-lg font-semibold">Delete Stock Category?</h2>
          <p class="mt-1 text-sm text-muted-foreground">
            Are you sure you want to delete <span id="delete-stock-category-name" class="font-medium text-foreground"></span>?
            This cannot be undone.
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" data-delete-stock-category-cancel class="h-10 rounded-lg border border-input bg-background px-4 text-sm font-medium transition-colors hover:bg-accent">
          Cancel
        </button>
        <button type="submit" class="h-10 rounded-lg bg-destructive px-4 text-sm font-medium text-white">
          Delete
        </button>
      </div>
    </form>
  </div>

@endsection

@push('scripts')
<script>
  (function() {
    var modal = document.getElementById("delete-stock-category-modal");
    var form = document.getElementById("delete-stock-category-form");
    var nameEl = document.getElementById("delete-stock-category-name");

    function openModal(url, name) {
      form.action = url;
      nameEl.textContent = '"' + name + '"';
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    }

    function closeModal() {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }

    document.addEventListener("click", function(e) {
      var btn = e.target.closest("[data-delete-stock-category]");
      if (btn) {
        // Close the row's options menu first (the layout's menu script closes on Esc)
        document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));
        openModal(btn.dataset.url, btn.dataset.name);
        return;
      }
      // Cancel button, or a click on the dark area outside the box, closes it
      if (e.target.closest("[data-delete-stock-category-cancel]") || e.target === modal) closeModal();
    });

    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape") closeModal();
    });
  })();

  // Tree: the arrow on a category opens / closes its sub-categories, the one in the header all of them
  (function() {
    var body = document.getElementById("stock-category-rows");
    var toggleAll = document.getElementById("stock-category-toggle-all");
    var rows = Array.prototype.slice.call(body.querySelectorAll("tr[data-id]"));
    var byId = {};
    rows.forEach(function(row) { byId[row.dataset.id] = row; });

    // The ids of the categories whose sub-categories are open (all of them after a name search)
    var open = {};

    function openAll() {
      rows.forEach(function(row) { open[row.dataset.id] = true; });
    }

    // A row shows when every category above it is open (top-level rows always show)
    function isShown(row) {
      var parent = byId[row.dataset.parent];
      while (parent) {
        if (!open[parent.dataset.id]) return false;
        parent = byId[parent.dataset.parent];
      }
      return true;
    }

    function render() {
      var allOpen = true;

      rows.forEach(function(row) {
        row.classList.toggle("hidden", !isShown(row));

        var button = row.querySelector("[data-toggle-row]");
        if (button) {
          button.setAttribute("aria-expanded", open[row.dataset.id] ? "true" : "false");
          if (!open[row.dataset.id]) allOpen = false;
        }
      });

      // Header arrow: double-down while something is closed, double-up once all are open
      toggleAll.dataset.allOpen = allOpen ? "1" : "";
      toggleAll.querySelector("[data-when-closed]").classList.toggle("hidden", allOpen);
      toggleAll.querySelector("[data-when-open]").classList.toggle("hidden", !allOpen);
      toggleAll.title = allOpen ? "Close all sub-categories" : "Open all sub-categories";
    }

    body.addEventListener("click", function(e) {
      var button = e.target.closest("[data-toggle-row]");
      if (!button) return;

      var id = button.closest("tr").dataset.id;
      open[id] = !open[id];
      render();
    });

    toggleAll.addEventListener("click", function() {
      if (toggleAll.dataset.allOpen) {
        open = {};
      } else {
        openAll();
      }
      render();
    });

    if (body.dataset.openAll) openAll();
    render();
  })();
</script>
@endpush
