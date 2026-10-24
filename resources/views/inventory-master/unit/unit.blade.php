@extends('layouts.app')

@section('title', 'Add Unit')

@section('content')

  <!-- Whole Add Unit section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('units.index')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Add Unit</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Create a simple unit (e.g. Kg) or a compound unit made of two (e.g. Box of 12 Pcs).</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('units.store') }}" method="POST" data-enter-next data-keep-draft class="mt-4 space-y-4 form-card">
      @csrf
      <div class="form-card-head col-full">Unit Details</div>

      <div>
        <label for="type" class="block text-sm font-medium text-foreground">Type</label>
        <select id="type" name="type" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
          <option value="Simple" @selected(old('type', 'Simple') == 'Simple')>Simple</option>
          <option value="Compound" @selected(old('type') == 'Compound')>Compound</option>
        </select>
        @error('type')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <!-- Only the fields of the selected type are shown (and sent), see the script below -->
      <div data-type-fields="Simple" class="space-y-4">
        @include('inventory-master.unit.simple-fields', ['unit' => null])
      </div>

      <div data-type-fields="Compound" class="space-y-4">
        @include('inventory-master.unit.compound-fields', ['unit' => null])
      </div>

      <div class="form-card-foot">
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          Save
        </button>
        <a href="{{ route('units.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Cancel
        </a>
        {{-- Opens the list in a new tab, so what is typed here is not lost --}}
        <a href="{{ route('units.index') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          <i data-lucide="list" class="size-4"></i> List
        </a>
      </div>
    </form>

  </div>

@endsection

@push('scripts')
<script>
  // Show the Simple fields or the Compound fields, depending on the selected Type.
  // The hidden section's fields are also disabled: a disabled field is not sent with the form
  // and its "required" is not checked, so the hidden fields don't stop the form from saving.
  (function() {
    var typeSelect = document.getElementById("type");

    function showFields() {
      document.querySelectorAll("[data-type-fields]").forEach(function(section) {
        var show = section.dataset.typeFields === typeSelect.value;

        section.classList.toggle("hidden", !show);
        section.querySelectorAll("input, select, textarea").forEach(function(field) {
          field.disabled = !show;
        });
      });
    }

    typeSelect.addEventListener("change", showFields);

    // Run once on page load too (e.g. when the form comes back with an error)
    showFields();
  })();
</script>
@endpush
