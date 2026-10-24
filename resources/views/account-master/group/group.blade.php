@extends('layouts.app')

@section('title', 'Add Group')

@section('content')

  <!-- Whole Add Group section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <h1 class="text-2xl font-bold tracking-tight text-foreground">Add Group</h1>
      <p class="mt-1 text-sm text-muted-foreground">Create a new group under an existing one.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('groups.store') }}" method="POST" data-enter-next data-keep-draft class="mt-6 space-y-4 rounded-xl border border-border bg-card p-6">
      @csrf

      <div>
        <label for="name" class="block text-sm font-medium text-foreground">Group Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name') }}"
          required
          placeholder="e.g. Petty Cash"
          class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        @error('name')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <div class="flex items-center gap-1.5">
          <label for="parent_id" class="text-sm font-medium text-foreground">Under</label>
          @include('components.add-new', ['route' => 'groups.create', 'title' => 'Add new group'])
        </div>
        <select id="parent_id" name="parent_id" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
          <option value="">Select group</option>
          @foreach($parentOptions as $option)
            <option value="{{ $option->id }}" @selected(old('parent_id') == $option->id)>{{ $option->name }}</option>
          @endforeach
        </select>
        @error('parent_id')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror

        <!-- Note box: highlighted so it is easy to notice -->
        <div class="mt-2 flex items-start gap-2 rounded-lg border border-primary bg-accent px-3 py-3 text-sm text-foreground">
          <i data-lucide="info" class="size-4 shrink-0 text-primary-text"></i>
          <p>
            <span class="font-medium text-primary-text">Note:</span>
            The group's nature (Assets/Liabilities/Income/Expenses) is picked up automatically from the group you select here.
          </p>
        </div>
      </div>

      <div class="flex gap-2 pt-2">
        <button type="submit" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
          Save
        </button>
        <a href="{{ route('groups.index') }}" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
          Cancel
        </a>
      </div>
    </form>

  </div>

@endsection
