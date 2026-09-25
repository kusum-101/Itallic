<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itallic - Select Company</title>
    <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}">
</head>

<body class="min-h-screen flex flex-col bg-light">
    <div class="flex-1 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">

        <div class="w-full max-w-md">

            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">

                <!-- Header -->
                <div class="border-b border-border px-4 py-4 sm:px-5">
                    <h1 class="text-center text-xl font-bold text-primary sm:text-2xl">
                        Select Company
                    </h1>
                </div>

                <!-- Success message (e.g. after adding a new company) -->
                @if(session('success'))
                <div class="border-b border-border px-4 py-3 sm:px-5" style="background: color-mix(in oklch, var(--success) 10%, transparent)">
                    <p class="text-sm text-success-text">{{ session('success') }}</p>
                </div>
                @endif

                <!-- Company list -->
                <div class="px-4 py-5 sm:px-5">

                    <div class="grid grid-cols-1 gap-y-3">

                        @foreach($companies as $company)
                        {{-- Mobile: name on top, button below it (full width).
                             Bigger screens (sm and up): name and button on one line. --}}
                        <div class="flex flex-col gap-2 rounded-lg border border-input px-3 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <span class="min-w-0 text-sm font-medium text-foreground" style="overflow-wrap: anywhere">{{ $company->company_name }}</span>

                            <form action="{{ route('choose-company', $company->id) }}" method="POST">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full whitespace-nowrap rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground transition-colors hover:bg-primary/90">
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

                </div>

            </div>

        </div>

    </div>

    @include('components.footer')

</body>

</html>
