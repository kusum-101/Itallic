@extends('layouts.auth')

@section('title', 'Login')
@section('width', 'max-w-md')

@section('content')
  <form action="/login" method="POST" class="form-card space-y-4">
    @csrf
    <div class="form-card-head">Login to Itallic</div>

    <!-- Wrong email / password -->
    @if(session('error'))
      <div class="auth-alert">{{ session('error') }}</div>
    @endif

    <div>
      <label for="email" class="block text-sm font-medium text-foreground">Email ID <span class="text-destructive-text">*</span></label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
        placeholder="Enter your email address" class="field-input mt-1.5">
      @error('email')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="password" class="block text-sm font-medium text-foreground">Password <span class="text-destructive-text">*</span></label>
      <input type="password" id="password" name="password" required
        placeholder="Enter your password" class="field-input mt-1.5">
      @error('password')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-card-foot">
      <button type="submit" class="auth-button">Login</button>
    </div>
  </form>

  <p class="mt-4 text-center text-sm text-muted-foreground">
    Don't have an account? <a href="/register" class="font-medium text-primary hover:underline">Register here</a>
  </p>
@endsection
