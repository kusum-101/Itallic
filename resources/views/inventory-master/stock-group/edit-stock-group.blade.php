@extends('layouts.app')

@section('title', 'Edit Stock Group')

@section('content')

  <!-- Whole Edit Stock Group section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('stock-groups.index')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Edit Stock Group</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Update this stock group's details.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('stock-groups.update', $stockGroup->id) }}" method="POST" data-enter-next data-keep-draft class="mt-4 space-y-4 form-card">
      @csrf
      <div class="form-card-head col-full">Stock Group Details</div>
      @method('PUT')

      <div>
        <label for="name" class="block text-sm font-medium text-foreground">Stock Group Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name', $stockGroup->name) }}"
          required
          class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        @error('name')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <div class="flex items-center gap-1.5">
          <label for="parent_id" class="text-sm font-medium text-foreground">Under</label>
          @include('components.add-new', ['route' => 'stock-groups.create', 'title' => 'Add new stock group'])
        </div>
        <select id="parent_id" name="parent_id" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
          <option value="">Primary (top-level stock group)</option>
          @foreach($parentOptions as $option)
            <option value="{{ $option->id }}" @selected(old('parent_id', $stockGroup->parent_id) == $option->id)>{{ $option->name }}</option>
          @endforeach
        </select>
        @error('parent_id')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div class="form-card-foot">
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          Save
        </button>
        <a href="{{ route('stock-groups.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Cancel
        </a>
        {{-- Opens the list in a new tab, so what is typed here is not lost --}}
        <a href="{{ route('stock-groups.index') }}" target="_blank" class="flex items-center gap-1.5 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          <i data-lucide="list" class="size-4"></i> List
        </a>
      </div>
    </form>

  </div>

@endsection
