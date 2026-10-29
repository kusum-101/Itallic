@extends('layouts.app')

@section('title', 'Add Stock Category')

@section('content')

  <!-- Whole Add Stock Category section sits in the middle of the page -->
  <div class="mx-auto max-w-lg">

    <div>
      <div class="flex items-center gap-2">
        @include('components.back', ['url' => route('stock-categories.index')])
        <h1 class="text-xl font-bold tracking-tight text-foreground">Add Stock Category</h1>
      </div>
      <p class="mt-0.5 text-sm text-muted-foreground">Create a new stock category, a second way to sort your items, e.g. Premium or Economy.</p>
    </div>

    {{-- data-enter-next (layouts/app): Enter moves to the next field instead of saving --}}
    <form action="{{ route('stock-categories.store') }}" method="POST" data-enter-next data-keep-draft class="mt-4 space-y-4 form-card">
      @csrf
      <div class="form-card-head col-full">Stock Category Details</div>

      <div>
        <label for="name" class="block text-sm font-medium text-foreground">Stock Category Name</label>
        <input
          type="text"
          id="name"
          name="name"
          value="{{ old('name') }}"
          required
          placeholder="e.g. Premium"
          class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none focus:border-ring focus:ring-1 focus:ring-ring/20">
        @error('name')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <div class="flex items-center gap-1.5">
          <label for="stock_group_id" class="text-sm font-medium text-foreground">Stock Group <span class="text-destructive-text">*</span></label>
          @include('components.add-new', ['route' => 'stock-groups.create', 'title' => 'Add new stock group'])
        </div>
        <select id="stock_group_id" name="stock_group_id" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm">
          <option value="" disabled @selected(!old('stock_group_id'))>Select Stock Group</option>
          @foreach($stockGroupOptions as $option)
            <option value="{{ $option->id }}" @selected(old('stock_group_id') == $option->id)>{{ $option->name }}</option>
          @endforeach
        </select>
        @if($stockGroupOptions->isEmpty())
          <p class="mt-1 text-sm text-muted-foreground">No stock group yet. <a href="{{ route('stock-groups.create') }}" class="font-medium text-primary hover:underline">Add a stock group</a> first.</p>
        @endif
        @error('stock_group_id')
          <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
        @enderror
      </div>

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
