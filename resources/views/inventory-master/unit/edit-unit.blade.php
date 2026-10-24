@extends('layouts.app')

@section('title', 'Edit Unit')

@section('content')

  <!-- Whole Edit Unit section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('units.index')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Edit Unit</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Update this unit's details.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('units.update', $unit->id) }}" method="POST" data-enter-next data-keep-draft class="mt-4 space-y-4 form-card">
      @csrf
      <div class="form-card-head col-full">Unit Details</div>
      @method('PUT')

      <!-- The type can't be changed after the unit is made, so it is only shown -->
      <div>
        <span class="block text-sm font-medium text-foreground">Type</span>
        <p class="mt-1.5 flex h-10 items-center rounded-lg bg-muted/40 px-3 text-sm text-foreground">{{ $unit->type }}</p>
      </div>

      @if($unit->isCompound())
        @include('inventory-master.unit.compound-fields')
      @else
        @include('inventory-master.unit.simple-fields')
      @endif

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
