@extends('layouts.app')

@section('title', 'Edit Stock Category')

@section('content')

  <!-- Whole Edit Stock Category section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('stock-categories.index')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Edit Stock Category</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Update this stock category's details.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('stock-categories.update', $stockCategory->id) }}" method="POST" data-enter-next data-keep-draft class="mt-4 space-y-4 form-card">
      @csrf
      <div class="form-card-head col-full">Stock Category Details</div>
      @method('PUT')

      <div>
        <label for="name" class="block text-sm font-medium text-foreground">Stock Category Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name', $stockCategory->name) }}"
          required
          class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        @error('name')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      @include('inventory-master.stock-category.under-field', ['selected' => $stockCategory->underValue()])

      <div class="form-card-foot">
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          Save
        </button>
        <a href="{{ route('stock-categories.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Cancel
        </a>
        {{-- Opens the list in a new tab, so what is typed here is not lost --}}
        <a href="{{ route('stock-categories.index') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          <i data-lucide="list" class="size-4"></i> List
        </a>
      </div>
    </form>

  </div>

@endsection
