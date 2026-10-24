@extends('layouts.auth')

@section('title', 'Select Company')
@section('width', 'max-w-md')

@section('content')
  <div class="form-card">
    <div class="form-card-head">Select Company</div>

    <!-- Success message (e.g. after adding a new company) -->
    @if(session('success'))
      <div class="auth-alert is-success mb-3">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-y-3">
      @foreach($companies as $company)
        {{-- Mobile: name on top, button below it (full width).
             Bigger screens (sm and up): name and button on one line. --}}
        <div class="flex flex-col gap-2 rounded-lg border border-border px-3 py-3 sm:flex-row sm:items-center sm:justify-between {{ session('company_id') == $company->id ? 'auth-current' : '' }}">
          <span class="min-w-0 text-sm font-medium text-foreground" style="overflow-wrap: anywhere">{{ $company->company_name }}</span>

          <form action="{{ route('choose-company', $company->id) }}" method="POST">
            @csrf
            <button type="submit" class="auth-button auth-button-small">
              @if(session('company_id') == $company->id)
                Stay with this company
              @else
                Switch to this company
              @endif
            </button>
          </form>
        </div>
      @endforeach
    </div>

    <div class="form-card-foot mt-4">
      <a href="{{ route('add-company') }}" class="auth-button-light">+ Add New Company</a>
    </div>
  </div>
@endsection
