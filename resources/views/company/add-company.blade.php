@extends('layouts.auth')

@section('title', 'Add New Company')
@section('width', 'auth-wide')

@section('content')
  @include('credential.company-form', ['title' => 'Add New Company', 'companyName' => ''])
@endsection
