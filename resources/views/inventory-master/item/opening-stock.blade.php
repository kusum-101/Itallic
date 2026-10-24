{{--
  Opening Stock section of the Add / Edit Item form: one row per godown.
  Needs: $godowns, $openings (list of row values).
  After a validation error the form's old values are used instead.
--}}
@php
  if (session()->hasOldInput()) {
    $openings = old('openings', []);
  }

  // Start with one empty row when there is nothing yet
  if (!$openings) {
    $openings = [[]];
  }
@endphp

<div class="col-full border-t border-border pt-4">
  <h2 class="text-base font-semibold text-foreground">Opening Stock</h2>
  <p class="mt-1 text-sm text-muted-foreground">How much of this item each godown has today. Leave it empty if there is none.</p>
  <!-- Compound unit only: how to type the quantity (filled by the script below) -->
  <p data-unit-hint class="mt-1 hidden text-sm text-primary-text"></p>

  <div class="mt-3 overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-2 py-2 text-start font-semibold">
            <span class="flex items-center gap-1.5">
              Godown
              @include('components.add-new', ['route' => 'godowns.create', 'title' => 'Add new godown'])
            </span>
          </th>
          <th class="px-2 py-2 text-start font-semibold">Quantity <span data-unit-symbol></span></th>
          <th class="px-2 py-2 text-start font-semibold">Rate</th>
          <th class="px-2 py-2 text-end font-semibold">Value</th>
          <th class="px-2 py-2"></th>
        </tr>
      </thead>

      {{-- data-draft-list / data-draft-template (layouts/app): rows added here come back after a page reload --}}
      <tbody data-opening-list data-draft-list="openings" data-draft-template="opening-template">
        @foreach($openings as $i => $row)
          @include('inventory-master.item.opening-row', ['i' => $i, 'row' => $row])
        @endforeach
      </tbody>

      <!-- Totals of all rows -->
      <tfoot class="border-t border-border font-medium text-foreground">
        <tr>
          <td class="px-2 py-3 text-end">Total</td>
          <td class="px-2 py-3"><span data-total-quantity>0</span> <span data-unit-symbol></span></td>
          <td class="px-2 py-3"></td>
          <td class="px-2 py-3 text-end" data-total-value>0.00</td>
          <td class="px-2 py-3"></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <button type="button" data-add-opening class="mt-3 flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
    <i data-lucide="plus" class="size-4"></i> Add Godown
  </button>

  <!-- A new, empty row. "+ Add Godown" copies it (__INDEX__ becomes a number) -->
  <template id="opening-template">
    @include('inventory-master.item.opening-row', ['i' => '__INDEX__', 'row' => []])
  </template>
</div>

@push('scripts')
<script>
  // Opening Stock rows: + Add / Remove a row, Value = Quantity x Rate, totals,
  // and the selected unit's symbol / decimal places on the Quantity boxes
  (function() {
    var list = document.querySelector("[data-opening-list]");
    var template = document.getElementById("opening-template");
    var unitSelect = document.getElementById("unit_id");

    // Number with exactly 2 digits after the point, e.g. 1500 -> "1,500.00"
    function money(number) {
      return number.toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotals() {
      var totalQuantity = 0;
      var totalValue = 0;

      list.querySelectorAll("[data-opening-row]").forEach(function(row) {
        var quantity = parseFloat(row.querySelector("[data-quantity]").value) || 0;
        var rate = parseFloat(row.querySelector("[data-rate]").value) || 0;
        var value = Math.round(quantity * rate * 100) / 100;

        row.querySelector("[data-value]").textContent = money(value);
        totalQuantity += quantity;
        totalValue += value;
      });

      // Round away tiny float errors, e.g. 0.1 + 0.2 = 0.30000000000000004
      document.querySelector("[data-total-quantity]").textContent = Math.round(totalQuantity * 10000) / 10000;
      document.querySelector("[data-total-value]").textContent = money(totalValue);
    }

    // The unit's symbol in the headings, and the step of the Quantity boxes
    // (Nos: whole numbers only, Kg: 0.001), from data-symbol / data-decimals on the unit's <option>
    function applyUnit() {
      var selected = unitSelect.options[unitSelect.selectedIndex];
      var symbol = selected.value ? "(" + selected.dataset.symbol + ")" : "";
      var decimals = Number(selected.dataset.decimals || 0);
      var step = decimals ? (1 / Math.pow(10, decimals)).toFixed(decimals) : "1";

      document.querySelectorAll("[data-unit-symbol]").forEach(function(el) {
        el.textContent = symbol;
      });
      list.querySelectorAll("[data-quantity]").forEach(function(input) {
        input.step = selected.value ? step : "any";
      });

      // Compound unit: "1 Box = 12 Pcs, so type the quantity in Pcs ..."
      var hint = document.querySelector("[data-unit-hint]");
      hint.textContent = selected.dataset.hint || "";
      hint.classList.toggle("hidden", !selected.dataset.hint);
    }

    function addRow() {
      // A number not used by any row yet, so the input names don't clash
      var index = list.children.length;
      while (list.querySelector('[name="openings[' + index + '][quantity]"]')) index++;

      list.insertAdjacentHTML("beforeend", template.innerHTML.replace(/__INDEX__/g, index));
      applyUnit();
    }

    document.querySelector("[data-add-opening]").addEventListener("click", addRow);

    list.addEventListener("click", function(e) {
      if (!e.target.closest("[data-remove-opening]")) return;
      e.target.closest("[data-opening-row]").remove();
      updateTotals();
    });

    list.addEventListener("input", updateTotals);
    unitSelect.addEventListener("change", applyUnit);

    // Run once on page load too (edit page, or form back with an error)
    applyUnit();
    updateTotals();
  })();
</script>
@endpush
