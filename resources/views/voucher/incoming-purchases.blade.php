@extends('layouts.app')

@section('title', 'Incoming Vouchers')

@section('content')

  <!-- Page heading -->
  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route('purchase-register')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">Incoming Vouchers</h1>
    </div>
    <p class="mt-0.5 text-sm text-muted-foreground">
      Sales other companies on this software made to you. OK saves one as shown under Saved As (a Purchase, unless you pick another voucher type on its View page).
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

  <!-- Waiting for OK. OK saves each sale as shown under Saved As, so OK is normally the only click.
       The OK form carries those values in hidden fields; View has the dropdowns to change them -->
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
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Saved As</th>
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

          {{-- What OK saves. To save it as another voucher type or with other accounts, use View --}}
          <td class="px-4 py-3 text-foreground">
            <div class="min-w-48">{{ $row->save_as }}</div>
            <a href="{{ route('incoming-purchases.show', $row->id) }}" class="text-xs text-primary-text underline">Change</a>
          </td>

          <td class="px-4 py-3">
            <div class="flex items-center gap-2">
              {{-- Every detail and document of the sale, before saying OK --}}
              <a href="{{ route('incoming-purchases.show', $row->id) }}" title="View" aria-label="View sale {{ $row->sale->voucher_no }}" class="flex h-10 items-center gap-1.5 rounded-lg border border-input px-3 text-sm font-medium text-foreground transition-colors hover:bg-accent">
                <i data-lucide="eye" class="size-4"></i> View
              </a>
              <form id="accept-{{ $row->id }}" action="{{ route('incoming-purchases.accept', $row->id) }}" method="POST">
                @csrf
                @php $type = $row->defaults['type']; @endphp
                <input type="hidden" name="voucher_type" value="{{ $type }}">
                <input type="hidden" name="dr_ledger_id" value="{{ $row->defaults['by_type'][$type]['dr'] }}">
                <input type="hidden" name="cr_ledger_id" value="{{ $row->defaults['by_type'][$type]['cr'] }}">
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
          <td colspan="8" class="px-4 py-8 text-center text-muted-foreground">Nothing waiting. When another company on this software makes a sale to you, it shows up here.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

  <!-- Sales you had saved that the seller then deleted or moved to another customer. Your voucher is still
       in your books: delete it if you don't need it, then Dismiss (or Dismiss to keep it) -->
  @if($withdrawn->isNotEmpty())
  <h2 class="mt-8 text-base font-semibold text-foreground">Withdrawn by seller ({{ $withdrawn->count() }})</h2>
  <p class="mt-0.5 text-sm text-muted-foreground">
    The seller deleted these sales or changed their customer after you saved them. Your voucher is still in your books: open it and delete it if you don't need it.
  </p>

  <div class="list-table mt-2 overflow-x-auto">
    <table class="chart-table w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">From Company</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Sale No</th>
          <th class="px-4 py-3 text-end font-semibold">Amount</th>
          <th class="px-4 py-3 text-start font-semibold">What Happened</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Your Voucher</th>
          <th class="px-4 py-3 text-start font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">
        @foreach($withdrawn as $row)
        <tr>
          <td class="px-4 py-3 text-foreground">{{ $row->fromCompany->company_name }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            <a href="{{ route('incoming-purchases.show', $row->id) }}" class="text-primary-text">{{ $row->sale_no }}</a>
          </td>
          <td class="whitespace-nowrap px-4 py-3 text-end text-foreground">{{ number_format($row->purchase->amount, 2) }}</td>
          <td class="whitespace-nowrap px-4 py-3 text-destructive-text">{{ $row->saleWasDeleted() ? 'Sale deleted' : 'Customer changed' }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            <a href="{{ route($row->purchase->type . '.show', $row->purchase->id) }}" class="text-primary-text">{{ $row->purchase->voucher_no }}</a>
          </td>
          <td class="px-4 py-3">
            <form action="{{ route('incoming-purchases.dismiss', $row->id) }}" method="POST">
              @csrf
              <button type="submit" title="Remove from this list (your voucher is not changed)" class="h-10 rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
                Dismiss
              </button>
            </form>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <!-- The last ones rejected. Accepted ones are not listed here: they are in their voucher's register
       (Purchase / Journal / Payment), and their View page there says which sale they came from -->
  @if($rejected->isNotEmpty())
  <h2 class="mt-8 text-base font-semibold text-foreground">Rejected</h2>

  <div class="list-table mt-2 overflow-x-auto">
    <table class="chart-table w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">From Company</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Sale No</th>
          <th class="px-4 py-3 text-end font-semibold">Amount</th>
          <th class="whitespace-nowrap px-4 py-3 text-start font-semibold">Rejected By</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">
        @foreach($rejected as $row)
        <tr>
          <td class="px-4 py-3 text-foreground">{{ $row->fromCompany->company_name }}</td>
          <td class="whitespace-nowrap px-4 py-3">
            <a href="{{ route('incoming-purchases.show', $row->id) }}" class="text-primary-text">{{ $row->sale_no }}</a>
          </td>
          <td class="whitespace-nowrap px-4 py-3 text-end text-foreground">{{ number_format($row->sale->amount, 2) }}</td>
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
