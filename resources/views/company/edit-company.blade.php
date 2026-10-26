@extends('layouts.auth')

@section('title', 'Alter Company')
@section('width', 'auth-wide')

@section('content')
  @include('credential.company-form', ['title' => 'Alter Company: ' . $company->company_name, 'companyName' => $company->company_name, 'company' => $company])
@endsection
