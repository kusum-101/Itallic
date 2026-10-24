@extends('layouts.app')

@section('title', $voucher['label'] . ' ' . $entry->voucher_no)

@section('content')

  <!-- Page heading -->
  <div class="flex flex-wrap items-center gap-2">
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route($type . '-register')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $voucher['label'] }} {{ $entry->voucher_no }}</h1>
    </div>
    <!-- status-badge (public/css/custom.css): green when verified, red when not -->
    <span class="status-badge {{ $entry->isVerified() ? 'verified' : 'not-verified' }}">{{ $entry->status }}</span>
  </div>

  <!-- Success message (after Verify / Unverify) -->
  @if(session('success'))
  <div class="mt-4 rounded-lg border border-border px-4 py-3" style="background: color-mix(in oklch, var(--success) 10%, transparent)">
    <p class="text-sm text-success-text">{{ session('success') }}</p>
  </div>
  @endif

  {{-- Every detail of the voucher, read only. Same grid as the Add form (form-grid, public/css/custom.css).
       Label => value, written once in the loop --}}
  <div class="form-grid mt-6 rounded-xl border border-border bg-card p-6">

    @foreach([
      $voucher['label'] . ' No'   => $entry->voucher_no,
      $voucher['label'] . ' Date' => $entry->date->format('d-m-Y'),
      'Financial Year'            => $entry->financial_year,
      'Party'                     => $entry->party->name ?? '-',
      'Challan / Bill No'         => $entry->reference_no ?: '-',
      'Total Value'               => number_format($entry->lines->sum('value'), 2),
      'Created'                   => ($entry->creator->name ?? '-') . ', ' . $entry->created_at->format('d-m-Y h:i A'),
      'Verified'                  => $entry->isVerified() ? ($entry->verifier->name ?? '-') . ', ' . $entry->verified_at->format('d-m-Y h:i A') : '-',
    ] as $label => $value)
    <div>
      <p class="text-xs text-muted-foreground">{{ $label }}</p>
      <p class="mt-1 text-sm font-medium text-foreground">{{ $value }}</p>
    </div>
    @endforeach

    <!-- Item rows -->
    <div class="col-full overflow-x-auto rounded-lg border border-border">
      <table class="chart-table w-full text-sm">
        <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
          <tr>
            <th class="px-4 py-3 text-start font-semibold">S.No.</th>
            <th class="px-4 py-3 text-start font-semibold">Item</th>
            <th class="px-4 py-3 text-start font-semibold">Godown</th>
            <th class="px-4 py-3 text-end font-semibold">Quantity</th>
            <th class="px-4 py-3 text-end font-semibold">Rate</th>
            <th class="px-4 py-3 text-end font-semibold">Value</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-border">
          @foreach($entry->lines as $line)
          <tr>
            <td class="px-4 py-3 text-muted-foreground">{{ $loop->iteration }}</td>
            <td class="px-4 py-3 font-medium text-foreground">{{ $line->item->name }}</td>
            <td class="px-4 py-3 text-muted-foreground">{{ $line->godown->name }}</td>
            {{-- e.g. 12.500 Kg, or 2 Box 6 Pcs for a compound unit --}}
            <td class="whitespace-nowrap px-4 py-3 text-end text-foreground">{{ $line->item->unit->formatQuantity($line->quantity()) }}</td>
            <td class="px-4 py-3 text-end text-muted-foreground">{{ number_format($line->rate, 2) }}</td>
            <td class="px-4 py-3 text-end text-foreground">{{ number_format($line->value, 2) }}</td>
          </tr>
          @endforeach
        </tbody>
        <tfoot class="border-t border-border font-medium text-foreground">
          <tr>
            <td class="px-4 py-3 text-end" colspan="5">Total</td>
            <td class="px-4 py-3 text-end">{{ number_format($entry->lines->sum('value'), 2) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>

    <div class="col-full">
      <p class="text-xs text-muted-foreground">Narration</p>
      <p class="mt-1 text-sm text-foreground" style="white-space: pre-line">{{ $entry->narration ?: '-' }}</p>
    </div>

    <!-- A verified voucher is locked: Edit comes back after "Unverify" -->
    <div class="col-full flex flex-wrap justify-center gap-2 pt-2">
      @if(!$entry->isVerified())
      <a href="{{ route($type . '.edit', $entry->id) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        Edit
      </a>
      @endif

      <form action="{{ route($type . '.verify', $entry->id) }}" method="POST">
        @csrf
        <button type="submit" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          {{ $entry->isVerified() ? 'Unverify' : 'Verify' }}
        </button>
      </form>

      <a href="{{ route($type . '-register') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        List
      </a>
    </div>
  </div>

@endsection
