@extends('layouts.app')

@section('title', 'Dashboard')

@php
  // Amount in the Indian way: 245267.8 -> ₹2,45,267.80 (lakhs and crores, not millions)
  $rupees = function ($amount) {
      $sign = $amount < 0 ? '-' : '';
      [$whole, $paise] = explode('.', number_format(abs($amount), 2, '.', ''));

      // The last 3 digits stay together, the rest goes in pairs: 245267 -> 2,45,267
      $lastThree = substr($whole, -3);
      $rest = substr($whole, 0, -3);
      if ($rest !== '') {
          $lastThree = ',' . $lastThree;
          $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
      }

      return $sign . '₹' . $rest . $lastThree . '.' . $paise;
  };

  // Business operation cards: label, amount, icon, colour
  $operations = [
      ['label' => 'Total Sale',     'amount' => $totalSale,     'icon' => 'trending-up', 'color' => 'chart-1', 'url' => route('sales-register')],
      ['label' => 'Total Purchase', 'amount' => $totalPurchase, 'icon' => 'receipt',     'color' => 'chart-2', 'url' => route('purchase-register')],
      ['label' => 'Total Expenses', 'amount' => $totalExpenses, 'icon' => 'wallet',      'color' => 'destructive', 'url' => route('payment-register')],
  ];

  // Quick Access tiles
  $quickLinks = [
      ['label' => 'Create Ledger',          'icon' => 'book-open',   'color' => 'chart-1', 'url' => route('ledgers.create')],
      ['label' => 'Create Item',            'icon' => 'boxes',       'color' => 'chart-4', 'url' => route('items.create')],
      ['label' => 'Create Sales',           'icon' => 'trending-up', 'color' => 'chart-2', 'url' => route('sales')],
      ['label' => 'Create Purchase',        'icon' => 'receipt',     'color' => 'chart-3', 'url' => route('purchase')],
      ['label' => 'Create Receipt',         'icon' => 'banknote',    'color' => 'chart-4', 'url' => route('receipt')],
      ['label' => 'Create Payment',         'icon' => 'credit-card', 'color' => 'chart-5', 'url' => route('payment')],
      ['label' => 'Create Material Inward', 'icon' => 'layers',      'color' => 'chart-1', 'url' => route('material-inward')],
      ['label' => 'Create Journal',         'icon' => 'scroll-text', 'color' => 'chart-2', 'url' => route('journal')],
  ];

  $stepsDone = collect($setupSteps)->where('done', true)->count();
@endphp

@section('content')
      <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <!-- Left side -->
        <div class="space-y-4 xl:col-span-2">
          <!-- Page header -->
          <!-- (The financial year and company are in the header) -->
          <div class="rounded-xl border border-border bg-card px-4 py-3">
            <h1 class="text-lg font-semibold text-foreground">Dashboard</h1>
          </div>

          <!-- Business operations: this financial year's totals -->
          <div class="rounded-xl border border-border bg-card p-4">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
              <h2 class="text-base font-semibold text-foreground">Business operations</h2>
              <p class="text-xs text-muted-foreground">{{ $yearStart->format('d M Y') }} to {{ $yearEnd->format('d M Y') }}</p>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
              @foreach ($operations as $card)
                <a href="{{ $card['url'] }}" class="flex items-center gap-3 rounded-lg border border-border p-3 transition-colors hover:bg-muted/40">
                  <span class="flex size-11 shrink-0 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--{{ $card['color'] }}) 14%, transparent)">
                    <i data-lucide="{{ $card['icon'] }}" class="size-5" style="color:var(--{{ $card['color'] }}-text)"></i>
                  </span>
                  <span class="min-w-0">
                    <span class="block truncate font-mono text-lg font-bold tabular-nums text-foreground">{{ $rupees($card['amount']) }}</span>
                    <span class="block text-sm text-muted-foreground">{{ $card['label'] }}</span>
                  </span>
                </a>
              @endforeach
            </div>
          </div>

          <!-- Money to receive and to pay -->
          <div class="rounded-xl border border-border bg-card p-4">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
              <h2 class="text-base font-semibold text-foreground">Receivable &amp; Payable</h2>
              <p class="text-xs text-muted-foreground">As on <span class="font-medium text-foreground">{{ $today->format('d M Y') }}</span></p>
            </div>
            <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
              @foreach ([['Total Receivable Amount', $receivable, $receivableChange], ['Total Payable Amount', $payable, $payableChange]] as [$label, $amount, $change])
                <div class="flex items-start justify-between gap-3 rounded-lg border border-border p-3">
                  <div class="min-w-0">
                    <p class="truncate font-mono text-xl font-bold tabular-nums text-foreground">{{ $rupees($amount) }}</p>
                    <p class="text-sm text-muted-foreground">{{ $label }}</p>
                  </div>
                  <div class="shrink-0 text-end">
                    @if ($change === null)
                      {{-- Nothing last month to compare with --}}
                      <span class="inline-flex items-center rounded-md bg-muted px-2 py-0.5 text-xs font-medium text-muted-foreground">New</span>
                    @else
                      <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 font-mono text-xs font-medium tabular-nums {{ $change >= 0 ? 'text-success-text' : 'text-destructive-text' }}" style="background-color:color-mix(in oklch, var({{ $change >= 0 ? '--success' : '--destructive' }}) 12%, transparent)">
                        <i data-lucide="{{ $change >= 0 ? 'trending-up' : 'trending-down' }}" class="size-3.5"></i>{{ abs($change) }}%
                      </span>
                    @endif
                    <p class="mt-1 text-xs text-muted-foreground">vs last month</p>
                  </div>
                </div>
              @endforeach
            </div>
          </div>

          <!-- Quick Access -->
          <div class="rounded-xl border border-border bg-card p-4">
            <h2 class="text-base font-semibold text-foreground">Quick Access</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-4">
              @foreach ($quickLinks as $link)
                <a href="{{ $link['url'] }}" class="flex flex-col items-center gap-2 rounded-lg border border-border px-2 py-4 text-center transition-colors hover:border-primary/40 hover:bg-muted/40">
                  <span class="flex size-11 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--{{ $link['color'] }}) 14%, transparent)">
                    <i data-lucide="{{ $link['icon'] }}" class="size-5" style="color:var(--{{ $link['color'] }}-text)"></i>
                  </span>
                  <span class="text-sm font-medium text-foreground">{{ $link['label'] }}</span>
                </a>
              @endforeach
            </div>
          </div>

          <!-- Newest account vouchers -->
          <div class="rounded-xl border border-border bg-card">
            <div class="border-b border-border px-4 py-3">
              <h2 class="text-base font-semibold text-foreground">Recent vouchers</h2>
            </div>
            @if ($recentVouchers->isEmpty())
              <p class="px-4 py-6 text-center text-sm text-muted-foreground">No vouchers yet. Use Quick Access to make the first one.</p>
            @else
              <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                  <thead>
                    <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground">
                      <th class="px-4 py-2.5">No</th>
                      <th class="px-4 py-2.5">Date</th>
                      <th class="px-4 py-2.5">Debit (Dr)</th>
                      <th class="px-4 py-2.5">Credit (Cr)</th>
                      <th class="px-4 py-2.5 text-end">Amount</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-border">
                    @foreach ($recentVouchers as $row)
                      <tr class="transition-colors hover:bg-muted/40">
                        <td class="whitespace-nowrap px-4 py-2.5"><a href="{{ route($row->type . '.show', $row->id) }}" class="font-medium text-primary hover:underline">{{ $row->voucher_no }}</a></td>
                        <td class="whitespace-nowrap px-4 py-2.5 text-muted-foreground">{{ $row->date->format('d-m-Y') }}</td>
                        <td class="px-4 py-2.5 text-foreground">{{ $row->drLedger->name ?? '' }}</td>
                        <td class="px-4 py-2.5 text-foreground">{{ $row->crLedger->name ?? '' }}</td>
                        <td class="whitespace-nowrap px-4 py-2.5 text-end font-mono tabular-nums text-foreground">{{ $rupees($row->amount) }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>

        <!-- Right side: Setup Guide -->
        <div>
          <div class="rounded-xl border border-border bg-card p-4">
            <h2 class="text-base font-semibold text-foreground">Setup Guide</h2>
            <p class="mt-1 text-sm text-muted-foreground">Get started by completing the setup tasks below</p>

            <ul class="mt-3 space-y-2">
              @foreach ($setupSteps as $i => $step)
                <li>
                  <a href="{{ $step['url'] }}" class="flex items-center gap-3 rounded-lg border border-border px-3 py-2.5 text-sm transition-colors hover:bg-muted/40">
                    @if ($step['done'])
                      <span class="flex size-5 shrink-0 items-center justify-center rounded border border-primary bg-primary/10 text-primary"><i data-lucide="check" class="size-3.5"></i></span>
                      <span class="text-muted-foreground line-through">{{ $i + 1 }}. {{ $step['label'] }}</span>
                    @else
                      <span class="size-5 shrink-0 rounded border border-input"></span>
                      <span class="text-foreground">{{ $i + 1 }}. {{ $step['label'] }}</span>
                    @endif
                  </a>
                </li>
              @endforeach
            </ul>

            <!-- How much of the setup is done -->
            <div class="mt-4">
              <div class="flex justify-between text-xs text-muted-foreground">
                <span>{{ $stepsDone }} of {{ count($setupSteps) }} done</span>
                <span>{{ round($stepsDone / count($setupSteps) * 100) }}%</span>
              </div>
              <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary" style="width: {{ $stepsDone / count($setupSteps) * 100 }}%"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
@endsection
