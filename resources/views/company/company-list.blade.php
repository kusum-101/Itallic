@extends('layouts.app')

@section('title', 'Company Setting')

@section('content')

  <!-- Page heading + Add Company button -->
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h1 class="text-2xl font-bold tracking-tight text-foreground">Company Setting</h1>
      <p class="mt-1 text-sm text-muted-foreground">Your company is listed here.</p>
    </div>

    <a href="{{ route('add-company') }}" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
      <i data-lucide="user-plus" class="size-4"></i> Add Company
    </a>
  </div>

  <!-- Company table -->
  <div class="mt-6 overflow-x-auto rounded-xl border border-border bg-card">
    <table class="w-full text-sm">

      <thead class="border-b border-border bg-muted/40 text-xs uppercase text-muted-foreground">
        <tr>
          <th class="px-4 py-3 text-start font-semibold">S.No.</th>
          <th class="px-4 py-3 text-start font-semibold">Company </th>
          <th class="px-4 py-3 text-start font-semibold">Email</th>
          <th class="px-4 py-3 text-start font-semibold">Phone</th>
          <th class="px-4 py-3 text-start font-semibold">PAN</th>
          <th class="px-4 py-3 text-start font-semibold">GST</th>
          <th class="px-4 py-3 text-start font-semibold">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-border">

        {{-- One row for each company. If there is no company, the @empty part runs --}}
        @forelse($companies as $company)
        <tr>
          <td class="px-4 py-3 text-muted-foreground">{{ $loop->iteration }}</td>
          <td class="px-4 py-3 font-medium text-foreground">{{ $company->company_name }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $company->company_email }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $company->company_phone_no }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $company->pan }}</td>
          <td class="px-4 py-3 text-muted-foreground">{{ $company->gst }}</td>

          <!-- Options for this company: 3-line button opens a small menu -->
          <td class="px-4 py-3">
            <div data-action-menu>
              <button type="button" data-action-menu-toggle aria-haspopup="menu" aria-expanded="false" aria-label="Actions" class="flex size-8 items-center justify-center rounded-lg border border-input text-foreground transition-colors hover:bg-accent">
                <i data-lucide="menu" class="size-4"></i>
              </button>

              <div data-action-menu-panel role="menu" class="fixed z-50 hidden min-w-48 rounded-lg border border-border bg-popover p-1 text-popover-foreground shadow-lg">
                <a href="{{ route('alter', ['id' => $company->id]) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="pencil" class="size-4"></i> Alter
                </a>
                <a href="{{ route('shipping-address', ['id' => $company->id]) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="map-pin" class="size-4"></i> Add Shipping Address
                </a>
                <a href="{{ route('billing-address', ['id' => $company->id]) }}" role="menuitem" class="flex items-center gap-2 rounded-md px-3 py-2 text-sm transition-colors hover:bg-accent">
                  <i data-lucide="map-pin" class="size-4"></i> Add Billing Address
                </a>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="px-4 py-8 text-center text-muted-foreground">No company found.</td>
        </tr>
        @endforelse

      </tbody>
    </table>
  </div>

@endsection
