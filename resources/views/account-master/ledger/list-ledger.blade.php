@extends('layouts.app')

@section('title', 'List Ledgers')

@section('content')

  <!-- Page heading -->
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-foreground">List Ledgers</h1>
    <p class="mt-1 text-sm text-muted-foreground">Total : {{ $ledgers->count() }}</p>
  </div>

  <!-- Success / error message (e.g. after adding, editing or deleting a ledger) -->
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
  <form action="{{ route('ledgers.index') }}" method="GET" class="mt-6 flex flex-nowrap items-center gap-2 overflow-x-auto rounded-xl border border-border bg-card p-4">

    <input
      type="text"
      name="name"
      value="{{ request('name') }}"
      placeholder="Ledger Name"
      class="h-10 w-52 shrink-0 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <select name="group" class="h-10 w-52 shrink-0 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Group</option>
      @foreach($groupOptions as $option)
        <option value="{{ $option->id }}" @selected(request('group') == $option->id)>{{ $option->name }}</option>
      @endforeach
    </select>

    <button type="submit" class="h-10 shrink-0 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      Search
    </button>
    <a href="{{ route('ledgers.index') }}" class="flex h-10 shrink-0 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
      Reset
    </a>
    <a href="{{ route('ledgers.create') }}" class="flex h-10 shrink-0 items-center gap-1.5 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      <i data-lucide="plus" class="size-4"></i> Add
    </a>

  </form>

  <!-- Ledgers table -->
  <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-card">
    <table class="w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">S.No.</th>
          <th class="px-4 py-3 text-start font-semibold">Ledger</th>
          <th class="px-4 py-3 text-start font-semibold">Group</th>
          <th class="px-4 py-3 text-start font-semibold">Opening Balance</th>
          <th class="px-4 py-3 text-start font-semibold">Created Date</th>
          <th class="px-4 py-3 text-start font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">

        {{-- One row for each ledger. If there is none, the @empty part runs --}}
        @forelse($ledgers as $ledger)
        <tr>
          <td class="px-4 py-3 text-muted-foreground">{{ $loop->iteration }}</td>
          <td class="px-4 py-3 font-medium text-foreground">{{ $ledger->name }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $ledger->group->name }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ number_format($ledger->opening_balance, 2) }} {{ $ledger->opening_type }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $ledger->created_at->format('d-m-Y h:i A') }}</td>

          <!-- Options for this ledger: 3-line button opens a small menu -->
          <td class="px-4 py-3">
            <div data-action-menu>
              <button type="button" data-action-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-label="Actions" class="flex size-8 items-center justify-center rounded-lg border border-input text-foreground transition-colors hover:bg-accent">
                <i data-lucide="menu" class="size-4"></i>
              </button>

              <div data-action-menu-panel role="menu" class="fixed z-50 hidden min-w-48 rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lg">
                <a href="{{ route('ledgers.edit', $ledger->id) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="pencil" class="size-4"></i> Edit
                </a>
                <button type="button" role="menuitem"
                  data-delete-ledger
                  data-url="{{ route('ledgers.destroy', $ledger->id) }}"
                  data-name="{{ $ledger->name }}"
                  class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-destructive-text transition-colors hover:bg-accent">
                  <i data-lucide="trash-2" class="size-4"></i> Delete
                </button>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="6" class="px-4 py-8 text-center text-muted-foreground">No ledgers found.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <!-- Delete warning popup: opened by a row's "Delete" option, asks before deleting -->
  <div id="delete-ledger-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-ledger-title">
    <form id="delete-ledger-form" method="POST" class="w-full max-w-md rounded-xl border border-border bg-card p-6 text-foreground shadow-lg">
      @csrf
      @method('DELETE')

      <div class="flex items-start gap-3">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full text-destructive-text" style="background: color-mix(in oklch, var(--destructive) 12%, transparent)">
          <i data-lucide="triangle-alert" class="size-5"></i>
        </div>
        <div>
          <h2 id="delete-ledger-title" class="text-lg font-semibold">Delete Ledger?</h2>
          <p class="mt-1 text-sm text-muted-foreground">
            Are you sure you want to delete <span id="delete-ledger-name" class="font-medium text-foreground"></span>?
            This cannot be undone.
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" data-delete-ledger-cancel class="h-10 rounded-lg border border-input bg-background px-4 text-sm font-medium transition-colors hover:bg-accent">
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
    var modal = document.getElementById("delete-ledger-modal");
    var form = document.getElementById("delete-ledger-form");
    var nameEl = document.getElementById("delete-ledger-name");

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
      var btn = e.target.closest("[data-delete-ledger]");
      if (btn) {
        // Close the row's options menu first (the layout's menu script closes on Esc)
        document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));
        openModal(btn.dataset.url, btn.dataset.name);
        return;
      }
      // Cancel button, or a click on the dark area outside the box, closes it
      if (e.target.closest("[data-delete-ledger-cancel]") || e.target === modal) closeModal();
    });

    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape") closeModal();
    });
  })();
</script>
@endpush
