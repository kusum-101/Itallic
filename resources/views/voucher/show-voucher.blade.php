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

  {{-- A sale to / purchase from another company on this software (Incoming Vouchers) --}}
  @if($entry->sentTo)
    <p class="mt-2 text-sm text-muted-foreground">
      Sent to <span class="font-medium text-foreground">{{ $entry->sentTo->toCompany->company_name }}</span>:
      {{ ['Pending' => 'waiting for their OK', 'Accepted' => 'saved in their books', 'Rejected' => 'rejected by them (edit this sale to send it again)'][$entry->sentTo->status] }}
    </p>
  @elseif($entry->receivedFrom)
    <p class="mt-2 text-sm text-muted-foreground">
      From <span class="font-medium text-foreground">{{ $entry->receivedFrom->fromCompany->company_name }}</span>'s sale {{ $entry->receivedFrom->sale->voucher_no }}
    </p>
  @endif

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
      $voucher['label'] . ' No'     => $entry->voucher_no,
      $voucher['label'] . ' Date'   => $entry->date->format('d-m-Y'),
      'Financial Year'              => $entry->financial_year,
      'Reference'                   => $entry->reference,
      'Debit Account (Dr)'          => $entry->drLedger->name,
      'Credit Account (Cr)'         => $entry->crLedger->name,
      'Bill No'                     => $entry->bill_no ?: '-',
      'Quote Amount'                => $entry->quote_amount === null ? '-' : number_format($entry->quote_amount, 2),
      $voucher['label'] . ' Amount' => number_format($entry->amount, 2),
      'Due Amount'                  => number_format($entry->due_amount, 2),
      'Created'                     => ($entry->creator->name ?? '-') . ', ' . $entry->created_at->format('d-m-Y h:i A'),
      'Verified'                    => $entry->isVerified() ? ($entry->verifier->name ?? '-') . ', ' . $entry->verified_at->format('d-m-Y h:i A') : '-',
    ] as $label => $value)
    <div>
      <p class="text-xs text-muted-foreground">{{ $label }}</p>
      <p class="mt-1 text-sm font-medium text-foreground">{{ $value }}</p>
    </div>
    @endforeach

    <div class="col-full">
      <p class="text-xs text-muted-foreground">Narration</p>
      <p class="mt-1 text-sm text-foreground" style="white-space: pre-line">{{ $entry->narration ?: '-' }}</p>
    </div>

    <div class="col-full">
      <p class="text-xs text-muted-foreground">Document Proof</p>
      @if($entry->documents->isEmpty())
        <p class="mt-1 text-sm text-foreground">-</p>
      @else
        <ul class="mt-1 flex flex-wrap gap-2">
          @foreach($entry->documents as $document)
          <li class="rounded-lg border border-input bg-background px-3 py-2 text-sm">
            <a href="{{ route('voucher-document', $document->id) }}" target="_blank" class="text-primary-text underline">{{ $document->original_name }}</a>
            <span class="text-xs text-muted-foreground">{{ number_format($document->size / 1024) }} KB</span>
          </li>
          @endforeach
        </ul>
      @endif
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
