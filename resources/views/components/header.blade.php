@php
  // Get this user's companies, so we can show the "Switch Company"
  // link only when there is more than one. $currentCompany comes
  // from the view composer in AppServiceProvider.
  $headerCompanies = \App\Models\Company::where('user_id', session('user_id'))->get();

  // The years the financial year menu offers (App\Support\FinancialYear)
  $yearOptions = session('company_id') ? \App\Support\FinancialYear::options(session('company_id')) : [];
@endphp

{{-- A thin header (48px, like Tally / Munim): year + company on the left, icons on the right.
     The head-* classes are in public/css/custom.css --}}
<header class="app-header sticky top-0 z-30 flex items-center justify-between gap-2 border-b border-border bg-background/80 px-3 backdrop-blur-xl sm:px-6">
  <div class="flex min-w-0 items-center gap-2">
    <button data-sidebar-open aria-controls="app-sidebar" aria-expanded="false" class="head-icon lg:hidden" aria-label="Open menu">
      <i data-lucide="menu"></i>
    </button>

    @if($currentCompany)
      <!-- Financial year menu: pick the year to work in (like Tally's "Change Period") -->
      <details class="head-menu relative">
        <summary class="head-year" title="Change financial year">
          {{ \App\Support\FinancialYear::label() }}
          <i data-lucide="chevron-down" class="size-3.5"></i>
        </summary>
        <div class="absolute start-0 z-40 mt-2 w-64 rounded-xl border border-border bg-popover p-1.5 text-popover-foreground shadow-lg">
          <p class="px-2.5 pb-1.5 pt-2 text-xs font-semibold text-muted-foreground">Financial Year</p>
          <div class="max-h-72 overflow-y-auto">
            @foreach($yearOptions as $year)
              <form action="{{ route('choose-year') }}" method="POST">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="head-menu-item {{ $year == \App\Support\FinancialYear::selected() ? 'is-selected' : '' }}">
                  <span class="flex-1 text-start">
                    {{ \App\Support\FinancialYear::label($year) }}
                    <span class="block text-xs font-normal text-muted-foreground">{{ \App\Support\FinancialYear::start($year)->format('d M Y') }} to {{ \App\Support\FinancialYear::end($year)->format('d M Y') }}</span>
                  </span>
                  @if($year == \App\Support\FinancialYear::current())
                    <span class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">Running</span>
                  @endif
                  @if($year == \App\Support\FinancialYear::selected())
                    <i data-lucide="check" class="text-primary"></i>
                  @endif
                </button>
              </form>
            @endforeach
          </div>
        </div>
      </details>

      <!-- Company name. With 2 or more companies it opens "Switch Company" -->
      @if($headerCompanies->count() >= 2)
        <a href="{{ route('select-company') }}" title="Switch Company" class="head-company">
          <span class="head-company-name">{{ $currentCompany->company_name }}</span>
          <i data-lucide="chevron-down" class="size-4 shrink-0 text-muted-foreground"></i>
        </a>
      @else
        <span class="head-company"><span class="head-company-name">{{ $currentCompany->company_name }}</span></span>
      @endif
    @endif
  </div>

  <div class="flex shrink-0 items-center gap-1">
    <!-- Page search (Ctrl + K) -->
    <button data-cmdk-open type="button" class="head-icon" aria-label="Search pages (Ctrl K)" title="Search pages (Ctrl K)">
      <i data-lucide="search"></i>
    </button>

    <!-- Keyboard Shortcuts panel (Ctrl + /). Inline icon: the theme has no "keyboard" icon -->
    <button data-shortcuts-open type="button" class="head-icon" aria-label="Keyboard Shortcuts (Ctrl /)" title="Keyboard Shortcuts (Ctrl /)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 8h.01"/><path d="M12 12h.01"/><path d="M14 8h.01"/><path d="M16 12h.01"/><path d="M18 8h.01"/><path d="M6 8h.01"/><path d="M7 16h10"/><path d="M8 12h.01"/><rect width="20" height="16" x="2" y="4" rx="2"/></svg>
    </button>

    <button data-theme-toggle class="head-icon" aria-label="Toggle theme" title="Light / Dark">
      <i data-lucide="moon" class="dark:hidden"></i>
      <i data-lucide="sun" class="hidden dark:block"></i>
    </button>

    <!-- Appearance customizer -->
    <div class="relative" data-customizer>
      <button data-customizer-btn aria-label="Customize appearance" aria-expanded="false" class="head-icon" title="Appearance">
        <i data-lucide="palette"></i>
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

    <a href="{{ route('notifications') }}" class="head-icon relative" aria-label="Notifications" title="Notifications">
      <i data-lucide="bell"></i>
      <span class="absolute end-1.5 top-1.5 size-1.5 rounded-full bg-destructive"></span>
    </a>

    <span class="head-divider"></span>

    <!-- Profile menu: <details> opens and closes by itself on click, no script needed -->
    <details class="head-menu head-profile relative">
      <summary class="head-avatar" aria-label="Profile menu" title="{{ $user->name ?? '' }}">{{ mb_strtoupper(mb_substr($user->name ?? '', 0, 1)) }}</summary>
      <div class="absolute end-0 z-40 mt-2 w-64 rounded-xl border border-border bg-popover p-1.5 text-popover-foreground shadow-lg">
        <div class="border-b border-border px-2.5 pb-2 pt-2">
          <p class="truncate text-sm font-semibold text-foreground">{{ $user->name ?? '' }}</p>
          <p class="truncate text-xs text-muted-foreground">{{ $user->email ?? '' }}</p>
        </div>
        <a href="{{ route('profile') }}" class="head-menu-item"><i data-lucide="user"></i> Profile</a>
        <a href="{{ route('company-setting') }}" class="head-menu-item"><i data-lucide="settings"></i> Company Setting</a>
        @if($headerCompanies->count() >= 2)
          <a href="{{ route('select-company') }}" class="head-menu-item"><i data-lucide="repeat"></i> Switch Company</a>
        @endif
        <form action="{{ route('logout') }}" method="POST" class="mt-1 border-t border-border pt-2">
          @csrf
          <button type="submit" class="head-menu-item text-destructive-text"><i data-lucide="log-out"></i> Logout</button>
        </form>
      </div>
    </details>
  </div>
</header>

<script>
  // Close the year / profile menu on a click outside it, or on Esc
  document.addEventListener('click', function (e) {
    document.querySelectorAll('details.head-menu[open]').forEach(function (menu) {
      if (!menu.contains(e.target)) menu.removeAttribute('open');
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('details.head-menu[open]').forEach(function (menu) { menu.removeAttribute('open'); });
    }
  });
</script>
