@extends('layouts.app')

@section('title', 'Incoming Purchases')

@section('content')

  <!-- Page heading -->
  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route('purchase-register')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">Incoming Purchases</h1>
    </div>
    <p class="mt-0.5 text-sm text-muted-foreground">
      Sales other companies on this software made to you. Check the accounts and press OK: it is saved in your Purchase Register.
    </p>
  </div>

  <!-- Success / error message (after OK or Reject) -->
  @if(session('success'))
  <div class="mt-4 rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--success) 10%, transparent)">
    <p class="text-sm text-success-text">{{ session('success') }}</p>
  </div>
  @endif

  @if(session('error') || $errors->any())
  <div class="mt-4 rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--destructive) 10%, transparent)">
    <p class="text-sm text-destructive-text">{{ session('error') ?: $errors->first() }}</p>
  </div>
  @endif

  <!-- Waiting for OK. The two dropdowns start on the usual accounts, so OK is normally the only click.
       Each row's form sits in its last cell; the dropdowns join it through form="..." -->
  <h2 class="mt-6 text-base font-semibold text-foreground">Waiting for OK ({{ $pending->count() }})</h2>

  <div class="list-table mt-2 overflow-x-auto">
    <table class="chart-table w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">From Company</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Sale No</th>
          <th class="px-4 py-3 text-start font-semibold">Date</th>
          <th class="px-4 py-3 text-start font-semibold">Narration</th>
          <th class="px-4 py-3 text-start font-semibold">Document</th>
          <th class="px-4 py-3 text-end font-semibold">Amount</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Purchase Account (Dr)</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Supplier (Cr)</th>
          <th class="px-4 py-3 text-start font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">

        @forelse($pending as $row)
        <tr>
          <td class="px-4 py-3 font-medium text-foreground">
            {{ $row->fromCompany->company_name }}
            {{-- Booked before and then edited by the seller: OK updates that Purchase --}}
            @if($row->purchase)
              <span class="mt-1 block text-xs font-normal text-muted-foreground">Edited by the seller. OK updates {{ $row->purchase->voucher_no }}.</span>
            @endif
          </td>
          <td class="whitespace-nowrap px-4 py-3 font-medium">
            <a href="{{ route('incoming-purchases.show', $row->id) }}" class="text-primary-text">{{ $row->sale->voucher_no }}</a>
          </td>
          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ $row->sale->date->format('d-m-Y') }}</td>

          <td class="px-4 py-3">
            @if($row->sale->narration)
              <div class="narration-box rounded-lg bg-muted px-3 py-2 text-foreground">{{ $row->sale->narration }}</div>
            @endif
          </td>

          {{-- The seller's documents are copied to the Purchase on OK --}}
          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
            {{ $row->sale->documents->count() ? $row->sale->documents->count() . ' file(s)' : '-' }}
          </td>

          <td class="whitespace-nowrap px-4 py-3 text-end font-medium text-foreground">{{ number_format($row->sale->amount, 2) }}</td>

          <td class="px-4 py-3">
            <select name="purchase_ledger_id" form="accept-{{ $row->id }}" class="h-10 w-full min-w-44 rounded-lg border border-input bg-background px-3 text-sm">
              @foreach($purchaseLedgers as $ledger)
                <option value="{{ $ledger->id }}" @selected($row->default_purchase == $ledger->id)>{{ $ledger->name }}</option>
              @endforeach
              <option value="new" @selected($row->default_purchase == 'new')>+ New ledger: Purchase</option>
            </select>
          </td>

          <td class="px-4 py-3">
            <select name="supplier_ledger_id" form="accept-{{ $row->id }}" class="h-10 w-full min-w-44 rounded-lg border border-input bg-background px-3 text-sm">
              @foreach($supplierLedgers as $ledger)
                <option value="{{ $ledger->id }}" @selected($row->default_supplier == $ledger->id)>{{ $ledger->name }}</option>
              @endforeach
              <option value="new" @selected($row->default_supplier == 'new')>+ New ledger: {{ $row->fromCompany->company_name }}</option>
            </select>
          </td>

          <td class="px-4 py-3">
            <div class="flex items-center gap-2">
              {{-- Every detail and document of the sale, before saying OK --}}
              <a href="{{ route('incoming-purchases.show', $row->id) }}" title="View" aria-label="View sale {{ $row->sale->voucher_no }}" class="flex h-10 items-center gap-1.5 rounded-lg border border-input px-3 text-sm font-medium text-foreground transition-colors hover:bg-accent">
                <i data-lucide="eye" class="size-4"></i> View
              </a>
              <form id="accept-{{ $row->id }}" action="{{ route('incoming-purchases.accept', $row->id) }}" method="POST">
                @csrf
                <button type="submit" class="h-10 rounded-lg bg-primary px-4 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                  OK
                </button>
              </form>
              <form action="{{ route('incoming-purchases.reject', $row->id) }}" method="POST" onsubmit="return confirm('Reject sale {{ $row->sale->voucher_no }}? It will not be saved as a purchase.')">
                @csrf
                <button type="submit" class="h-10 rounded-lg border border-input px-4 text-sm font-medium text-destructive-text transition-colors hover:bg-accent">
                  Reject
                </button>
              </form>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="9" class="px-4 py-8 text-center text-muted-foreground">Nothing waiting. When another company on this software makes a sale to you, it shows up here.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <!-- The last ones accepted or rejected -->
  @if($handled->isNotEmpty())
  <h2 class="mt-8 text-base font-semibold text-foreground">Recently done</h2>

  <div class="list-table mt-2 overflow-x-auto">
    <table class="chart-table w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">From Company</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Sale No</th>
          <th class="px-4 py-3 text-end font-semibold">Amount</th>
          <th class="px-4 py-3 text-start font-semibold">Status</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Purchase No</th>
          <th class="px-4 py-3 text-start font-semibold">Done By</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">
        @foreach($handled as $row)
        <tr>
          <td class="px-4 py-3 text-foreground">{{ $row->fromCompany->company_name }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            <a href="{{ route('incoming-purchases.show', $row->id) }}" class="text-primary-text">{{ $row->sale->voucher_no }}</a>
          </td>
          <td class="whitespace-nowrap px-4 py-3 text-end text-foreground">{{ number_format($row->sale->amount, 2) }}</td>
          <td class="px-4 py-3">
            <span class="status-badge {{ $row->status == 'Accepted' ? 'verified' : 'not-verified' }}">{{ $row->status }}</span>
          </td>
          <td class="whitespace-nowrap px-4 py-3">
            @if($row->purchase)
              <a href="{{ route('purchase.show', $row->purchase->id) }}" class="text-primary-text">{{ $row->purchase->voucher_no }}</a>
            @else
              -
            @endif
          </td>
          <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">
            {{ $row->handler->name ?? '-' }}{{ $row->handled_at ? ', ' . $row->handled_at->format('d-m-Y h:i A') : '' }}
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

@endsection
