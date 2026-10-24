{{--
  Billing and Shipping address section of the Add / Edit Ledger form (party groups only).
  Needs: $states, $billing (values), $shipping (list of values), $sameAsBilling.
  After a validation error the form's old values are used instead.
--}}
@php
  if (session()->hasOldInput()) {
    $billing = old('billing', []);
    $shipping = old('shipping', []);
    // No shipping block in the form means the billing address is used for shipping too
    $sameAsBilling = old('same_as_billing') || !$shipping;
  }
@endphp

<!-- Addresses: party fields, take the whole row -->
<div data-party-field class="col-full hidden border-t border-border pt-4">

  <!-- Billing address -->
  <h2 class="text-base font-semibold text-foreground">Billing Address</h2>
  <div data-address-block class="form-grid mt-3">
    @include('account-master.ledger.address-fields', ['name' => 'billing', 'key' => 'billing', 'values' => $billing])
  </div>

  <!-- Shipping address: same as billing, or one or more of its own -->
  <div class="mt-6 flex flex-wrap items-center justify-between gap-2">
    <h2 class="text-base font-semibold text-foreground">Shipping Address</h2>
    <label class="flex items-center gap-2 text-sm text-foreground">
      <input type="checkbox" name="same_as_billing" value="1" data-same-as-billing class="accent-primary" @checked($sameAsBilling)>
      Same as Billing Address
    </label>
  </div>

  <div data-shipping-section class="{{ $sameAsBilling ? 'hidden' : '' }}">
    {{-- data-draft-list / data-draft-template (layouts/app): blocks added here come back after a page reload --}}
    <div data-shipping-list data-draft-list="shipping" data-draft-template="shipping-template">
      @foreach($shipping as $i => $values)
        <div data-address-block data-draft-row class="form-grid mt-3 rounded-lg border border-border p-4">
          <div class="col-full flex items-center justify-between">
            <span class="text-sm font-medium text-foreground">Shipping Address <span data-block-number>{{ $loop->iteration }}</span></span>
            <button type="button" data-remove-shipping class="rounded-md px-2 py-1 text-sm text-destructive-text hover:bg-accent">Remove</button>
          </div>
          @include('account-master.ledger.address-fields', ['name' => "shipping[$i]", 'key' => "shipping.$i", 'values' => $values])
        </div>
      @endforeach
    </div>

    <button type="button" data-add-shipping class="mt-3 flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
      <i data-lucide="plus" class="size-4"></i> Add Shipping Address
    </button>
  </div>

  <!-- A new, empty shipping block. "+ Add Shipping Address" copies it (__INDEX__ becomes a number) -->
  <template id="shipping-template">
    <div data-address-block data-draft-row class="form-grid mt-3 rounded-lg border border-border p-4">
      <div class="col-full flex items-center justify-between">
        <span class="text-sm font-medium text-foreground">Shipping Address <span data-block-number></span></span>
        <button type="button" data-remove-shipping class="rounded-md px-2 py-1 text-sm text-destructive-text hover:bg-accent">Remove</button>
      </div>
      @include('account-master.ledger.address-fields', ['name' => 'shipping[__INDEX__]', 'key' => 'shipping.__INDEX__', 'values' => []])
    </div>
  </template>
</div>

@push('scripts')
<script>
  // Address section: State -> District dropdowns (+ City suggestions), "Same as Billing" and + / Remove shipping blocks
  (function() {
    var districtsUrl = "{{ url('districts') }}/";
    var citiesUrl = "{{ url('cities') }}/";
    var sameCheckbox = document.querySelector("[data-same-as-billing]");
    var shippingSection = document.querySelector("[data-shipping-section]");
    var shippingList = document.querySelector("[data-shipping-list]");
    var template = document.getElementById("shipping-template");

    // Each list is fetched once and reused (e.g. two blocks in the same state)
    var loaded = {};

    function load(url) {
      if (!loaded[url]) {
        loaded[url] = fetch(url).then(function(res) { return res.json(); });
      }
      return loaded[url];
    }

    // Replace a dropdown's options with the list from the url, then select value (if given)
    function fill(select, url, value) {
      select.innerHTML = '<option value="">Select</option>';
      if (!url) return Promise.resolve();

      return load(url).then(function(rows) {
        rows.forEach(function(row) {
          select.add(new Option(row.name, row.id));
        });
        if (value) select.value = value;
      });
    }

    // City dropdown of a block: the district's cities + "Other" (shows a box to type a city that isn't listed).
    // The chosen / typed name goes into the City text box, which is what gets saved.
    function fillCities(block, districtId) {
      var select = block.querySelector("[data-city-select]");
      var input = block.querySelector("[data-city]");
      var saved = input.value;

      select.innerHTML = '<option value="">Select</option>';
      input.classList.add("hidden");
      if (!districtId) return;

      load(citiesUrl + districtId).then(function(rows) {
        rows.forEach(function(row) {
          select.add(new Option(row.name, row.name));
        });
        select.add(new Option("Other (type city)", "__other"));

        // A saved city: select it, or show it in the text box if it isn't in the list
        if (saved) {
          select.value = saved;
          if (select.value !== saved) {
            select.value = "__other";
            input.classList.remove("hidden");
          }
        }
      });
    }

    // A block that already has a State (edit page, or form back with an error): load its District and City lists
    function prefill(block) {
      var state = block.querySelector("[data-state]");
      var district = block.querySelector("[data-district]");

      if (!state.value) return;

      fill(district, districtsUrl + state.value, district.dataset.value).then(function() {
        fillCities(block, district.value);
      });
    }

    document.querySelectorAll("[data-party-field] [data-address-block]").forEach(prefill);

    // One listener for every block, also the ones added later
    document.addEventListener("change", function(e) {
      var block = e.target.closest("[data-address-block]");
      if (!block) return;

      var district = block.querySelector("[data-district]");
      var city = block.querySelector("[data-city]");

      if (e.target.matches("[data-state]")) {
        fill(district, e.target.value ? districtsUrl + e.target.value : "");
        city.value = "";
        fillCities(block, "");
      }

      if (e.target.matches("[data-district]")) {
        city.value = "";
        fillCities(block, e.target.value);
      }

      if (e.target.matches("[data-city-select]")) {
        var other = e.target.value === "__other";
        city.classList.toggle("hidden", !other);
        city.value = other ? "" : e.target.value;
        if (other) city.focus();
      }
    });

    // Number the shipping blocks 1, 2, 3...
    function renumber() {
      shippingList.querySelectorAll("[data-block-number]").forEach(function(el, i) {
        el.textContent = i + 1;
      });
    }

    function addShipping() {
      // A number not used by any block yet, so the input names don't clash
      var index = shippingList.children.length;
      while (shippingList.querySelector('[name="shipping[' + index + '][address]"]')) index++;

      shippingList.insertAdjacentHTML("beforeend", template.innerHTML.replace(/__INDEX__/g, index));
      renumber();
    }

    document.querySelector("[data-add-shipping]").addEventListener("click", addShipping);

    shippingList.addEventListener("click", function(e) {
      if (!e.target.closest("[data-remove-shipping]")) return;
      e.target.closest("[data-address-block]").remove();
      renumber();
    });

    // After a page reload the draft (layouts/app) may have changed the tick: show or hide to match it
    shippingSection.classList.toggle("hidden", sameCheckbox.checked);
    renumber();

    // Unticking "Same as Billing" shows the shipping blocks (with one empty block to start with)
    sameCheckbox.addEventListener("change", function() {
      shippingSection.classList.toggle("hidden", sameCheckbox.checked);
      if (!sameCheckbox.checked && !shippingList.children.length) addShipping();
    });
  })();
</script>
@endpush
