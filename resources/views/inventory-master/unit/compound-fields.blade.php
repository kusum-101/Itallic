{{--
  Fields of a Compound unit: First Unit, Conversion, Second Unit, e.g. Box, 12, Pcs = "Box of 12 Pcs".
  $unit        : the saved unit on the Edit page, null on the Add page
  $simpleUnits : this company's simple units, for the two dropdowns
--}}
<div>
  <div class="flex items-center gap-1.5">
    <label for="first_unit_id" class="text-sm font-medium text-foreground">First Unit <span class="text-destructive-text">*</span></label>
    @include('components.add-new', ['route' => 'units.create', 'title' => 'Add new unit'])
  </div>
  <select id="first_unit_id" name="first_unit_id" required data-compound-part class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="">Select unit</option>
    @foreach($simpleUnits as $option)
      <option value="{{ $option->id }}" @selected(old('first_unit_id', $unit->first_unit_id ?? '') == $option->id)>{{ $option->symbol }}</option>
    @endforeach
  </select>
  @error('first_unit_id')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
  <p class="mt-1 text-xs text-muted-foreground">The bigger unit, e.g. Box.</p>
</div>

<div>
  <label for="conversion" class="block text-sm font-medium text-foreground">Conversion <span class="text-destructive-text">*</span></label>
  <input
    type="number"
    id="conversion"
    name="conversion"
    value="{{ old('conversion', isset($unit->conversion) ? (float) $unit->conversion : '') }}"
    required
    min="0"
    step="any"
    placeholder="e.g. 12"
    data-compound-part
    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
  @error('conversion')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
  <p class="mt-1 text-xs text-muted-foreground">How many of the second unit make one first unit.</p>
</div>

<div>
  <div class="flex items-center gap-1.5">
    <label for="second_unit_id" class="text-sm font-medium text-foreground">Second Unit <span class="text-destructive-text">*</span></label>
    @include('components.add-new', ['route' => 'units.create', 'title' => 'Add new unit'])
  </div>
  <select id="second_unit_id" name="second_unit_id" required data-compound-part class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="">Select unit</option>
    @foreach($simpleUnits as $option)
      <option value="{{ $option->id }}" @selected(old('second_unit_id', $unit->second_unit_id ?? '') == $option->id)>{{ $option->symbol }}</option>
    @endforeach
  </select>
  @error('second_unit_id')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
  <p class="mt-1 text-xs text-muted-foreground">The smaller unit, e.g. Pcs. Stock of items in this unit is counted in it.</p>
</div>

<!-- What the unit will be called, filled by the script below while typing -->
<div class="flex items-start gap-2 rounded-lg border border-primary bg-accent px-3 py-3 text-sm text-foreground">
  <i data-lucide="info" class="size-4 shrink-0 text-primary-text"></i>
  <p>
    <span class="font-medium text-primary-text">Unit:</span>
    <span data-compound-preview>Select both units and the conversion</span>
  </p>
</div>

@push('scripts')
<script>
  // Shows the compound unit's name while it is filled in, e.g. "Box of 12 Pcs (1 Box = 12 Pcs)"
  (function() {
    var first = document.getElementById("first_unit_id");
    var conversion = document.getElementById("conversion");
    var second = document.getElementById("second_unit_id");
    var preview = document.querySelector("[data-compound-preview]");

    function showPreview() {
      var firstSymbol = first.value ? first.options[first.selectedIndex].text : "";
      var secondSymbol = second.value ? second.options[second.selectedIndex].text : "";
      var number = parseFloat(conversion.value);

      if (!firstSymbol || !secondSymbol || !(number > 0)) {
        preview.textContent = "Select both units and the conversion";
        return;
      }

      preview.textContent = firstSymbol + " of " + number + " " + secondSymbol
        + "  (1 " + firstSymbol + " = " + number + " " + secondSymbol + ")";
    }

    document.querySelectorAll("[data-compound-part]").forEach(function(field) {
      field.addEventListener("input", showPreview);
      field.addEventListener("change", showPreview);
    });

    // Run once on page load too (edit page, or form back with an error)
    showPreview();
  })();
</script>
@endpush
