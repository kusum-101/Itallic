@extends('layouts.app')

{{-- One form for both pages: $item is null on Add Item, the saved item on Edit Item.
     $copyFrom: on Add Item, the item being copied (the form starts with its details), or null --}}
@section('title', $item ? 'Edit Item' : 'Add Item')

@php
  // Where the fields get their starting values: the item being edited, or the item being copied
  $fill = $item ?? $copyFrom;
@endphp

@section('content')

  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route('items.index')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $item ? 'Edit Item' : 'Add Item' }}</h1>
    </div>
    <p class="mt-0.5 text-sm text-muted-foreground">{{ $item ? 'Update this item\'s details and opening stock.' : 'Create a new stock item with its opening stock.' }}</p>
  </div>

  @unless($item)
  <!-- Copy details from: pick an item and the form is filled with its details and opening stock.
       It is a GET form of its own (not part of the Save form): picking an item reloads this page with ?copy=id -->
  <form action="{{ route('items.create') }}" method="GET" class="mt-6 flex flex-wrap items-center gap-2 rounded-xl border border-border bg-card p-4">
    <label for="copy_from" class="flex items-center gap-2 text-sm font-medium text-foreground">
      <i data-lucide="copy" class="size-4 text-muted-foreground"></i> Copy details from
    </label>
    <select id="copy_from" name="copy" data-copy-from class="h-10 w-full rounded-lg border border-input bg-background px-3 text-sm sm:w-72">
      <option value="">Select item</option>
      @foreach($copyOptions as $option)
        <option value="{{ $option->id }}" @selected(($copyFrom->id ?? '') == $option->id)>{{ $option->name }}</option>
      @endforeach
    </select>
    @if($copyFrom)
      <a href="{{ route('items.create') }}" class="flex h-10 items-center rounded-lg border border-input px-4 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        Clear
      </a>
    @endif
  </form>
  @endunless

  @if($copyFrom)
  <!-- Note box: highlighted so it is easy to notice -->
  <div class="mt-4 flex items-start gap-2 rounded-lg border border-primary bg-accent px-3 py-3 text-sm text-foreground">
    <i data-lucide="info" class="size-4 shrink-0 text-primary-text"></i>
    <p>
      <span class="font-medium text-primary-text">Copied from "{{ $copyFrom->name }}":</span>
      all its details and opening stock are filled in below. Type the new item's name, change what is different, and Save.
      "{{ $copyFrom->name }}" itself is not changed.
    </p>
  </div>
  @endif

  {{-- form-grid (public/css/custom.css): 1 field per row on mobile, 2 on tablet, 4 on big screens.
       data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
  <form action="{{ $item ? route('items.update', $item->id) : route('items.store') }}" method="POST" data-enter-next data-keep-draft class="form-grid mt-4 form-card">
    @csrf
    <div class="form-card-head col-full">Item Details</div>
    @if($item)
      @method('PUT')
    @endif

    <div class="col-2">
      <label for="name" class="block text-sm font-medium text-foreground">Item Name <span class="text-destructive-text">*</span></label>
      <input
        type="text"
        id="name"
        name="name"
        value="{{ old('name', $fill->name ?? '') }}"
        required
        placeholder="e.g. Italian Marble 2x2"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('name')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <label for="unit_id" class="text-sm font-medium text-foreground">Unit <span class="text-destructive-text">*</span></label>
        @include('components.add-new', ['route' => 'units.create', 'title' => 'Add new unit'])
      </div>
      {{-- data-symbol / data-decimals / data-hint are used by the Opening Stock script (opening-stock.blade.php).
           They are of the unit the quantity is typed in: the unit itself, or the second unit of a
           compound unit (Pcs for "Box of 12 Pcs") --}}
      <select id="unit_id" name="unit_id" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
        <option value="">Select unit</option>
        @foreach($units as $unit)
          <option
            value="{{ $unit->id }}"
            data-symbol="{{ $unit->quantityUnit()->symbol }}"
            data-decimals="{{ $unit->quantityUnit()->decimals }}"
            data-hint="{{ $unit->quantityHint() }}"
            @selected(old('unit_id', $fill->unit_id ?? '') == $unit->id)>
            {{ $unit->symbol }}{{ $unit->name ? ' (' . $unit->name . ')' : '' }}
          </option>
        @endforeach
      </select>
      @error('unit_id')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <label for="stock_group_id" class="text-sm font-medium text-foreground">Stock Group</label>
        @include('components.add-new', ['route' => 'stock-groups.create', 'title' => 'Add new stock group'])
      </div>
      <select id="stock_group_id" name="stock_group_id" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
        <option value="">Primary</option>
        @foreach($stockGroups as $option)
          <option value="{{ $option->id }}" @selected(old('stock_group_id', $fill->stock_group_id ?? '') == $option->id)>{{ $option->name }}</option>
        @endforeach
      </select>
      @error('stock_group_id')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <div class="flex items-center gap-1.5">
        <label for="stock_category_id" class="text-sm font-medium text-foreground">Stock Category</label>
        @include('components.add-new', ['route' => 'stock-categories.create', 'title' => 'Add new stock category'])
      </div>
      <select id="stock_category_id" name="stock_category_id" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
        <option value="">None</option>
        @foreach($stockCategories as $option)
          <option value="{{ $option->id }}" @selected(old('stock_category_id', $fill->stock_category_id ?? '') == $option->id)>{{ $option->name }}</option>
        @endforeach
      </select>
      @error('stock_category_id')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="hsn_code" class="block text-sm font-medium text-foreground">HSN Code</label>
      <input
        type="text"
        id="hsn_code"
        name="hsn_code"
        value="{{ old('hsn_code', $fill->hsn_code ?? '') }}"
        maxlength="8"
        placeholder="e.g. 6802"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('hsn_code')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="gst_percent" class="block text-sm font-medium text-foreground">GST %</label>
      <input
        type="number"
        id="gst_percent"
        name="gst_percent"
        value="{{ old('gst_percent', isset($fill->gst_percent) ? (float) $fill->gst_percent : '') }}"
        step="0.01"
        min="0"
        max="100"
        placeholder="e.g. 18"
        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
      @error('gst_percent')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <!-- Opening Stock rows (one per godown) -->
    @include('inventory-master.item.opening-stock')


    <div class="form-card-foot col-full">
      <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
        Save
      </button>
      <a href="{{ route('items.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        Cancel
      </a>
      {{-- Opens the list in a new tab, so what is typed here is not lost --}}
      <a href="{{ route('items.index') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
        <i data-lucide="list" class="size-4"></i> List
      </a>
    </div>
  </form>

@endsection

@push('scripts')
<script>
  (function() {
    // "Copy details from": picking an item reloads the page filled with it
    var copySelect = document.querySelector("[data-copy-from]");
    if (copySelect) {
      copySelect.addEventListener("change", function() {
        copySelect.form.submit();
      });

      // Sidebar "Copy Allocation Details" opens this page at #copy_from: start in the dropdown
      if (location.hash === "#copy_from") copySelect.focus();
    }

    // Just copied: the name is the copied item's, so select it; typing replaces it with the new name.
    // Not after a reload: then the box has the user's own typed name back (draft, layouts/app)
    @if($copyFrom && !session()->hasOldInput())
      var navigation = performance.getEntriesByType("navigation")[0];
      if (!navigation || navigation.type !== "reload") {
        var name = document.getElementById("name");
        name.focus();
        name.select();
      }
    @endif
  })();
</script>
@endpush
