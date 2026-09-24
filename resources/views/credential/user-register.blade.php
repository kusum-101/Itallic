<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itallic - Register</title>

    <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap.min.css') }}">
</head>

<body class="min-h-screen flex flex-col bg-light">

    <div class="flex-1 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">

        <div class="max-w-2xl">

            <form action="/register" method="POST">
                @csrf

                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">

                    <!-- Header -->
                    <div class="border-b border-border px-3 py-3 sm:px-3">

                        <h1 class="text-center text-xl font-bold text-primary sm:text-2xl">
                            Register Yourself First
                        </h1>

                        <p class="mt-1 text-center text-sm text-muted-foreground">
                            Create your account by entering the details below.
                        </p>

                    </div>


                    <!-- Form Body -->
                    <div class="px-4 py-4 sm:px-4 sm:py-4">

                        <div class="grid grid-cols-1 gap-x-5 gap-y-4">

                            <!-- Name -->
                            <div>
                                <label for="name"
                                    class="block text-sm font-medium text-foreground">
                                    Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    placeholder="Enter your name"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                @error('name')
                                <p class="mt-1 text-sm text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>
                            <!-- Mobile -->
                            <div>
                                <label for="mobile_no"
                                    class="block text-sm font-medium text-foreground">
                                    Mobile No.
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="tel"
                                    id="mobile_no"
                                    name="mobile_no"
                                    value="{{ old('mobile_no') }}"
                                    required
                                    maxlength="10"
                                    pattern="[0-9]{10}"
                                    placeholder="Enter 10-digit mobile number"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                @error('mobile_no')
                                <p class="mt-1 text-sm text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>


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
                            <div>

                                <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">


                                    <!-- Department -->
                                    <div>
                                        <label for="department"
                                            class="block text-sm font-medium text-foreground">
                                            Department
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            id="department"
                                            name="department"
                                            required
                                            placeholder="Enter your department"
                                            class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                                    </div>

                                    <!-- Role -->
                                    <div>
                                        <label for="role"
                                            class="block text-sm font-medium text-foreground">
                                            Role
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input
                                            type="text"
                                            id="role"
                                            name="role"
                                            required
                                            placeholder="Enter your role"
                                            class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                        @error('password')
                                        <p class="mt-1 text-sm text-danger">
                                            {{ $message }}
                                        </p>
                                        @enderror
                                    </div>




                                </div>

                            </div>



                            <!-- Company Name -->
                            <div>
                                <label for="company_name"
                                    class="block text-sm font-medium text-foreground">
                                    Company Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="company_name"
                                    name="company_name"
                                    value="{{ old('company_name') }}"
                                    required
                                    placeholder="Enter your company name"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                @error('company_name')
                                <p class="mt-1 text-sm text-danger">
                                    {{ $message }}
                                </p>
                                @enderror
                            </div>





                        </div>


                        <!-- Password Section -->
                        <div class="mt-6 border-t border-border pt-3">

                            <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-2">


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
                                        minlength="8"
                                        placeholder="Create a strong password"
                                        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                    @error('password')
                                    <p class="mt-1 text-sm text-danger">
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>


                                <!-- Confirm Password -->
                                <div>
                                    <label for="password_confirmation"
                                        class="block text-sm font-medium text-foreground">
                                        Confirm Password
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        name="password_confirmation"
                                        required
                                        minlength="8"
                                        placeholder="Retype your password"
                                        class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                    @error('password_confirmation')
                                    <p class="mt-1 text-sm text-danger">
                                        {{ $message }}
                                    </p>
                                    @enderror
                                </div>


                            </div>

                        </div>

                    </div>


                    <!-- Footer -->
                    <div class="flex flex-col-reverse gap-3 border-t border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">

                        <!-- Cancel -->
                        <button
                            type="reset"
                            class="w-full rounded-lg border border-input px-5 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:w-auto">
                            Cancel
                        </button>


                        <!-- Submit -->
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:w-auto">
                            Submit & Next
                        </button>

                    </div>
                    <div class="px-5 py-4 sm:px-6">
   
    <p class="text-center">Already have an account? <a href="/login" class="text-primary hover:underline">Login here</a></p>
</div>

                </div>

        </div>

        </form>

    </div>

    </div>

    @include('components.footer')

</body>

</html>