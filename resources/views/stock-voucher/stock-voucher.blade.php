@extends('layouts.app')

@section('title', ($entry ? 'Edit ' : 'Add ') . $voucher['label'])

@php
  // Saved rows (Edit page) or the form's old values (back with an error); one empty row to start with
  if (session()->hasOldInput()) {
    $lines = old('lines', []);
  }

  if (!$lines) {
    $lines = [[]];
  }
@endphp

@section('content')

  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route($type . '-register')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $entry ? 'Edit' : 'Add' }} {{ $voucher['label'] }}</h1>
    </div>
    <p class="mt-0.5 text-sm text-muted-foreground">{{ $entry ? 'Change the details of ' . $entry->voucher_no . '.' : 'Record material that came into a godown.' }}</p>
  </div>

  {{-- One form for every inventory voucher type: $type / $voucher come from StockVoucherController.
       The same form is the Add page ($entry is null) and the Edit page ($entry is the saved voucher).
       form-grid (public/css/custom.css): 1 field per row on mobile, 2 on tablet, 4 on big screens.
       data-enter-next (layouts/app): Enter moves to the next field instead of saving.
       data-keep-draft (layouts/app): what is typed stays after a page reload. --}}
  <form id="stock-voucher-form" action="{{ $entry ? route($type . '.update', $entry->id) : route($type . '.store') }}" method="POST" data-enter-next data-keep-draft class="form-grid mt-4 form-card">
    @csrf
    <div class="form-card-head col-full">{{ $voucher['label'] }} Details</div>
    @if($entry)
      @method('PUT')
    @endif

    <div>
      <label for="voucher_no" class="block text-sm font-medium text-foreground">{{ $voucher['label'] }} No</label>
      <input
        type="text"
        id="voucher_no"
        value="{{ $entry?->voucher_no }}"
        readonly
        tabindex="-1"
        placeholder="Auto generated"
        class="mt-1.5 h-10 w-full cursor-not-allowed rounded-lg border border-input bg-muted px-3 text-sm text-muted-foreground outline-none">
    </div>

    <div>
      <label for="financial_year" class="block text-sm font-medium text-foreground">Financial Year</label>
      <input
        type="text"
        id="financial_year"
        readonly
        tabindex="-1"
        class="mt-1.5 h-10 w-full cursor-not-allowed rounded-lg border border-input bg-muted px-3 text-sm text-muted-foreground outline-none">
    </div>

    <!-- Date sits on the right end of the first row on big screens -->
    <div class="col-end">
      <label for="date" class="block text-sm font-medium text-foreground">{{ $voucher['label'] }} Date <span class="text-destructive-text">*</span></label>
      <input
        type="date"
        id="date"
        name="date"
        value="{{ old('date', ($entry?->date ?? \App\Support\FinancialYear::defaultDate())->format('Y-m-d')) }}"
        {{-- Only dates of the voucher's financial year: the year picked in the header for a new one --}}
        @php $dateYear = $entry ? \App\Support\FinancialYear::of($entry->date) : \App\Support\FinancialYear::selected(); @endphp
        min="{{ \App\Support\FinancialYear::start($dateYear)->format('Y-m-d') }}"
        max="{{ \App\Support\FinancialYear::end($dateYear)->format('Y-m-d') }}"
        required
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('date')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="col-2">
      <div class="flex items-center gap-1.5">
        <label for="ledger_id" class="text-sm font-medium text-foreground">Party</label>
        @include('components.add-new', ['route' => 'ledgers.create', 'title' => 'Add new ledger'])
      </div>
      <select id="ledger_id" name="ledger_id" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
        <option value="">None</option>
        @foreach($parties as $option)
          <option value="{{ $option->id }}" @selected(old('ledger_id', $entry?->ledger_id) == $option->id)>{{ $option->name }}</option>
        @endforeach
      </select>
      <p class="mt-1 text-xs text-muted-foreground">Who the material came from. Ledgers under {{ implode(' / ', $voucher['party']) }}.</p>
      @error('ledger_id')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="reference_no" class="block text-sm font-medium text-foreground">Challan / Bill No</label>
      <input
        type="text"
        id="reference_no"
        name="reference_no"
        value="{{ old('reference_no', $entry?->reference_no) }}"
        maxlength="100"
        placeholder="e.g. CH-101"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('reference_no')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <!-- Items: one row per item -->
    <div class="col-full border-t border-border pt-4">
      <div class="flex items-center gap-1.5">
        <h2 class="text-base font-semibold text-foreground">Items</h2>
        @include('components.add-new', ['route' => 'items.create', 'title' => 'Add new item'])
      </div>

      <div class="mt-3 overflow-x-auto">
        <table class="form-table w-full text-sm">
          <thead>
            <tr>
              <th class="px-2 py-2 text-start font-semibold">Item <span class="text-destructive-text">*</span></th>
              <th class="px-2 py-2 text-start font-semibold">
                <span class="flex items-center gap-1.5">
                  Godown <span class="text-destructive-text">*</span>
                  @include('components.add-new', ['route' => 'godowns.create', 'title' => 'Add new godown'])
                </span>
              </th>
              <th class="px-2 py-2 text-start font-semibold">Quantity <span class="text-destructive-text">*</span></th>
              <th class="px-2 py-2 text-start font-semibold">Rate</th>
              <th class="px-2 py-2 text-end font-semibold">Value</th>
              <th class="px-2 py-2"></th>
            </tr>
          </thead>

          {{-- data-draft-list / data-draft-template (layouts/app): rows added here come back after a page reload --}}
          <tbody data-line-list data-draft-list="lines" data-draft-template="line-template">
            @foreach($lines as $i => $row)
              @include('stock-voucher.line-row', ['i' => $i, 'row' => $row])
            @endforeach
          </tbody>

          <!-- Total value of all rows (quantities are in different units, so they are not added up) -->
          <tfoot class="border-t border-border font-medium text-foreground">
            <tr>
              <td class="px-2 py-3" colspan="3"></td>
              <td class="px-2 py-3 text-end">Total</td>
              <td class="px-2 py-3 text-end" data-total-value>0.00</td>
              <td class="px-2 py-3"></td>
            </tr>
          </tfoot>
        </table>
      </div>

      @error('lines')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror

      <button type="button" data-add-line class="mt-3 flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        <i data-lucide="plus" class="size-4"></i> Add Item
      </button>

      <!-- A new, empty row. "+ Add Item" copies it (__INDEX__ becomes a number) -->
      <template id="line-template">
        @include('stock-voucher.line-row', ['i' => '__INDEX__', 'row' => []])
      </template>
    </div>

    <div class="col-full">
      <label for="narration" class="block text-sm font-medium text-foreground">Narration</label>
      <textarea
        id="narration"
        name="narration"
        rows="2"
        maxlength="1000"
        placeholder="e.g. Received against PO-12"
        class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">{{ old('narration', $entry?->narration) }}</textarea>
      @error('narration')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-card-foot col-full">
      <button type="submit" id="stock-voucher-submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        {{ $entry ? 'Update' : 'Submit' }}
      </button>
      {{-- Opens the register in a new tab, so what is typed here is not lost --}}
      <a href="{{ route($type . '-register') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        <i data-lucide="list" class="size-4"></i> List
      </a>
      <a href="{{ route('dashboardUser') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        Exit
      </a>
    </div>
  </form>

@endsection

@push('scripts')
<script>
  // Item rows: + Add / Remove a row, each row's unit and Value, the Total
  (function() {
    var list = document.querySelector("[data-line-list]");
    var template = document.getElementById("line-template");

    // Number with exactly 2 digits after the point, e.g. 1500 -> "1,500.00"
    function money(number) {
      return number.toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // The picked item's unit under Quantity ("Kg", or "Pcs (1 Box = 12 Pcs)"),
    // and the step of the Quantity box (Nos: whole numbers only, Kg: 0.001)
    function applyUnit(row) {
      var select = row.querySelector("[data-line-item]");
      var selected = select.options[select.selectedIndex];
      var label = row.querySelector("[data-line-unit]");
      var input = row.querySelector("[data-line-quantity]");

      if (!selected.value) {
        label.textContent = "";
        input.step = "any";
        return;
      }

      var decimals = Number(selected.dataset.decimals || 0);
      label.textContent = selected.dataset.symbol + (selected.dataset.hint ? " (" + selected.dataset.hint + ")" : "");
      input.step = decimals ? (1 / Math.pow(10, decimals)).toFixed(decimals) : "1";
    }

    function updateTotals() {
      var total = 0;

      list.querySelectorAll("[data-line-row]").forEach(function(row) {
        var quantity = parseFloat(row.querySelector("[data-line-quantity]").value) || 0;
        var rate = parseFloat(row.querySelector("[data-line-rate]").value) || 0;
        var value = Math.round(quantity * rate * 100) / 100;

        row.querySelector("[data-line-value]").textContent = money(value);
        total += value;
      });

      document.querySelector("[data-total-value]").textContent = money(total);
    }

    function addRow() {
      // A number not used by any row yet, so the input names don't clash
      var index = list.children.length;
      while (list.querySelector('[name="lines[' + index + '][quantity]"]')) index++;

      list.insertAdjacentHTML("beforeend", template.innerHTML.replace(/__INDEX__/g, index));
      var row = list.lastElementChild;
      applyUnit(row);
      row.querySelector("[data-line-item]").focus();
    }

    document.querySelector("[data-add-line]").addEventListener("click", addRow);

    list.addEventListener("click", function(e) {
      if (!e.target.closest("[data-remove-line]")) return;
      e.target.closest("[data-line-row]").remove();
      updateTotals();
    });

    list.addEventListener("change", function(e) {
      if (e.target.matches("[data-line-item]")) applyUnit(e.target.closest("[data-line-row]"));
    });

    list.addEventListener("input", updateTotals);

    // Run once on page load too (edit page, form back with an error, or a reloaded draft)
    list.querySelectorAll("[data-line-row]").forEach(applyUnit);
    updateTotals();
  })();

  // Financial Year from the date: April to March, e.g. 09-Oct-2026 -> 26-27
  (function() {
    var dateInput = document.getElementById("date");
    var yearBox = document.getElementById("financial_year");

    function fillYear() {
      if (!dateInput.value) {
        yearBox.value = "";
        return;
      }

      var parts = dateInput.value.split("-");
      var year = Number(parts[0]);
      var start = Number(parts[1]) >= 4 ? year : year - 1;
      yearBox.value = String(start).slice(-2) + "-" + String(start + 1).slice(-2);
    }

    dateInput.addEventListener("change", fillYear);
    fillYear();
  })();

  // A double click on Submit must not save the voucher twice
  document.getElementById("stock-voucher-form").addEventListener("submit", function() {
    document.getElementById("stock-voucher-submit").disabled = true;
  });
</script>
@endpush
