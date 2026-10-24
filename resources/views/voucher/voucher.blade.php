    @extends('layouts.app')

@section('title', ($entry ? 'Edit ' : 'Add ') . $voucher['label'])

@section('content')

  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route($type . '-register')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $entry ? 'Edit' : 'Add' }} {{ $voucher['label'] }}</h1>
    </div>
    <p class="mt-0.5 text-sm text-muted-foreground">{{ $entry ? 'Change the details of ' . $entry->voucher_no . '.' : 'Move an amount from one ledger to another.' }}</p>
  </div>

  {{-- One form for every voucher type (Journal, Sales, Purchase, Receipt, Payment, Contra):
       $type / $voucher come from VoucherController and set the labels and the Dr / Cr ledgers.
       The same form is the Add page ($entry is null) and the Edit page ($entry is the saved voucher).
       form-grid (public/css/custom.css): 1 field per row on mobile, 2 on tablet, 4 on big screens.
       data-enter-next (layouts/app): Enter moves to the next field instead of saving. --}}
  <form id="voucher-form" action="{{ $entry ? route($type . '.update', $entry->id) : route($type . '.store') }}" method="POST" enctype="multipart/form-data" data-enter-next class="form-grid mt-4 form-card">
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
        {{-- Add page: the number this voucher will get (nextNumber in the controller) --}}
        value="{{ $entry?->voucher_no ?? $nextNo }}"
        readonly
        tabindex="-1"
        class="mt-1.5 h-10 w-full cursor-not-allowed rounded-lg border border-input bg-muted px-3 font-mono text-sm font-semibold text-foreground outline-none">
      @unless($entry)
        <p class="mt-1 text-xs text-muted-foreground">Next number, given when you save.</p>
      @endunless
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

    <!-- Debit Account (left) and Credit Account (right).
         Same markup for both, so it is written once in the loop -->
    {{-- options: only the ledgers this voucher type allows on that side
         (VoucherController::TYPES), e.g. Sales -> Dr: Sundry Debtors, Cr: Sales Accounts --}}
    @foreach([
      ['key' => 'dr', 'label' => 'Debit Account', 'type' => 'Dr', 'options' => $drLedgers],
      ['key' => 'cr', 'label' => 'Credit Account', 'type' => 'Cr', 'options' => $crLedgers],
    ] as $side)
    <div class="col-2">
      <div class="flex items-center gap-1.5">
        <label for="{{ $side['key'] }}_ledger_id" class="block text-sm font-medium text-foreground">{{ $side['label'] }} <span class="text-destructive-text">*</span></label>
        {{-- Opens Add Ledger in a new tab, so this half-filled voucher is not lost --}}
        @include('components.add-new', ['route' => 'ledgers.create', 'title' => 'Add new ledger'])
      </div>
      <div class="mt-1.5 flex gap-2">
        {{-- data-balance: the ledger's current balance (opening balance + vouchers), shown in the box on the right --}}
        <select id="{{ $side['key'] }}_ledger_id" name="{{ $side['key'] }}_ledger_id" required data-account-select class="h-10 min-w-0 flex-1 rounded-lg border border-input bg-background px-3 text-sm">
          <option value="">Select ledger</option>
          @foreach($side['options'] as $option)
            <option value="{{ $option->id }}" data-balance="{{ $option->balance }}" @selected(old($side['key'] . '_ledger_id', $entry?->{$side['key'] . '_ledger_id'}) == $option->id)>{{ $option->name }}</option>
          @endforeach
        </select>
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg border border-input bg-muted text-sm font-medium text-foreground">{{ $side['type'] }}</span>
        <input
          type="text"
          readonly
          tabindex="-1"
          data-balance-box
          placeholder="Balance"
          aria-label="{{ $side['label'] }} balance"
          class="h-10 w-36 shrink-0 cursor-not-allowed rounded-lg border border-input bg-muted px-3 text-sm text-muted-foreground outline-none">
      </div>
      {{-- Which groups the dropdown above is limited to (nothing for Journal: all ledgers) --}}
      @if($voucher[$side['key']])
        <p class="mt-1 text-xs text-muted-foreground">Ledgers under {{ implode(' / ', $voucher[$side['key']]) }}.</p>
      @endif
      @error($side['key'] . '_ledger_id')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>
    @endforeach

    <!-- Same ledger can't be on both sides -->
    <p id="same-ledger-error" class="col-full hidden text-sm text-destructive-text">Debit and Credit Account can't be the same ledger.</p>

    <div>
      <label for="reference" class="block text-sm font-medium text-foreground">Reference <span class="text-destructive-text">*</span></label>
      <select id="reference" name="reference" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
        <option value="">Select</option>
        @foreach(\App\Http\Controllers\Voucher\VoucherController::REFERENCES as $option)
          <option value="{{ $option }}" @selected(old('reference', $entry?->reference) == $option)>{{ $option }}</option>
        @endforeach
      </select>
      @error('reference')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="bill_no" class="block text-sm font-medium text-foreground">Bill No</label>
      <input
        type="text"
        id="bill_no"
        name="bill_no"
        value="{{ old('bill_no', $entry?->bill_no) }}"
        maxlength="100"
        placeholder="e.g. INV-101"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('bill_no')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="quote_amount" class="block text-sm font-medium text-foreground">Quote Amount</label>
      <input
        type="number"
        id="quote_amount"
        name="quote_amount"
        value="{{ old('quote_amount', $entry?->quote_amount) }}"
        step="0.01"
        min="0"
        placeholder="0.00"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('quote_amount')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="amount" class="block text-sm font-medium text-foreground">{{ $voucher['label'] }} Amount <span class="text-destructive-text">*</span></label>
      <input
        type="number"
        id="amount"
        name="amount"
        value="{{ old('amount', $entry?->amount) }}"
        step="0.01"
        min="0.01"
        required
        placeholder="0.00"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('amount')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="col-2">
      <label for="narration" class="block text-sm font-medium text-foreground">Narration</label>
      <textarea
        id="narration"
        name="narration"
        rows="2"
        maxlength="1000"
        placeholder="e.g. Being rent paid for October"
        class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">{{ old('narration', $entry?->narration) }}</textarea>
      @error('narration')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="due_amount" class="block text-sm font-medium text-foreground">Due Amount</label>
      <input
        type="text"
        id="due_amount"
        value="0.00"
        readonly
        tabindex="-1"
        class="mt-1.5 h-10 w-full cursor-not-allowed rounded-lg border border-input bg-muted px-3 text-sm text-muted-foreground outline-none">
      <p class="mt-1 text-xs text-muted-foreground">Quote Amount minus {{ $voucher['label'] }} Amount.</p>
    </div>

    <div>
      <label for="financial_year" class="block text-sm font-medium text-foreground">Financial Year</label>
      <input
        type="text"
        id="financial_year"
        readonly
        tabindex="-1"
        class="mt-1.5 h-10 w-full cursor-not-allowed rounded-lg border border-input bg-muted px-3 text-sm text-muted-foreground outline-none">
      <p class="mt-1 text-xs text-muted-foreground">Set from the {{ $voucher['label'] }} Date. It is part of the {{ $voucher['label'] }} No.</p>
    </div>

    <!-- Document Proof: pick files (more than once if needed), remove any with the cross -->
    <div class="col-full">
      <p class="block text-sm font-medium text-foreground">Document Proof</p>
      {{-- The real file input is hidden (sr-only), this label is the button that opens it --}}
      <input
        type="file"
        id="documents"
        name="documents[]"
        multiple
        accept="image/*,.pdf"
        class="sr-only">
      <label for="documents" class="mt-1.5 inline-flex cursor-pointer items-center gap-1.5 rounded-lg border border-input bg-background px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        <i data-lucide="paperclip" class="size-4"></i> Choose Files
      </label>
      <p class="mt-1 text-xs text-muted-foreground">Images or PDF, up to 2 MB each. You can pick more than once.</p>
      <ul id="document-list" class="mt-3 flex flex-wrap gap-2"></ul>
      @error('documents')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
      @error('documents.*')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror

      {{-- Edit page: the documents already saved. Tick "Remove" to delete one on Update --}}
      @if($entry && $entry->documents->isNotEmpty())
        <p class="mt-3 text-xs text-muted-foreground">Saved documents:</p>
        <ul class="mt-1 flex flex-wrap gap-2">
          @foreach($entry->documents as $document)
          <li class="flex items-center gap-2 rounded-lg border border-input bg-background px-3 py-2 text-sm text-foreground">
            <a href="{{ route('voucher-document', $document->id) }}" target="_blank" class="max-w-xs truncate text-primary-text underline" title="{{ $document->original_name }}">{{ $document->original_name }}</a>
            <label class="flex items-center gap-1 text-xs text-destructive-text">
              <input type="checkbox" name="remove_documents[]" value="{{ $document->id }}" @checked(in_array($document->id, (array) old('remove_documents', [])))> Remove
            </label>
          </li>
          @endforeach
        </ul>
      @endif
    </div>

    <div class="form-card-foot col-full">
      <button type="submit" id="voucher-submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        {{ $entry ? 'Update' : 'Submit' }}
      </button>
      <a href="{{ route($type . '-register') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        List
      </a>
      <a href="{{ route('dashboardUser') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        Exit
      </a>
    </div>
  </form>

@endsection

@push('scripts')
<script>
  // Debit / Credit Account: show the picked ledger's balance, and
  // don't allow the same ledger on both sides
  (function() {
    var selects = document.querySelectorAll("[data-account-select]");
    var drSelect = selects[0];
    var crSelect = selects[1];
    var sameError = document.getElementById("same-ledger-error");

    function showBalance(select) {
      var selected = select.options[select.selectedIndex];
      var box = select.parentElement.querySelector("[data-balance-box]");
      box.value = selected.dataset.balance || "";
    }

    function checkSame() {
      var same = drSelect.value !== "" && drSelect.value === crSelect.value;
      sameError.classList.toggle("hidden", !same);
      // setCustomValidity stops the submit while both sides are the same
      crSelect.setCustomValidity(same ? "Debit and Credit Account can't be the same ledger." : "");
    }

    selects.forEach(function(select) {
      select.addEventListener("change", function() {
        showBalance(select);
        checkSame();
      });

      // Run once on page load too (e.g. when the form comes back with an error)
      showBalance(select);
    });

    checkSame();
  })();

  // Due Amount = Quote Amount - voucher Amount (never below 0)
  (function() {
    var quoteInput = document.getElementById("quote_amount");
    var amountInput = document.getElementById("amount");
    var dueInput = document.getElementById("due_amount");

    function fillDue() {
      var quote = parseFloat(quoteInput.value) || 0;
      var amount = parseFloat(amountInput.value) || 0;
      dueInput.value = Math.max(quote - amount, 0).toFixed(2);
    }

    quoteInput.addEventListener("input", fillDue);
    amountInput.addEventListener("input", fillDue);
    fillDue();
  })();

  // Financial Year from the voucher Date: April to March,
  // e.g. 09-Oct-2026 -> 26-27, 15-Feb-2027 -> 26-27
  (function() {
    var dateInput = document.getElementById("date");
    var yearInput = document.getElementById("financial_year");

    function fillYear() {
      if (!dateInput.value) {
        yearInput.value = "";
        return;
      }

      var parts = dateInput.value.split("-");
      var year = parseInt(parts[0], 10);
      var month = parseInt(parts[1], 10);
      var start = month >= 4 ? year : year - 1;

      yearInput.value = String(start).slice(-2) + "-" + String(start + 1).slice(-2);
    }

    dateInput.addEventListener("change", fillYear);
    fillYear();
  })();

  // Document Proof: keep every picked file in one list, so picking again adds
  // to it instead of replacing it, and each file can be removed with its cross
  (function() {
    var fileInput = document.getElementById("documents");
    var list = document.getElementById("document-list");
    var files = [];

    // Put our list back into the file input, so it is what the form sends
    function syncInput() {
      var transfer = new DataTransfer();
      files.forEach(function(file) {
        transfer.items.add(file);
      });
      fileInput.files = transfer.files;
    }

    function render() {
      list.innerHTML = "";

      files.forEach(function(file, index) {
        var item = document.createElement("li");
        item.className = "flex items-center gap-2 rounded-lg border border-input bg-background px-2.5 py-1.5 text-sm text-foreground";

        var name = document.createElement("span");
        name.className = "max-w-xs truncate";
        name.textContent = file.name;
        name.title = file.name;

        var size = document.createElement("span");
        size.className = "text-xs text-muted-foreground";
        size.textContent = (file.size / 1024).toFixed(0) + " KB";

        var remove = document.createElement("button");
        remove.type = "button";
        remove.title = "Remove";
        remove.setAttribute("aria-label", "Remove " + file.name);
        remove.className = "rounded-md p-1 text-muted-foreground transition-colors hover:bg-accent hover:text-destructive-text";
        // Plain SVG cross: lucide icons are only drawn once on page load
        remove.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>';
        remove.addEventListener("click", function() {
          files.splice(index, 1);
          syncInput();
          render();
        });

        item.append(name, size, remove);
        list.appendChild(item);
      });
    }

    fileInput.addEventListener("change", function() {
      Array.prototype.forEach.call(fileInput.files, function(file) {
        // Skip a file that is already in the list
        var exists = files.some(function(old) {
          return old.name === file.name && old.size === file.size;
        });

        if (!exists) {
          files.push(file);
        }
      });

      syncInput();
      render();
    });
  })();

  // A double click on Submit must not save the voucher twice
  document.getElementById("voucher-form").addEventListener("submit", function() {
    document.getElementById("voucher-submit").disabled = true;
  });

  // Draft: whatever is typed in the form is kept in this tab (sessionStorage),
  // so it is still there after the page is reloaded. Opening the page any other
  // way (Exit and come back, sidebar link, new tab) starts with an empty form.
  // Files can't be kept: the browser doesn't allow putting a file back into a file input.
  (function() {
    var form = document.getElementById("voucher-form");
    var key = "{{ $type }}-draft-{{ session('company_id') }}-{{ $entry->id ?? 'new' }}";

    // Fields that can't be kept in a draft (the automatic boxes have no name, so they are skipped anyway)
    var skip = ["_token", "_method", "documents[]", "remove_documents[]"];

    function fields() {
      return Array.prototype.filter.call(form.elements, function(el) {
        return el.name && skip.indexOf(el.name) === -1;
      });
    }

    function saveDraft() {
      var draft = {};
      fields().forEach(function(el) {
        draft[el.name] = el.value;
      });

      try {
        sessionStorage.setItem(key, JSON.stringify(draft));
      } catch (e) {
        // Storage blocked (e.g. private window): the form still works, just no draft
      }
    }

    function loadDraft() {
      var draft = null;

      try {
        draft = JSON.parse(sessionStorage.getItem(key));
      } catch (e) {
        return;
      }

      if (!draft) {
        return;
      }

      // Fill in page order, firing the same events as typing would, so the
      // balance boxes, Due Amount and Financial Year update too
      fields().forEach(function(el) {
        if (!(el.name in draft)) {
          return;
        }

        // Skip a ledger / option that no longer exists (e.g. ledger deleted)
        if (el.tagName === "SELECT" && !Array.prototype.some.call(el.options, function(option) { return option.value === draft[el.name]; })) {
          return;
        }

        el.value = draft[el.name];
        el.dispatchEvent(new Event("input"));
        el.dispatchEvent(new Event("change"));
      });
    }

    // Only a reload brings the draft back; any other visit throws it away
    var navigation = performance.getEntriesByType("navigation")[0];
    var isReload = navigation && navigation.type === "reload";

    if (!isReload) {
      try {
        sessionStorage.removeItem(key);
      } catch (e) {}
    }

    // When the form comes back from the server with an error, its own values win
    @if(!session()->hasOldInput())
      if (isReload) {
        loadDraft();
      }
    @endif

    form.addEventListener("input", saveDraft);
    form.addEventListener("change", saveDraft);
  })();
</script>
@endpush
