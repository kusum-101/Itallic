<header class="sticky top-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background/80 px-4 backdrop-blur-xl sm:px-6">
  <div class="flex items-center gap-3">
    <button data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-foreground lg:hidden" aria-label="Open menu">
      <i data-lucide="menu" class="size-5"></i>
    </button>
    <button data-cmdk-open type="button" class="hidden h-9 w-64 items-center gap-2 rounded-lg border border-input bg-muted/40 px-3 text-sm text-muted-foreground transition-colors hover:bg-muted/60 sm:flex lg:w-72">
      <i data-lucide="search" class="size-4 text-muted-foreground/70"></i>
      <span class="flex-1 text-start">Search users, roles, pages…</span>
      <kbd class="rounded border border-border px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">⌘K</kbd>
    </button>
  </div>

  @php
    // Get this user's companies, so we can show the "Switch Company"
    // link only when there is more than one. $currentCompany comes
    // from the view composer in AppServiceProvider.
    $headerCompanies = \App\Models\Company::where('user_id', session('user_id'))->get();
  @endphp

  <div class="flex items-center gap-2">

    @if($currentCompany)
    <span class="hidden text-sm font-medium text-foreground sm:inline">{{ $currentCompany->company_name }}</span>
    @endif

    @if($headerCompanies->count() >= 2)
    <a href="{{ route('select-company') }}" class="hidden items-center gap-1.5 rounded-lg border border-input px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent md:inline-flex">
      <i data-lucide="repeat" class="size-4"></i>
      Switch Company
    </a>
    @endif

    <form action="{{ route('logout') }}" method="POST">
      @csrf

      <button
        type="submit"
        class="hidden items-center gap-1.5 rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90 md:inline-flex">

        <i data-lucide="user" class="size-4"></i>
        Logout

      </button>
    </form>

    <button data-theme-toggle class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-foreground" aria-label="Toggle theme">
      <i data-lucide="moon" class="size-4 dark:hidden"></i>
      <i data-lucide="sun" class="hidden size-4 dark:block"></i>
    </button>

    <!-- Appearance customizer -->
    <div class="relative" data-customizer>
      <button data-customizer-btn aria-label="Customize appearance" aria-expanded="false" class="flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
        <i data-lucide="palette" class="size-4"></i>
      </button>
      <div data-customizer-panel class="absolute end-0 z-40 mt-2 hidden w-64 rounded-xl border border-border bg-popover p-3 text-popover-foreground shadow-lg">
        <p class="mb-1.5 text-xs font-semibold text-muted-foreground">Theme</p>
        <div class="grid grid-cols-3 gap-1.5">
          <button type="button" data-set-theme="light" class="flex flex-col items-center gap-1 rounded-lg border-2 border-border p-2 text-[11px] font-medium transition-all hover:border-primary/30"><i data-lucide="sun" data-appearance-tint class="size-4 text-muted-foreground"></i><span data-appearance-tint class="text-muted-foreground">Light</span></button>
          <button type="button" data-set-theme="dark" class="flex flex-col items-center gap-1 rounded-lg border-2 border-border p-2 text-[11px] font-medium transition-all hover:border-primary/30"><i data-lucide="moon" data-appearance-tint class="size-4 text-muted-foreground"></i><span data-appearance-tint class="text-muted-foreground">Dark</span></button>
          <button type="button" data-set-theme="system" class="flex flex-col items-center gap-1 rounded-lg border-2 border-border p-2 text-[11px] font-medium transition-all hover:border-primary/30"><i data-lucide="monitor" data-appearance-tint class="size-4 text-muted-foreground"></i><span data-appearance-tint class="text-muted-foreground">System</span></button>
        </div>
        <p class="mb-1.5 mt-3 text-xs font-semibold text-muted-foreground">Color</p>
        <div class="grid grid-cols-4 gap-1.5">
          <button type="button" data-set-color="violet" aria-label="Violet" class="flex items-center justify-center rounded-lg border-2 border-border p-2 transition-all hover:border-primary/30"><span class="size-4 rounded-full" style="background-color:oklch(0.589 0.24 293)"></span></button>
          <button type="button" data-set-color="blue" aria-label="Blue" class="flex items-center justify-center rounded-lg border-2 border-border p-2 transition-all hover:border-primary/30"><span class="size-4 rounded-full" style="background-color:oklch(0.589 0.19 262)"></span></button>
          <button type="button" data-set-color="teal" aria-label="Teal" class="flex items-center justify-center rounded-lg border-2 border-border p-2 transition-all hover:border-primary/30"><span class="size-4 rounded-full" style="background-color:oklch(0.589 0.13 175)"></span></button>
          <button type="button" data-set-color="rose" aria-label="Rose" class="flex items-center justify-center rounded-lg border-2 border-border p-2 transition-all hover:border-primary/30"><span class="size-4 rounded-full" style="background-color:oklch(0.589 0.16 357)"></span></button>
        </div>
        <p class="mb-1.5 mt-3 text-xs font-semibold text-muted-foreground">Density</p>
        <div class="grid grid-cols-3 gap-1.5">
          <button type="button" data-set-density="compact" class="flex flex-col items-center gap-1 rounded-lg border-2 border-border p-2 text-[11px] font-medium transition-all hover:border-primary/30"><i data-lucide="rows-3" data-appearance-tint class="size-4 text-muted-foreground"></i><span data-appearance-tint class="text-muted-foreground">Compact</span></button>
          <button type="button" data-set-density="comfortable" class="flex flex-col items-center gap-1 rounded-lg border-2 border-border p-2 text-[11px] font-medium transition-all hover:border-primary/30"><i data-lucide="rows-4" data-appearance-tint class="size-4 text-muted-foreground"></i><span data-appearance-tint class="text-muted-foreground">Cozy</span></button>
          <button type="button" data-set-density="spacious" class="flex flex-col items-center gap-1 rounded-lg border-2 border-border p-2 text-[11px] font-medium transition-all hover:border-primary/30"><i data-lucide="stretch-horizontal" data-appearance-tint class="size-4 text-muted-foreground"></i><span data-appearance-tint class="text-muted-foreground">Roomy</span></button>
        </div>
      </div>
    </div>

    <a href="{{ route('notifications') }}" class="relative flex size-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-accent hover:text-foreground" aria-label="Notifications">
      <i data-lucide="bell" class="size-4"></i>
      <span class="absolute end-1.5 top-1.5 size-1.5 rounded-full bg-destructive"></span>
    </a>
    <a href="{{ route('profile') }}" class="ms-1 flex size-8 items-center justify-center rounded-full bg-primary/10 text-xs font-semibold text-primary-text" aria-label="Profile">{{ mb_strtoupper(mb_substr($user->name ?? '', 0, 1)) }}</a>
  </div>
</header>
