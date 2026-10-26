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

      {{-- Saved before and then edited by the seller: OK updates that voucher (its type can't change) --}}
      @if($entry->purchase)
      <p class="col-full text-sm text-muted-foreground">The seller edited this sale after you saved it. OK updates {{ $entry->purchase->voucher_no }}.</p>
      @endif

      @php
        $controller = \App\Http\Controllers\Voucher\IncomingPurchaseController::class;

        // Debit / Credit dropdown options of each voucher type, also used by the script below
        $options = [];
        foreach ($choices as $optionType => $choice) {
          foreach (['dr', 'cr'] as $side) {
            $list = $choice[$side]['ledgers']->map(fn ($ledger) => ['id' => (string) $ledger->id, 'name' => $ledger->name])->all();
            if ($choice[$side]['purchase']) {
              $list[] = ['id' => $controller::NEW_PURCHASE, 'name' => '+ New ledger: Purchase'];
            }
            if ($choice[$side]['supplier']) {
              $list[] = ['id' => $controller::NEW_SUPPLIER, 'name' => '+ New ledger: ' . $seller->company_name];
            }
            $options[$optionType][$side] = $list;
          }
        }

        // After a failed OK the form comes back with what was picked
        $type = $entry->purchase ? $entry->purchase->type : old('voucher_type', $entry->defaults['type']);
        $picked = [
          'dr' => old('dr_ledger_id', $entry->defaults['by_type'][$type]['dr']),
          'cr' => old('cr_ledger_id', $entry->defaults['by_type'][$type]['cr']),
        ];
      @endphp

      <!-- How it is saved in your books. Changing the Voucher Type changes which ledgers each side lists -->
      <form id="accept-form" action="{{ route('incoming-purchases.accept', $entry->id) }}" method="POST" style="display: contents">
        @csrf
        <div>
          <label for="voucher_type" class="text-xs text-muted-foreground">Save As (Voucher Type)</label>
          <select id="voucher_type" name="voucher_type" @disabled($entry->purchase) class="mt-1 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
            @foreach($choices as $optionType => $choice)
              <option value="{{ $optionType }}" @selected($type == $optionType)>{{ $choice['label'] }}</option>
            @endforeach
          </select>
        </div>
        @foreach(['dr' => 'Debit Account (Dr)', 'cr' => 'Credit Account (Cr)'] as $side => $label)
        <div>
          <label for="{{ $side }}_ledger_id" class="text-xs text-muted-foreground">{{ $label }}</label>
          <select id="{{ $side }}_ledger_id" name="{{ $side }}_ledger_id" class="mt-1 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
            <option value="">Select</option>
            @foreach($options[$type][$side] as $option)
              <option value="{{ $option['id'] }}" @selected($picked[$side] == $option['id'])>{{ $option['name'] }}</option>
            @endforeach
          </select>
        </div>
        @endforeach
      </form>

      <div class="col-full flex flex-wrap justify-center gap-2 pt-2">
        <button type="submit" form="accept-form" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          OK
        </button>
        <form action="{{ route('incoming-purchases.reject', $entry->id) }}" method="POST" onsubmit="return confirm('Reject sale {{ $sale->voucher_no }}? It will not be saved in your books.')">
          @csrf
          <button type="submit" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-destructive-text transition-colors hover:bg-accent">
            Reject
          </button>
        </form>
        <a href="{{ route('incoming-purchases') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          List
        </a>
      </div>

    @elseif($entry->status == 'Withdrawn')

      {{-- Saved here, then the seller changed the sale's customer. The voucher stays until this company deletes it --}}
      <div class="col-full rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--destructive) 10%, transparent)">
        <p class="text-sm text-destructive-text">
          The seller changed the customer of this sale after you saved it, so it is no longer a sale to you.
          @if($entry->purchase)
            Your voucher <a href="{{ route($entry->purchase->type . '.show', $entry->purchase->id) }}" class="font-medium underline">{{ $entry->purchase->voucher_no }}</a>
            is still in your books: open it and delete it if you don't need it.
          @else
            Your voucher for it has been deleted.
          @endif
        </p>
      </div>

      <div class="col-full flex flex-wrap justify-center gap-2 pt-2">
        @if($entry->purchase && !$entry->handled_by)
        <form action="{{ route('incoming-purchases.dismiss', $entry->id) }}" method="POST">
          @csrf
          <button type="submit" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
            Dismiss
          </button>
        </form>
        @endif
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
            &nbsp;|&nbsp; Saved as <a href="{{ route($entry->purchase->type . '.show', $entry->purchase->id) }}" class="text-primary-text">{{ $entry->purchase->voucher_no }}</a>
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

@if($entry->status == 'Pending')
@push('scripts')
<script>
  // Voucher Type changed: refill the Debit / Credit dropdowns with that type's ledgers,
  // each starting on what OK would use for that type
  (function() {
    var options = @json($options);
    var defaults = @json($entry->defaults['by_type']);
    var typeSelect = document.getElementById("voucher_type");

    function fill(side, type) {
      var select = document.getElementById(side + "_ledger_id");
      select.innerHTML = '<option value="">Select</option>';
      options[type][side].forEach(function(option) {
        var el = new Option(option.name, option.id);
        el.selected = String(defaults[type][side]) === option.id;
        select.add(el);
      });
    }

    typeSelect.addEventListener("change", function() {
      fill("dr", typeSelect.value);
      fill("cr", typeSelect.value);
    });
  })();
</script>
@endpush
@endif
