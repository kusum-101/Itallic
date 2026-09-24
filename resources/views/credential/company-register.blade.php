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

        <div class=" max-w-2xl">

            <form action="/company/register" method="POST">

                @csrf
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">

                    <!-- Header -->
                    <div class="border-b border-border px-3 py-3 sm:px-3">

                        <h1 class="text-center text-xl font-bold text-primary sm:text-2xl">
                            Register Your Company
                        </h1>

                        <p class="mt-1 text-center text-sm text-muted-foreground">
                            Create your company account by entering the details below.
                        </p>

                    </div>

                    <!-- Form Body -->
                    <div class="px-4 py-4 sm:px-4 sm:py-4">

                        <div class="grid grid-cols-1 gap-x-5 gap-y-4 md:grid-cols-1">


                            <!-- Company Name -->
                            <div class="md:col-span-2">
                                <label for="company_name"
                                    class="block text-sm font-medium text-foreground">
                                    Company Name
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="company_name"
                                    name="company_name"
                                    required
                                    placeholder="Enter your company name"
                                    value="{{ $user->company_name }}"

                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>



                            <!-- Company Email -->
                            <div>
                                <label for="company_email"
                                    class="block text-sm font-medium text-foreground">
                                    Company Email
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="company_email"
                                    name="company_email"
                                    required
                                    placeholder="Enter your company email"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>
                            <!-- Company Mobile -->
                            <div>
                                <label for="company_mobile"
                                    class="block text-sm font-medium text-foreground">
                                    Company Mobile
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="company_mobile"
                                    name="company_mobile"
                                    required
                                    placeholder="Enter your company mobile number"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>

                            <!-- PAN -->
                            <div>
                                <label for="pan_number"
                                    class="block text-sm font-medium text-foreground">
                                    PAN Number
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="pan_number"
                                    name="pan_number"
                                    required
                                    placeholder="Enter PAN Number"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>

                            <!-- Gst -->
                            <div>
                                <label for="gst_number"
                                    class="block text-sm font-medium text-foreground">
                                    GST Number
                                    <span class="text-xs text-muted-foreground">

                                    </span>
                                </label>

                                <input
                                    type="text"
                                    id="gst_number"
                                    name="gst_number"
                                    placeholder="Enter GST Number"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>

                            <!-- Company Address -->
                            <div class="md:col-span-2">
                                <label for="address"
                                    class="block text-sm font-medium text-foreground">
                                    Company Address
                                    <span class="text-danger">*</span>
                                </label>

                                <textarea
                                    id="address"
                                    name="address"
                                    rows="2"
                                    required
                                    placeholder="Enter complete company address"
                                    class="mt-1.5 w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20"></textarea>
                            </div>

                            <!-- Branch Address -->
                            <!-- <div class="md:col-span-2">
                                <label for="branch_address"
                                    class="block text-sm font-medium text-foreground">
                                    Branch Office Address
                                    <span class="text-xs text-muted-foreground">

                                    </span>
                                </label>

                                <textarea
                                    id="branch_address"
                                    name="branch_address"
                                    rows="1"
                                    placeholder="Enter branch address if available"
                                    class="mt-1.5 w-full resize-none rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20"></textarea>
                            </div> -->

                            <!-- City -->
                            <div>
                                <label for="city"
                                    class="block text-sm font-medium text-foreground">
                                    City
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="city"
                                    name="city"
                                    required
                                    placeholder="Enter city name"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>

                            <!-- State -->
                            <div>
                                <label for="state"
                                    class="block text-sm font-medium text-foreground">
                                    State
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="state"
                                    name="state"
                                    required
                                    placeholder="Enter state name"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>
                            <!-- District -->
                            <div>
                                <label for="district"
                                    class="block text-sm font-medium text-foreground">
                                    District
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="district"
                                    name="district"
                                    required
                                    placeholder="Enter district name"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>
                            <!-- Pin Code -->
                            <div>
                                <label for="pin_code"
                                    class="block text-sm font-medium text-foreground">
                                    Pin Code
                                    <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="pin_code"
                                    name="pin_code"
                                    required
                                    placeholder="Enter pin code"
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">
                            </div>


                            <!-- Plan -->
                            <div class="md:col-span-2">
                                <label for="plan"
                                    class="block text-sm font-medium text-foreground">
                                    Select Plan
                                    <span class="text-danger">*</span>
                                </label>

                                <select
                                    id="plan"
                                    name="plan"
                                    required
                                    class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring focus:ring-1 focus:ring-ring/20">

                                    <option value="" selected disabled>
                                        -- Choose a Plan --
                                    </option>

                                    <option value="free_trial">
                                        Free Trial (1 Year / Limited Access) - ₹0/-
                                    </option>

                                    <option value="limited_1year">
                                        Basic Plan - ₹399/-
                                    </option>

                                    <option value="limited_2years">
                                        Advanced Plan - ₹999/-
                                    </option>

                                </select>
                            </div>

                        </div>


                    </div>

                    <!-- Footer -->
                    <div class="flex flex-col-reverse gap-3 border-t border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-6">

                        <button
                            type="button"
                            onclick="history.back()"
                            class="w-full rounded-lg border border-input px-5 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground sm:w-auto">
                            Back
                        </button>


                        <button
                            type="submit"
                            class="w-full rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 sm:w-auto">
                            Register & Proceed
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

    @include('components.footer')

</body>

</html>