@extends('layouts.app')

@section('title', 'Add Godown')

@section('content')

  <!-- Whole Add Godown section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <h1 class="text-2xl font-bold tracking-tight text-foreground">Add Godown</h1>
      <p class="mt-1 text-sm text-muted-foreground">Create a new place where stock is kept.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('godowns.store') }}" method="POST" data-enter-next data-keep-draft class="mt-6 space-y-4 rounded-xl border border-border bg-card p-6">
      @csrf

      <div>
        <label for="name" class="block text-sm font-medium text-foreground">Godown Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name') }}"
          required
          placeholder="e.g. Warehouse 2"
          class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        @error('name')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label for="address" class="block text-sm font-medium text-foreground">Address</label>
        <textarea
          id="address"
          name="address"
          rows="3"
          placeholder="Optional"
          class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">{{ old('address') }}</textarea>
        @error('address')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div class="flex gap-2 pt-2">
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          Save
        </button>
        <a href="{{ route('godowns.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Cancel
        </a>
      </div>
    </form>

  </div>

@endsection
