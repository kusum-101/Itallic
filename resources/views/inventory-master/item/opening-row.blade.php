{{--
  One row of the Opening Stock table: Godown, Quantity, Rate, Value.
  $i       : row number in the input names, e.g. openings[2][quantity] ("__INDEX__" in the template)
  $row     : saved / old values of this row
  $godowns : this company's godowns
  Value is worked out by the script in opening-stock.blade.php (and again by the controller when saving).
--}}
<tr data-opening-row data-draft-row>
  <td class="px-2 py-2 align-top">
    <select name="openings[{{ $i }}][godown_id]" aria-label="Godown" class="h-10 w-full min-w-40 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Select godown</option>
      @foreach($godowns as $godown)
        <option value="{{ $godown->id }}" @selected(($row['godown_id'] ?? '') == $godown->id)>{{ $godown->name }}</option>
      @endforeach
    </select>
    @error("openings.$i.godown_id")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <td class="px-2 py-2 align-top">
    <input
      type="number"
      name="openings[{{ $i }}][quantity]"
      value="{{ $row['quantity'] ?? '' }}"
      data-quantity
      min="0"
      step="any"
      aria-label="Quantity"
      placeholder="0"
      class="h-10 w-full min-w-28 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
    @error("openings.$i.quantity")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <td class="px-2 py-2 align-top">
    <input
      type="number"
      name="openings[{{ $i }}][rate]"
      value="{{ $row['rate'] ?? '' }}"
      data-rate
      min="0"
      step="0.01"
      aria-label="Rate"
      placeholder="0.00"
      class="h-10 w-full min-w-28 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
    @error("openings.$i.rate")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <!-- Value = Quantity x Rate (only shown, not sent: the controller works it out again) -->
  <td class="px-2 py-2 align-top">
    <div data-value class="flex h-10 min-w-28 items-center justify-end rounded-lg bg-muted/40 px-3 text-sm text-foreground">0.00</div>
  </td>

  <td class="px-2 py-2 align-top">
    {{-- The trash icon is written out as <svg> (not <i data-lucide>): rows added by "+ Add Godown"
         come after the page has loaded, when the theme has already turned <i data-lucide> into icons --}}
    <button type="button" data-remove-opening aria-label="Remove row" class="flex size-10 items-center justify-center rounded-lg text-destructive-text transition-colors hover:bg-accent">
      <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
    </button>
  </td>
</tr>
