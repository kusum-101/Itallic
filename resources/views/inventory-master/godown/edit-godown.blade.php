@extends('layouts.app')

@section('title', 'Edit Godown')

@section('content')

  <!-- Whole Edit Godown section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('godowns.index')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Edit Godown</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Update this godown's details.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('godowns.update', $godown->id) }}" method="POST" data-enter-next data-keep-draft class="mt-4 space-y-4 form-card">
      @csrf
      <div class="form-card-head col-full">Godown Details</div>
      @method('PUT')

      <div>
        <label for="name" class="block text-sm font-medium text-foreground">Godown Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name', $godown->name) }}"
          required
          class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        @error('name')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror

        @if($godown->is_primary)
        <!-- Note box: highlighted so it is easy to notice -->
        <div class="mt-2 flex items-start gap-2 rounded-lg border border-primary bg-accent px-3 py-3 text-sm text-foreground">
          <i data-lucide="info" class="size-4 shrink-0 text-primary-text"></i>
          <p>
            <span class="font-medium text-primary-text">Note:</span>
            This is the default godown. It can be renamed but not deleted.
          </p>
        </div>
        @endif
      </div>

      <div>
        <label for="address" class="block text-sm font-medium text-foreground">Address</label>
        <textarea
          id="address"
          name="address"
          rows="3"
          placeholder="Optional"
          class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">{{ old('address', $godown->address) }}</textarea>
        @error('address')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-card-foot">
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          Save
        </button>
        <a href="{{ route('godowns.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Cancel
        </a>
        {{-- Opens the list in a new tab, so what is typed here is not lost --}}
        <a href="{{ route('godowns.index') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          <i data-lucide="list" class="size-4"></i> List
        </a>
      </div>
    </form>

  </div>

@endsection
