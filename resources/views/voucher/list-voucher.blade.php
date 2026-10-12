@extends('layouts.app')

@section('title', $voucher['label'] . ' Register')

@section('content')

  <!-- Page heading. Total and Amount count every voucher that matches the filters, not just this page -->
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $voucher['label'] }} Register</h1>
    <p class="mt-1 text-sm text-muted-foreground">Total : {{ $vouchers->total() }} &nbsp;|&nbsp; Amount : {{ number_format($totalAmount, 2) }}</p>
  </div>

  <!-- Success / error message (e.g. after adding, editing or deleting a voucher) -->
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

  <!-- Filters. form-grid (public/css/custom.css): 1 field per row on mobile, 2 on tablet, 4 on big screens -->
  <form action="{{ route($type . '-register') }}" method="GET" class="form-grid mt-6 rounded-xl border border-border bg-card p-4">

    <input
      type="text"
      name="voucher_no"
      value="{{ request('voucher_no') }}"
      placeholder="{{ $voucher['label'] }} No"
      class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    {{-- A voucher shows up when this ledger is on its Debit or Credit side --}}
    <select name="ledger" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Ledger</option>
      @foreach($ledgerOptions as $option)
        <option value="{{ $option->id }}" @selected(request('ledger') == $option->id)>{{ $option->name }}</option>
      @endforeach
    </select>

    <input
      type="text"
      name="bill_no"
      value="{{ request('bill_no') }}"
      placeholder="Bill No"
      class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <select name="status" class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Status</option>
      @foreach(['Verified', 'Not Verified'] as $option)
        <option value="{{ $option }}" @selected(request('status') == $option)>{{ $option }}</option>
      @endforeach
    </select>

    <input
      type="date"
      name="from"
      value="{{ request('from') }}"
      aria-label="From date"
      title="From date"
      class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <input
      type="date"
      name="to"
      value="{{ request('to') }}"
      aria-label="To date"
      title="To date"
      class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">

    <div class="col-full flex flex-wrap items-center gap-2">
      <button type="submit" class="h-10 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        Search
      </button>
      <a href="{{ route($type . '-register') }}" class="flex h-10 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        Reset
      </a>
      {{-- Downloads every voucher that matches the filters above (all pages) --}}
      <a href="{{ route($type . '-register.export', request()->except('page')) }}" class="flex h-10 items-center gap-1.5 rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        <i data-lucide="download" class="size-4"></i> Export to Excel
      </a>
      <a href="{{ route($type) }}" class="flex h-10 items-center gap-1.5 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        <i data-lucide="plus" class="size-4"></i> Add
      </a>
    </div>

  </form>

  <!-- Vouchers table (scrolls sideways when the columns don't fit) -->
  <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-card">
    <table class="w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">S.No.</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">{{ $voucher['label'] }} No</th>
          <th class="px-4 py-3 text-start font-semibold">Date</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Debit Account (Dr)</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Credit Account (Cr)</th>
          <th class="px-4 py-3 text-start font-semibold">Narration</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Bill No</th>
          <th class="px-4 py-3 text-end font-semibold">Amount</th>
          <th class="px-4 py-3 text-start font-semibold">Status</th>
          <th class="px-4 py-3 text-start font-semibold">Document</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Created Date</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Created By</th>
          <th class="px-4 py-3 text-start font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">

        {{-- One row for each voucher. If there is none, the @empty part runs --}}
        @forelse($vouchers as $row)
        <tr>
          {{-- firstItem(): the numbering carries on from the page before --}}
          <td class="px-4 py-3 text-muted-foreground">{{ $vouchers->firstItem() + $loop->index }}</td>
          <td class="whitespace-nowrap px-4 py-3 font-medium text-foreground">
            <a href="{{ route($type . '.show', $row->id) }}" class="text-primary-text">{{ $row->voucher_no }}</a>
          </td>
          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row->date->format('d-m-Y') }}</td>
          <td class="px-4 py-3 text-foreground"><div class="max-w-xs truncate" title="{{ $row->drLedger->name }}">{{ $row->drLedger->name }}</div></td>
          <td class="px-4 py-3 text-foreground"><div class="max-w-xs truncate" title="{{ $row->crLedger->name }}">{{ $row->crLedger->name }}</div></td>

          <!-- narration-box (public/css/custom.css): same width in every row, scrolls when the text is long -->
          <td class="px-4 py-3">
            @if($row->narration)
              <div class="narration-box rounded-lg bg-muted px-3 py-2 text-foreground">{{ $row->narration }}</div>
            @endif
          </td>

          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row->bill_no }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-end text-foreground">{{ number_format($row->amount, 2) }}</td>

          <!-- status-badge (public/css/custom.css): green when verified, red when not -->
          <td class="px-4 py-3">
            <span class="status-badge {{ $row->isVerified() ? 'verified' : 'not-verified' }}">{{ $row->status }}</span>
          </td>

          <td class="whitespace-nowrap px-4 py-3">
            @foreach($row->documents as $document)
              <a href="{{ route('voucher-document', $document->id) }}" target="_blank" title="{{ $document->original_name }}" class="text-primary-text underline">Doc {{ $loop->iteration }}</a>@if(!$loop->last), @endif
            @endforeach
          </td>

          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row->created_at->format('d-m-Y h:i A') }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row->creator->name ?? '-' }}</td>

          <!-- Options for this voucher: 3-line button opens a small menu (same as List Ledgers).
               A verified voucher is locked: Edit and Delete come back after "Unverify" -->
          <td class="px-4 py-3">
            <div data-action-menu>
              <button type="button" data-action-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-label="Actions" class="flex size-8 items-center justify-center rounded-lg border border-input text-foreground transition-colors hover:bg-accent">
                <i data-lucide="menu" class="size-4"></i>
              </button>

              <div data-action-menu-panel role="menu" class="fixed z-50 hidden min-w-48 rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lg">
                <a href="{{ route($type . '.show', $row->id) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="eye" class="size-4"></i> View
                </a>

                @if(!$row->isVerified())
                <a href="{{ route($type . '.edit', $row->id) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="pencil" class="size-4"></i> Edit
                </a>
                @endif

                <form action="{{ route($type . '.verify', $row->id) }}" method="POST">
                  @csrf
                  <button type="submit" role="menuitem" class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm transition-colors hover:bg-accent">
                    @if($row->isVerified())
                      <i data-lucide="rotate-ccw" class="size-4"></i> Unverify
                    @else
                      <i data-lucide="circle-check" class="size-4"></i> Verify
                    @endif
                  </button>
                </form>

                @if(!$row->isVerified())
                <button type="button" role="menuitem"
                  data-delete-voucher
                  data-url="{{ route($type . '.destroy', $row->id) }}"
                  data-name="{{ $row->voucher_no }}"
                  class="flex w-full items-center gap-2 rounded-md px-3 py-2 text-left text-sm text-destructive-text transition-colors hover:bg-accent">
                  <i data-lucide="trash-2" class="size-4"></i> Delete
                </button>
                @endif
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="13" class="px-4 py-8 text-center text-muted-foreground">No {{ strtolower($voucher['label']) }} vouchers found.</td>
        </tr>
        @endforelse

      </tbody>
    </table>

    <!-- Pages: shown only when the vouchers don't fit on one page -->
    @if($vouchers->hasPages())
    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border px-4 py-3 text-sm text-muted-foreground">
      <p>Showing {{ $vouchers->firstItem() }} to {{ $vouchers->lastItem() }} of {{ $vouchers->total() }}</p>

      <div class="flex items-center gap-2">
        @if(!$vouchers->onFirstPage())
        <a href="{{ $vouchers->previousPageUrl() }}" class="flex h-10 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Previous
        </a>
        @endif

        <span>Page {{ $vouchers->currentPage() }} of {{ $vouchers->lastPage() }}</span>

        @if($vouchers->hasMorePages())
        <a href="{{ $vouchers->nextPageUrl() }}" class="flex h-10 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Next
        </a>
        @endif
      </div>
    </div>
    @endif
  </div>

  <!-- Delete warning popup: opened by a row's "Delete" option, asks before deleting -->
  <div id="delete-voucher-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true" aria-labelledby="delete-voucher-title">
    <form id="delete-voucher-form" method="POST" class="w-full max-w-md rounded-xl border border-border bg-card p-6 text-foreground shadow-lg">
      @csrf
      @method('DELETE')

      <div class="flex items-start gap-3">
        <div class="flex size-10 shrink-0 items-center justify-center rounded-full text-destructive-text" style="background: color-mix(in oklch, var(--destructive) 12%, transparent)">
          <i data-lucide="triangle-alert" class="size-5"></i>
        </div>
        <div>
          <h2 id="delete-voucher-title" class="text-lg font-semibold">Delete {{ $voucher['label'] }}?</h2>
          <p class="mt-1 text-sm text-muted-foreground">
            Are you sure you want to delete <span id="delete-voucher-name" class="font-medium text-foreground"></span>?
            Its documents are deleted too. This cannot be undone.
          </p>
        </div>
      </div>

      <div class="mt-6 flex justify-end gap-2">
        <button type="button" data-delete-voucher-cancel class="h-10 rounded-lg border border-input bg-background px-4 text-sm font-medium transition-colors hover:bg-accent">
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
    var modal = document.getElementById("delete-voucher-modal");
    var form = document.getElementById("delete-voucher-form");
    var nameEl = document.getElementById("delete-voucher-name");

    function openModal(url, name) {
      form.action = url;
      nameEl.textContent = name;
      modal.classList.remove("hidden");
      modal.classList.add("flex");
    }

    function closeModal() {
      modal.classList.add("hidden");
      modal.classList.remove("flex");
    }

    document.addEventListener("click", function(e) {
      var btn = e.target.closest("[data-delete-voucher]");
      if (btn) {
        // Close the row's options menu first (the layout's menu script closes on Esc)
        document.dispatchEvent(new KeyboardEvent("keydown", { key: "Escape" }));
        openModal(btn.dataset.url, btn.dataset.name);
        return;
      }
      // Cancel button, or a click on the dark area outside the box, closes it
      if (e.target.closest("[data-delete-voucher-cancel]") || e.target === modal) closeModal();
    });

    document.addEventListener("keydown", function(e) {
      if (e.key === "Escape") closeModal();
    });
  })();
</script>
@endpush
