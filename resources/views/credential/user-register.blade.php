@extends('layouts.auth')

@section('title', 'Register')
@section('width', 'auth-wide')

@section('content')
  {{-- auth-grid (public/css/custom.css): 1 field per row on mobile, 2 on bigger screens --}}
  <form action="/register" method="POST" class="form-card auth-grid">
    @csrf
    <div class="form-card-head col-full">
      Register Yourself First
      <span class="block text-xs font-normal text-muted-foreground">Create your account by entering the details below.</span>
    </div>

    <div class="col-full">
      <label for="name" class="block text-sm font-medium text-foreground">Name <span class="text-destructive-text">*</span></label>
      <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus
        placeholder="Enter your name" class="field-input mt-1.5">
      @error('name')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="mobile_no" class="block text-sm font-medium text-foreground">Mobile No. <span class="text-destructive-text">*</span></label>
      <input type="tel" id="mobile_no" name="mobile_no" value="{{ old('mobile_no') }}" required maxlength="10" pattern="[0-9]{10}"
        placeholder="Enter 10-digit mobile number" class="field-input mt-1.5">
      @error('mobile_no')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="email" class="block text-sm font-medium text-foreground">Email ID <span class="text-destructive-text">*</span></label>
      <input type="email" id="email" name="email" value="{{ old('email') }}" required
        placeholder="Enter your email address" class="field-input mt-1.5">
      @error('email')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="department" class="block text-sm font-medium text-foreground">Department <span class="text-destructive-text">*</span></label>
      <input type="text" id="department" name="department" value="{{ old('department') }}" required maxlength="20"
        placeholder="Enter your department" class="field-input mt-1.5">
      @error('department')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="role" class="block text-sm font-medium text-foreground">Role <span class="text-destructive-text">*</span></label>
      <input type="text" id="role" name="role" value="{{ old('role') }}" required
        placeholder="Enter your role" class="field-input mt-1.5">
      @error('role')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="col-full">
      <label for="company_name" class="block text-sm font-medium text-foreground">Company Name <span class="text-destructive-text">*</span></label>
      <input type="text" id="company_name" name="company_name" value="{{ old('company_name') }}" required
        placeholder="Enter your company name" class="field-input mt-1.5">
      @error('company_name')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <!-- Password section -->
    <div class="auth-section col-full">Password</div>

    <div>
      <label for="password" class="block text-sm font-medium text-foreground">Password <span class="text-destructive-text">*</span></label>
      <input type="password" id="password" name="password" required minlength="8"
        placeholder="Create a strong password" class="field-input mt-1.5">
      @error('password')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div>
      <label for="password_confirmation" class="block text-sm font-medium text-foreground">Confirm Password <span class="text-destructive-text">*</span></label>
      <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
        placeholder="Retype your password" class="field-input mt-1.5">
      @error('password_confirmation')
        <p class="mt-1 text-sm text-destructive-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-card-foot col-full">
      <button type="submit" class="auth-button">Submit &amp; Next</button>
      <button type="reset" class="auth-button-light">Cancel</button>
    </div>
  </form>

  <p class="mt-4 text-center text-sm text-muted-foreground">
    Already have an account? <a href="/login" class="font-medium text-primary hover:underline">Login here</a>
  </p>
@endsection
