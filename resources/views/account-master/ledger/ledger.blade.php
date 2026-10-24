@extends('layouts.app')

@section('title', 'Add Ledger')

@section('content')

  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route('ledgers.index')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">Add Ledger</h1>
    </div>
    <p class="mt-0.5 text-sm text-muted-foreground">Create a new ledger under a group.</p>
  </div>

  {{-- form-grid (public/css/custom.css): 1 field per row on mobile, 2 on tablet, 4 on big screens.
       data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
  <form action="{{ route('ledgers.store') }}" method="POST" data-enter-next data-keep-draft class="form-grid mt-4 form-card">
    @csrf
    <div class="form-card-head col-full">Ledger Details</div>

    <div class="col-2">
      <label for="name" class="block text-sm font-medium text-foreground">Ledger Name</label>
      <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name') }}"
        required
        placeholder="e.g. Sharma Traders"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('name')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="col-2">
      <div class="flex items-center gap-1.5">
        <label for="group_id" class="text-sm font-medium text-foreground">Group</label>
        @include('components.add-new', ['route' => 'groups.create', 'title' => 'Add new group'])
      </div>
      {{-- data-party="1" tells the script below to show the party fields for this group --}}
      <select id="group_id" name="group_id" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
        <option value="">Select group</option>
        @foreach($groupOptions as $option)
          <option value="{{ $option->id }}" data-party="{{ $option->isPartyGroup() ? 1 : 0 }}" @selected(old('group_id') == $option->id)>{{ $option->name }}</option>
        @endforeach
      </select>
      @error('group_id')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <!-- Note box: highlighted so it is easy to notice -->
    <div class="col-full flex items-start gap-2 rounded-lg border border-primary bg-accent px-3 py-3 text-sm text-foreground">
      <i data-lucide="info" class="size-4 shrink-0 text-primary-text"></i>
      <p>
        <span class="font-medium text-primary-text">Note:</span>
        The fields below change according to the group you select above.
      </p>
    </div>

    <!-- Party fields (data-party-field): shown for Sundry Debtors, Sundry Creditors, Loans, Capital Account etc. -->
    <div data-party-field class="hidden">
      <label for="contact" class="block text-sm font-medium text-foreground">Contact Detail</label>
      <input
        type="text"
        id="contact"
        name="contact"
        value="{{ old('contact') }}"
        placeholder="Mobile number or email"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('contact')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div data-party-field class="hidden">
      <label for="gst_no" class="block text-sm font-medium text-foreground">GST No.</label>
      <input
        type="text"
        id="gst_no"
        name="gst_no"
        value="{{ old('gst_no') }}"
        maxlength="15"
        placeholder="e.g. 07ABCDE1234F1Z5"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm uppercase outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('gst_no')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div data-party-field class="hidden">
      <label for="pan_no" class="block text-sm font-medium text-foreground">PAN No.</label>
      <input
        type="text"
        id="pan_no"
        name="pan_no"
        value="{{ old('pan_no') }}"
        maxlength="10"
        placeholder="e.g. ABCDE1234F"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm uppercase outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('pan_no')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
      <p class="mt-1 text-xs text-muted-foreground">Filled automatically from GST No.</p>
    </div>

    <!-- Other fields (data-other-field): shown for every other group (Sales, Purchase, Expenses etc.) -->
    <div data-other-field class="hidden">
      <label for="gst_percent" class="block text-sm font-medium text-foreground">GST %</label>
      <input
        type="number"
        id="gst_percent"
        name="gst_percent"
        value="{{ old('gst_percent') }}"
        step="0.01"
        min="0"
        max="100"
        placeholder="e.g. 18"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('gst_percent')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div data-other-field class="hidden">
      <label for="hsn_code" class="block text-sm font-medium text-foreground">HSN Code</label>
      <input
        type="text"
        id="hsn_code"
        name="hsn_code"
        value="{{ old('hsn_code') }}"
        maxlength="8"
        placeholder="e.g. 8471"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('hsn_code')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <!-- Opening Balance: shown for every group -->
    <div>
      <label for="opening_balance" class="block text-sm font-medium text-foreground">Opening Balance</label>
      <div class="mt-1.5 flex gap-2">
        <input
          type="number"
          id="opening_balance"
          name="opening_balance"
          value="{{ old('opening_balance') }}"
          step="0.01"
          min="0"
          placeholder="0.00"
          class="h-10 min-w-0 flex-1 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        <select name="opening_type" aria-label="Dr or Cr" class="h-10 shrink-0 rounded-lg border border-input bg-background px-3 text-sm">
          <option value="Dr" @selected(old('opening_type') == 'Dr')>Dr</option>
          <option value="Cr" @selected(old('opening_type') == 'Cr')>Cr</option>
        </select>
      </div>
      @error('opening_balance')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <!-- Billing / Shipping addresses (party groups only) -->
    @include('account-master.ledger.addresses')

    <div class="form-card-foot col-full">
      <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        Save
      </button>
      <a href="{{ route('ledgers.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        Cancel
      </a>
      {{-- Opens the list in a new tab, so what is typed here is not lost --}}
      <a href="{{ route('ledgers.index') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        <i data-lucide="list" class="size-4"></i> List
      </a>
    </div>
  </form>

@endsection

@push('scripts')
<script>
  // Show the party fields or the other fields, depending on the selected group
  (function() {
    var groupSelect = document.getElementById("group_id");
    var partyFields = document.querySelectorAll("[data-party-field]");
    var otherFields = document.querySelectorAll("[data-other-field]");

    function showFields() {
      var selected = groupSelect.options[groupSelect.selectedIndex];
      var hasGroup = selected.value !== "";
      var isParty = selected.dataset.party === "1";

      // No group selected yet: both stay hidden
      partyFields.forEach(function(field) {
        field.classList.toggle("hidden", !(hasGroup && isParty));
      });
      otherFields.forEach(function(field) {
        field.classList.toggle("hidden", !(hasGroup && !isParty));
      });
    }

    groupSelect.addEventListener("change", showFields);

    // Run once on page load too (e.g. when the form comes back with an error)
    showFields();
  })();

  // PAN No. from GST No.: characters 3 to 12 of a GST No. are the PAN
  // (e.g. 09AATCM5105A1ZL -> AATCM5105A). Once a full GST No. is typed,
  // PAN is filled in and locked (readonly), so it can't be changed by mistake.
  (function() {
    var gstInput = document.getElementById("gst_no");
    var panInput = document.getElementById("pan_no");

    function fillPan() {
      var gst = gstInput.value.trim().toUpperCase();

      if (gst.length === 15) {
        panInput.value = gst.substring(2, 12);
        panInput.readOnly = true;
        panInput.classList.add("opacity-70", "cursor-not-allowed");
      } else {
        panInput.readOnly = false;
        panInput.classList.remove("opacity-70", "cursor-not-allowed");
      }
    }

    gstInput.addEventListener("input", fillPan);

    // Run once on page load too (e.g. when the form comes back with an error)
    fillPan();
  })();
</script>
@endpush
