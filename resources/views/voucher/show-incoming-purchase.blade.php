@extends('layouts.app')

@section('title', 'Incoming ' . $entry->sale->voucher_no)

@section('content')

  @php
    $sale = $entry->sale;
    $seller = $entry->fromCompany;
  @endphp

  <!-- Page heading -->
  <div class="flex flex-wrap items-center gap-2">
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route('incoming-purchases')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">Sale {{ $sale->voucher_no }} from {{ $seller->company_name }}</h1>
    </div>
    <!-- status-badge (public/css/custom.css): green once accepted, red while pending or rejected -->
    <span class="status-badge {{ $entry->status == 'Accepted' ? 'verified' : 'not-verified' }}">{{ $entry->status }}</span>
  </div>

  @if(session('error') || $errors->any())
  <div class="mt-4 rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--destructive) 10%, transparent)">
    <p class="text-sm text-destructive-text">{{ session('error') ?: $errors->first() }}</p>
  </div>
  @endif

  {{-- The seller's sale, read only. Same grid as the voucher View page (form-grid, public/css/custom.css) --}}
  <div class="form-grid mt-6 rounded-xl border border-border bg-card p-6">

    @foreach([
      'From Company'  => $seller->company_name,
      'Seller GST No' => $seller->gst ?: '-',
      'Seller PAN No' => $seller->pan ?: '-',
      'Seller Phone'  => $seller->company_phone_no ?: '-',
      'Sale No'       => $sale->voucher_no,
      'Sale Date'     => $sale->date->format('d-m-Y'),
      'Reference'     => $sale->reference,
      'Seller Bill No'=> $sale->bill_no ?: '-',
      'Quote Amount'  => $sale->quote_amount === null ? '-' : number_format($sale->quote_amount, 2),
      'Amount'        => number_format($sale->amount, 2),
      'Due Amount'    => number_format($sale->due_amount, 2),
      'Received'      => $entry->updated_at->format('d-m-Y h:i A'),
    ] as $label => $value)
    <div>
      <p class="text-xs text-muted-foreground">{{ $label }}</p>
      <p class="mt-1 text-sm font-medium text-foreground">{{ $value }}</p>
    </div>
    @endforeach

    <div class="col-full">
      <p class="text-xs text-muted-foreground">Narration</p>
      <p class="mt-1 text-sm text-foreground" style="white-space: pre-line">{{ $sale->narration ?: '-' }}</p>
    </div>

    <div class="col-full">
      <p class="text-xs text-muted-foreground">Document Proof</p>
      @if($sale->documents->isEmpty())
        <p class="mt-1 text-sm text-foreground">-</p>
      @else
        <ul class="mt-1 flex flex-wrap gap-2">
          @foreach($sale->documents as $document)
          <li class="rounded-lg border border-input bg-background px-3 py-2 text-sm">
            <a href="{{ route('incoming-purchases.document', [$entry->id, $document->id]) }}" target="_blank" class="text-primary-text underline">{{ $document->original_name }}</a>
            <span class="text-xs text-muted-foreground">{{ number_format($document->size / 1024) }} KB</span>
          </li>
          @endforeach
        </ul>
      @endif
    </div>

    @if($entry->status == 'Pending')

      {{-- Booked before and then edited by the seller: OK updates that Purchase --}}
      @if($entry->purchase)
      <p class="col-full text-sm text-muted-foreground">The seller edited this sale after you saved it. OK updates {{ $entry->purchase->voucher_no }}.</p>
      @endif

      <!-- How it is saved in your books. The dropdowns start on the usual accounts -->
      <form id="accept-form" action="{{ route('incoming-purchases.accept', $entry->id) }}" method="POST" style="display: contents">
        @csrf
        <div>
          <label for="purchase_ledger_id" class="text-xs text-muted-foreground">Purchase Account (Dr)</label>
          <select id="purchase_ledger_id" name="purchase_ledger_id" class="mt-1 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
            @foreach($purchaseLedgers as $ledger)
              <option value="{{ $ledger->id }}" @selected($entry->default_purchase == $ledger->id)>{{ $ledger->name }}</option>
            @endforeach
            <option value="new" @selected($entry->default_purchase == 'new')>+ New ledger: Purchase</option>
          </select>
        </div>
        <div>
          <label for="supplier_ledger_id" class="text-xs text-muted-foreground">Supplier (Cr)</label>
          <select id="supplier_ledger_id" name="supplier_ledger_id" class="mt-1 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
            @foreach($supplierLedgers as $ledger)
              <option value="{{ $ledger->id }}" @selected($entry->default_supplier == $ledger->id)>{{ $ledger->name }}</option>
            @endforeach
            <option value="new" @selected($entry->default_supplier == 'new')>+ New ledger: {{ $seller->company_name }}</option>
          </select>
        </div>
      </form>

      <div class="col-full flex flex-wrap justify-center gap-2 pt-2">
        <button type="submit" form="accept-form" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          OK
        </button>
        <form action="{{ route('incoming-purchases.reject', $entry->id) }}" method="POST" onsubmit="return confirm('Reject sale {{ $sale->voucher_no }}? It will not be saved as a purchase.')">
          @csrf
          <button type="submit" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-destructive-text transition-colors hover:bg-accent">
            Reject
          </button>
        </form>
        <a href="{{ route('incoming-purchases') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          List
        </a>
      </div>

    @else

      <div class="col-full">
        <p class="text-xs text-muted-foreground">{{ $entry->status }} By</p>
        <p class="mt-1 text-sm font-medium text-foreground">
          {{ $entry->handler->name ?? '-' }}{{ $entry->handled_at ? ', ' . $entry->handled_at->format('d-m-Y h:i A') : '' }}
          @if($entry->purchase)
            &nbsp;|&nbsp; Purchase <a href="{{ route('purchase.show', $entry->purchase->id) }}" class="text-primary-text">{{ $entry->purchase->voucher_no }}</a>
          @endif
        </p>
      </div>

      <div class="col-full flex justify-center pt-2">
        <a href="{{ route('incoming-purchases') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          List
        </a>
      </div>

    @endif
  </div>

@endsection
