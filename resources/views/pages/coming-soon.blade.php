@extends('layouts.app')

@section('title', $title)

@section('content')
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-foreground">{{ $title }}</h1>
    <p class="mt-1 text-sm text-muted-foreground">This page is coming soon.</p>
  </div>
@endsection
