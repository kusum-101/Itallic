{{--
  One address block: Address, State, District, City, Pin Code.
  $name   : input name prefix, e.g. "billing" or "shipping[2]"
  $key    : the same in dot form for error messages, e.g. "billing" or "shipping.2"
  $values : saved / old values of this block
  $states : State::list()
  District and City options are loaded by the script in addresses.blade.php
  (data-value is the one to select once they are loaded).
--}}
@php
  $id = str_replace('.', '-', $key);
@endphp

<div class="col-full">
  <label for="{{ $id }}-address" class="block text-sm font-medium text-foreground">Address <span class="text-destructive-text">*</span></label>
  <textarea
    id="{{ $id }}-address"
    name="{{ $name }}[address]"
    rows="1"
    class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">{{ $values['address'] ?? '' }}</textarea>
  @error("$key.address")
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>

<div>
  <label for="{{ $id }}-state" class="block text-sm font-medium text-foreground">State <span class="text-destructive-text">*</span></label>
  <select id="{{ $id }}-state" name="{{ $name }}[state_id]" data-state class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="">Select</option>
    @foreach($states as $state)
      <option value="{{ $state->id }}" @selected(($values['state_id'] ?? '') == $state->id)>{{ $state->name }}</option>
    @endforeach
  </select>
  @error("$key.state_id")
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>

<div>
  <label for="{{ $id }}-district" class="block text-sm font-medium text-foreground">District <span class="text-destructive-text">*</span></label>
  <select id="{{ $id }}-district" name="{{ $name }}[district_id]" data-district data-value="{{ $values['district_id'] ?? '' }}" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="">Select</option>
  </select>
  @error("$key.district_id")
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>

<!-- City: pick from the district's list, or "Other" to type one that isn't in it (e.g. Noida).
     The dropdown has no name; the script copies the choice into the text box, which is what gets saved. -->
<div>
  <label for="{{ $id }}-city" class="block text-sm font-medium text-foreground">City <span class="text-destructive-text">*</span></label>
  <select id="{{ $id }}-city" data-city-select class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="">Select</option>
  </select>
  <input
    type="text"
    name="{{ $name }}[city]"
    value="{{ $values['city'] ?? '' }}"
    data-city
    maxlength="100"
    aria-label="City name"
    placeholder="Type city name"
    class="mt-2 hidden h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
  @error("$key.city")
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>

<div>
  <label for="{{ $id }}-pin" class="block text-sm font-medium text-foreground">Pin Code <span class="text-destructive-text">*</span></label>
  <input
    type="text"
    id="{{ $id }}-pin"
    name="{{ $name }}[pin_code]"
    value="{{ $values['pin_code'] ?? '' }}"
    maxlength="6"
    inputmode="numeric"
    placeholder="e.g. 440001"
    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
  @error("$key.pin_code")
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>
