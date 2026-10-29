{{-- "Under" field of the Add / Edit Stock Category form, as a tree (StockCategory::underOptions):
     a stock group puts the category directly in it, a category makes it a sub-category.
     $selected: the current choice ("group:2" / "category:5"), or '' on the Add page --}}
<div>
  <div class="flex items-center gap-1.5">
    <label for="under" class="text-sm font-medium text-foreground">Under <span class="text-destructive-text">*</span></label>
    @include('components.add-new', ['route' => 'stock-groups.create', 'title' => 'Add new stock group'])
  </div>
  <select id="under" name="under" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
    <option value="" disabled @selected(!old('under', $selected))>Select Stock Group or Category</option>
    @foreach($underOptions as $option)
      <option value="{{ $option['value'] }}" @selected(old('under', $selected) == $option['value'])>
        {{ str_repeat("\u{00A0}\u{00A0}\u{00A0}\u{00A0}", $option['depth']) }}{{ $option['type'] == 'category' ? '└ ' : '' }}{{ $option['name'] }}
      </option>
    @endforeach
  </select>
  @if(collect($underOptions)->where('type', 'group')->isEmpty())
    <p class="mt-1 text-sm text-muted-foreground">No stock group yet. <a href="{{ route('stock-groups.create') }}" class="font-medium text-primary hover:underline">Add a stock group</a> first.</p>
  @else
    <p class="mt-1 text-xs text-muted-foreground">Pick a stock group, or a category (└) to make this a sub-category of it.</p>
  @endif
  @error('under')
    <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
  @enderror
</div>
