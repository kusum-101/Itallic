{{--
  One item row of an inventory voucher: Item, Godown, Quantity, Rate, Value.
  $i       : row number in the input names, e.g. lines[2][quantity] ("__INDEX__" in the template)
  $row     : saved / old values of this row
  $items   : this company's items (with their unit)
  $godowns : this company's godowns, Main Location first (a new row starts with it)
  The unit under Quantity and the Value are filled by the script in stock-voucher.blade.php
  (Value is worked out again by the controller when saving).
--}}
<tr data-line-row data-draft-row>
  <td class="px-2 py-2 align-top">
    {{-- data-symbol / data-decimals / data-hint: the unit the quantity is typed in (Pcs for "Box of 12 Pcs") --}}
    <select name="lines[{{ $i }}][item_id]" data-line-item aria-label="Item" class="h-10 w-full min-w-48 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Select item</option>
      @foreach($items as $item)
        <option
          value="{{ $item->id }}"
          data-symbol="{{ $item->unit->quantityUnit()->symbol }}"
          data-decimals="{{ $item->unit->quantityUnit()->decimals }}"
          data-hint="{{ $item->unit->isCompound() ? '1 ' . $item->unit->firstUnit->symbol . ' = ' . (float) $item->unit->conversion . ' ' . $item->unit->secondUnit->symbol : '' }}"
          @selected(($row['item_id'] ?? '') == $item->id)>{{ $item->name }}</option>
      @endforeach
    </select>
    @error("lines.$i.item_id")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <td class="px-2 py-2 align-top">
    <select name="lines[{{ $i }}][godown_id]" aria-label="Godown" class="h-10 w-full min-w-40 rounded-lg border border-input bg-background px-3 text-sm">
      <option value="">Select godown</option>
      @foreach($godowns as $godown)
        <option value="{{ $godown->id }}" @selected(($row['godown_id'] ?? $godowns->first()?->id) == $godown->id)>{{ $godown->name }}</option>
      @endforeach
    </select>
    @error("lines.$i.godown_id")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <td class="px-2 py-2 align-top">
    <input
      type="number"
      name="lines[{{ $i }}][quantity]"
      value="{{ $row['quantity'] ?? '' }}"
      data-line-quantity
      min="0"
      step="any"
      aria-label="Quantity"
      placeholder="0"
      class="h-10 w-full min-w-28 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
    {{-- The item's unit, e.g. "Kg", or "Pcs (1 Box = 12 Pcs)" --}}
    <p data-line-unit class="mt-1 text-xs text-muted-foreground"></p>
    @error("lines.$i.quantity")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <td class="px-2 py-2 align-top">
    <input
      type="number"
      name="lines[{{ $i }}][rate]"
      value="{{ $row['rate'] ?? '' }}"
      data-line-rate
      min="0"
      step="0.01"
      aria-label="Rate"
      placeholder="0.00"
      class="h-10 w-full min-w-28 rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
    @error("lines.$i.rate")
      <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
    @enderror
  </td>

  <!-- Value = Quantity x Rate (only shown, not sent: the controller works it out again) -->
  <td class="px-2 py-2 align-top">
    <div data-line-value class="flex h-10 min-w-28 items-center justify-end rounded-lg bg-muted/40 px-3 text-sm text-foreground">0.00</div>
  </td>

  <td class="px-2 py-2 align-top">
    {{-- Written out as <svg>: rows added later don't get the theme's <i data-lucide> icons --}}
    <button type="button" data-remove-line aria-label="Remove row" class="flex size-10 items-center justify-center rounded-lg text-destructive-text transition-colors hover:bg-accent">
      <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
    </button>
  </td>
</tr>
