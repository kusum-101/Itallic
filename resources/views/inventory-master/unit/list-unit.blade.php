@extends('layouts.app')

@section('title', 'List Units')

@section('content')

  <!-- Page heading -->
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('dashboardUser')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">List Units</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Total : {{ $units->count() }}</p>
    </div>
    <a href="{{ route('units.create') }}" class="flex h-9 items-center gap-1.5 rounded-lg bg-primary px-3 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      <i data-lucide="plus" class="size-4"></i> Add
    </a>
  </div>

  <!-- Success / error message (e.g. after adding, editing or deleting a unit) -->
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
  <form action="{{ route('units.index') }}" method="GET" class="mt-4 flex flex-nowrap items-center gap-2 overflow-x-auto list-toolbar">

    <input
      type="text"
      name="name"
      value="{{ request('name') }}"
      placeholder="Symbol / Name"
      class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <select name="type" class="h-10 w-44 shrink-0 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Type</option>
      <option value="Simple" @selected(request('type') == 'Simple')>Simple</option>
      <option value="Compound" @selected(request('type') == 'Compound')>Compound</option>
    </select>

    <button type="submit" class="h-10 shrink-0 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      Search
    </button>
    <a href="{{ route('units.index') }}" class="flex h-10 shrink-0 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
      Reset
    </a>

  </form>

  <!-- Units table -->
  <div class="list-table overflow-x-auto">
    <table class="chart-table w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">S.No.</th>
          <th class="px-4 py-3 text-start font-semibold">Symbol</th>
          <th class="px-4 py-3 text-start font-semibold">Type</th>
          <th class="px-4 py-3 text-start font-semibold">Formal Name</th>
          <th class="px-4 py-3 text-start font-semibold">UQC</th>
          <th class="px-4 py-3 text-start font-semibold">Decimal Places</th>
          <th class="px-4 py-3 text-start font-semibold">Created Date</th>
          <th class="px-4 py-3 text-end font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">

        {{-- One row for each unit. If there is none, the @empty part runs --}}
        @forelse($units as $unit)
        <tr>
          <td class="px-4 py-3 text-muted-foreground">{{ $loop->iteration }}</td>
          <td class="px-4 py-3 font-medium text-foreground">{{ $unit->symbol }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $unit->type }}</td>
          {{-- A compound unit has no formal name or UQC of its own --}}
          <td class="px-4 py-3 text-muted-foreground">{{ $unit->name ?? '-' }}</td>
          <td class="px-4 py-3 text-muted-foreground">
            @if($unit->isCompound())
              -
            @else
              {{ $unit->uqc ? $unit->uqc . '-' . \App\Models\Unit::UQCS[$unit->uqc] : 'Not Applicable' }}
            @endif
          </td>
          <td class="px-4 py-3 text-muted-foreground">{{ $unit->quantityUnit()->decimals }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $unit->created_at->format('d-m-Y h:i A') }}</td>

          <!-- Options for this unit: 3-line button opens a small menu -->
          <td class="px-4 py-3">
            <div class="row-actions">
                <a href="{{ route('units.edit', $unit->id) }}" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="pencil" class="size-4"></i> Edit
                </a>
                <button type="button"
                  data-delete-unit
                  data-url="{{ route('units.destroy', $unit->id) }}"
                  data-name="{{ $unit->symbol }}"
                  class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-destructive-text transition-colors hover:bg-accent">
                  <i data-lucide="trash-2" class="size-4"></i> Delete
                </button>
              </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">No units found.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <!-- Delete warning popup: opened by a row's "Delete" option, asks before deleting -->
  <div id="delete-unit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-unit-title">
    <form id="delete-unit-form" method="POST" class="w-full max-w-md rounded-xl border border-border bg-card p-6 text-foreground shadow-lg">
      @csrf
      @method('DELETE')

      <div class="flex items-start gap-3">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full text-destructive-text" style="background: color-mix(in oklch, var(--destructive) 12%, transparent)">
          <i data-lucide="triangle-alert" class="size-5"></i>
        </div>
        <div>
          <h2 id="delete-unit-title" class="text-lg font-semibold">Delete Unit?</h2>
          <p class="mt-1 text-sm text-muted-foreground">
            Are you sure you want to delete <span id="delete-unit-name" class="font-medium text-foreground"></span>?
            This cannot be undone.
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" data-delete-unit-cancel class="h-10 rounded-lg border border-input bg-background px-4 text-sm font-medium transition-colors hover:bg-accent">
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
    var modal = document.getElementById("delete-unit-modal");
    var form = document.getElementById("delete-unit-form");
    var nameEl = document.getElementById("delete-unit-name");

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
      var btn = e.target.closest("[data-delete-unit]");
      if (btn) {
        // Close the row's options menu first (the layout's menu script closes on Esc)
        document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));
        openModal(btn.dataset.url, btn.dataset.name);
        return;
      }
      // Cancel button, or a click on the dark area outside the box, closes it
      if (e.target.closest("[data-delete-unit-cancel]") || e.target === modal) closeModal();
    });

    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape") closeModal();
    });
  })();
</script>
@endpush
