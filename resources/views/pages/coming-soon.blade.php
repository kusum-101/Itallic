@extends('layouts.app')

@section('title', $title)

@section('content')
  <div>
    <div class="flex items-center gap-2">
      @include('components.back', ['url' => route('dashboardUser')])
      <h1 class="text-xl font-bold tracking-tight text-foreground">{{ $title }}</h1>
    </div>
    <p class="mt-1 text-sm text-muted-foreground">This page is coming soon.</p>
  </div>
@endsection
