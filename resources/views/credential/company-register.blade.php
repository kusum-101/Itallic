@extends('layouts.auth')

@section('title', 'Register Company')
@section('width', 'auth-wide')

@section('content')
  {{-- Right after sign-up: the company name typed there comes filled in --}}
  @include('credential.company-form', ['title' => 'Register Your Company', 'companyName' => $user->company_name])
@endsection
