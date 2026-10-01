<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Itallic - Simple Accounting & Billing for Your Business</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
    <script type="module" crossorigin src="{{ asset('assets/main-KQ41WFZu.js') }}"></script>

    <!-- Only the few layout styles this page needs that the main CSS doesn't have -->
    <style>
        html { scroll-behavior: smooth; }
        .container-home { max-width: 72rem; margin: 0 auto; padding: 0 1rem; }
        .hero-title { font-size: 2.25rem; line-height: 1.15; }
        .section { padding: 4rem 0; scroll-margin-top: 4rem; }
        .plan-popular { border: 2px solid var(--primary); transform: translateY(-0.5rem); }
        .register-band { background: var(--primary); color: var(--primary-foreground); }
        .plan-badge { position: absolute; top: -0.75rem; left: 50%; transform: translateX(-50%); white-space: nowrap; }
        .btn-light { background: var(--primary-foreground); color: var(--primary); }
        .btn-light:hover { opacity: .9; }
        .nav-links { display: none; }
        @media (min-width: 640px) {
            .container-home { padding: 0 1.5rem; }
        }
        @media (min-width: 768px) {
            .nav-links { display: flex; }
            .hero-title { font-size: 3.25rem; }
            .section { padding: 5rem 0; }
        }
    </style>
</head>

<body class="min-h-screen bg-background text-foreground">

    <!-- Top bar: logo, links, Login + Register -->
    <header class="sticky top-0 z-40 border-b border-border bg-card">
        <div class="container-home flex h-16 items-center justify-between gap-3">
            <a href="/" class="flex items-center gap-2">
                <span class="flex size-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                    <i data-lucide="layers" class="size-5"></i>
                </span>
                <span class="text-lg font-bold">Itallic</span>
            </a>

            <nav class="nav-links items-center gap-6 text-sm font-medium text-muted-foreground">
                <a href="#features" class="hover:text-foreground">Features</a>
                <a href="#how-it-works" class="hover:text-foreground">How it Works</a>
                <a href="#plans" class="hover:text-foreground">Plans</a>
            </nav>

            <div class="flex items-center gap-2">
                <a href="/login" class="rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent">
                    Login
                </a>
                <a href="/register" class="rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
                    Register Free
                </a>
            </div>
        </div>
    </header>

    <!-- Hero -->
    <section class="section">
        <div class="container-home text-center">
            <span class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-3 py-1 text-xs font-medium text-primary">
                <i data-lucide="sparkles" class="size-4"></i>
                Start with a Free Trial - ₹0
            </span>

            <h1 class="hero-title mt-6 font-bold">
                Accounting, Billing & GST<br>
                <span class="text-primary">made simple for your business</span>
            </h1>

            <p class="mx-auto mt-5 max-w-lg text-base text-muted-foreground">
                Manage ledgers, vouchers, stock, quotations and reports for all your companies
                in one place. Ready-made groups like Tally, so you can start in minutes.
            </p>

            <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="/register" class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-base font-semibold text-primary-foreground shadow-lg transition-colors hover:bg-primary/90">
                    Register Now - It's Free
                    <i data-lucide="arrow-right" class="size-5"></i>
                </a>
                <a href="#plans" class="inline-flex items-center gap-2 rounded-lg border border-input bg-card px-6 py-3 text-base font-medium text-foreground transition-colors hover:bg-accent">
                    View Plans
                </a>
            </div>

            <p class="mt-4 text-sm text-muted-foreground">
                Already have an account? <a href="/login" class="font-medium text-primary hover:underline">Login here</a>
            </p>
        </div>
    </section>

    <!-- Features: what the software gives you -->
    <section id="features" class="section border-t border-border bg-card">
        <div class="container-home">
            <div class="text-center">
                <h2 class="text-3xl font-bold">Everything your business needs</h2>
                <p class="mx-auto mt-3 max-w-lg text-muted-foreground">One software for accounts, inventory, sales and your team.</p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['book-open', 'Groups & Ledgers', 'Default groups like Tally are created for you. Add parties with GST No., PAN and billing / shipping addresses.'],
                    ['receipt', 'Vouchers', 'Sales, Purchase, Payment, Receipt, Contra, Journal, Credit Note and Cheque registers.'],
                    ['boxes', 'Inventory', 'Items, Units, Stock Groups, Godowns and Material Inward / Outward / Transfer.'],
                    ['file-text', 'Quotation & Orders', 'Make Quotations, Purchase Orders and Rate Cards for your clients and vendors.'],
                    ['chart-column', 'Reports', 'Day Book, Balance Sheet, Profit & Loss, Ledger & Stock Summary, GST Sale and Payment Due.'],
                    ['building-2', 'Multiple Companies', 'Run more than one company from one login and switch between them anytime.'],
                ] as [$icon, $title, $text])
                    <div class="rounded-xl border border-border bg-background p-6">
                        <span class="flex size-11 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                            <i data-lucide="{{ $icon }}" class="size-5"></i>
                        </span>
                        <h3 class="mt-4 text-lg font-semibold">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How it works: 3 steps -->
    <section id="how-it-works" class="section border-t border-border">
        <div class="container-home">
            <div class="text-center">
                <h2 class="text-3xl font-bold">Start in 3 easy steps</h2>
                <p class="mx-auto mt-3 max-w-lg text-muted-foreground">No setup, no installation. Just register and start working.</p>
            </div>

            <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
                @foreach([
                    ['user-plus', 'Register', 'Create your account with your name, email and mobile number.'],
                    ['building-2', 'Add Your Company', 'Fill in company details, GST and address, and choose a plan.'],
                    ['rocket', 'Start Working', 'Your groups are ready. Add ledgers, make vouchers and see reports.'],
                ] as $i => [$icon, $title, $text])
                    <div class="rounded-xl border border-border bg-card p-6 text-center">
                        <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-primary text-lg font-bold text-primary-foreground">
                            {{ $i + 1 }}
                        </span>
                        <h3 class="mt-4 flex items-center justify-center gap-2 text-lg font-semibold">
                            <i data-lucide="{{ $icon }}" class="size-5 text-primary"></i> {{ $title }}
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Plans: same plans as the Company Register form -->
    <section id="plans" class="section border-t border-border bg-card">
        <div class="container-home">
            <div class="text-center">
                <h2 class="text-3xl font-bold">Simple, honest pricing</h2>
                <p class="mx-auto mt-3 max-w-lg text-muted-foreground">Start free. Upgrade when your business grows.</p>
            </div>

            <div class="mt-10 grid grid-cols-1 items-start gap-6 md:grid-cols-3">
                @foreach([
                    ['Free Trial', '0', '1 Year', 'Try Itallic with limited access.', false],
                    ['Basic Plan', '399', '1 Year', 'For small businesses getting started.', true],
                    ['Advanced Plan', '999', '2 Years', 'For growing businesses that want more.', false],
                ] as [$name, $price, $period, $text, $popular])
                    <div class="relative rounded-2xl border border-border bg-background p-6 {{ $popular ? 'plan-popular shadow-lg' : '' }}">
                        @if($popular)
                            <span class="plan-badge rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">
                                Most Popular
                            </span>
                        @endif

                        <h3 class="text-lg font-semibold">{{ $name }}</h3>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>

                        <p class="mt-5">
                            <span class="text-4xl font-bold">₹{{ $price }}</span>
                            <span class="text-sm text-muted-foreground">/ {{ $period }}</span>
                        </p>

                        <ul class="mt-6 grid gap-3 text-sm">
                            @foreach(['Groups & Ledgers', 'Vouchers & Reports', 'Inventory & Quotation', 'Multiple Companies'] as $feature)
                                <li class="flex items-center gap-2">
                                    <i data-lucide="check" class="size-4 text-primary"></i> {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="/register" class="mt-8 block w-full rounded-lg px-6 py-3 text-center text-sm font-semibold transition-colors {{ $popular ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'border border-input text-foreground hover:bg-accent' }}">
                            {{ $price == '0' ? 'Start Free Trial' : 'Register & Choose' }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Register band: the main call to action -->
    <section class="register-band">
        <div class="container-home py-14 text-center">
            <h2 class="text-3xl font-bold">Ready to manage your business the easy way?</h2>
            <p class="mx-auto mt-3 max-w-lg">Register today and start your Free Trial. It takes less than 2 minutes.</p>
            <a href="/register" class="btn-light mt-8 inline-flex items-center gap-2 rounded-lg px-6 py-3 text-base font-semibold shadow-lg">
                <i data-lucide="user-plus" class="size-5"></i>
                Register Now
            </a>
        </div>
    </section>

    @include('components.footer')

</body>

</html>
