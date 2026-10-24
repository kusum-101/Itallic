{{--
  Fields of a Simple unit (Nos, Kg, ...): Symbol, Formal Name, UQC, Decimal Places.
  $unit : the saved unit on the Edit page, null on the Add page
--}}
<div>
  <label for="symbol" class="block text-sm font-medium text-foreground">Symbol <span class="text-destructive-text">*</span></label>
  <input
    type="text"
    id="symbol"
    name="symbol"
    value="{{ old('symbol', $unit->symbol ?? '') }}"
    required
    maxlength="20"
    placeholder="e.g. Kg"
    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
  @error('symbol')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>

<div>
  <label for="name" class="block text-sm font-medium text-foreground">Formal Name</label>
  <input
    type="text"
    id="name"
    name="name"
    value="{{ old('name', $unit->name ?? '') }}"
    placeholder="e.g. Kilogram"
    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
  @error('name')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>

<div>
  <label for="uqc" class="block text-sm font-medium text-foreground">Unit Quantity Code (UQC)</label>
  <select id="uqc" name="uqc" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="">Not Applicable</option>
    @foreach(\App\Models\Unit::UQCS as $code => $uqcName)
      <option value="{{ $code }}" @selected(old('uqc', $unit->uqc ?? '') == $code)>{{ $code }}-{{ $uqcName }}</option>
    @endforeach
  </select>
  @error('uqc')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
  <p class="mt-1 text-xs text-muted-foreground">The GST code of this unit, used in GST returns and e-way bills.</p>
</div>

<div>
  <label for="decimals" class="block text-sm font-medium text-foreground">Decimal Places <span class="text-destructive-text">*</span></label>
  <select id="decimals" name="decimals" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    @foreach([0, 1, 2, 3, 4] as $places)
      <option value="{{ $places }}" @selected(old('decimals', $unit->decimals ?? 0) == $places)>{{ $places }}</option>
    @endforeach
  </select>
  @error('decimals')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror

  <!-- Note box: highlighted so it is easy to notice -->
  <div class="mt-2 flex items-start gap-2 rounded-lg border border-primary bg-accent px-3 py-3 text-sm text-foreground">
    <i data-lucide="info" class="size-4 shrink-0 text-primary-text"></i>
    <p>
      <span class="font-medium text-primary-text">Note:</span>
      How many digits a quantity can have after the point. Use 0 for things you count (Nos, Box) and 2 or 3 for things you weigh or measure (Kg, Mtr).
    </p>
  </div>
</div>
