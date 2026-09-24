<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itallic - Login</title>
    <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}">

</head>

<body class="min-h-screen flex flex-col bg-light">
    <div class="flex-1 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">

        <div class="w-full max-w-md">

            <form action="/login" method="POST">
                @csrf

                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">

                    <!-- Header -->
                    <div class="border-b border-border px-4 py-4 sm:px-5">

                        <h1 class="text-center text-xl font-bold text-primary sm:text-2xl">
                            Login to Itallic
                        </h1>

                       

                    </div>


                    <!-- Form Body -->
                    <div class="px-4 py-5 sm:px-5">

                        <div class="grid grid-cols-1 gap-y-4">

                            <!-- Email -->
                            <div>
                                <label for="email"
                                    class="block text-sm font-medium text-foreground">
                                    Email ID
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    placeholder="Enter your email address"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                @error('email')
                                <p class="mt-1 text-sm text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>


                            <!-- Password -->
                            <div>
                                <label for="password"
                                    class="block text-sm font-medium text-foreground">
                                    Password
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    required
                                    placeholder="Enter your password"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                @error('password')
                                <p class="mt-1 text-sm text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>


                            <!-- Login Error -->
                            @if(session('error'))
                            <div class="border-danger bg-danger/10 px-3 py-2">
                                <p class="text-sm text-danger">
                                    {{ session('error') }}
                                </p>
                            </div>
                            @endif

                        </div>

                    </div>


                    <!-- Footer -->
                    <div class="border-t border-border px-5 py-4 sm:px-6">

                        <!-- Login Button -->
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                            Login
                        </button>

                        <!-- Register Link -->
                        <p class="mt-4 text-center text-sm text-muted-foreground">
                            Don't have an account?
                            <a
                                href="/register"
                                class="text-primary hover:underline">
                                Register here
                            </a>
                        </p>

                    </div>

                </div>

            </form>

        </div>

    </div>

    @include('components.footer')

</body>

</html>