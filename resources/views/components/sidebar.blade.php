@php
  /*
   * The sidebar menu. Each section (MASTERS, VOUCHERS, ...) has a list of links.
   * A link:      ['label', 'icon', 'url', 'add' => url of its "Add" page (optional, shows a + next to it)]
   * A sub-menu:  ['label', 'icon', 'children' => [links]]
   * To add a page to the sidebar, add one line here.
   */

  // Sales from other companies on this software waiting for this company's OK
  $incomingCount = \App\Models\InterCompanyEntry::where('to_company_id', session('company_id'))->where('status', 'Pending')->count();

  $menu = [
      'Masters' => [
          ['label' => 'Company Setting', 'icon' => 'settings', 'url' => route('company-setting')],
          ['label' => 'Account Master', 'icon' => 'book-open', 'children' => [
              ['label' => 'Chart of Accounts', 'icon' => 'layers', 'url' => route('chart-of-accounts')],
              ['label' => 'List Group',  'icon' => 'folder',    'url' => route('groups.index'),  'add' => route('groups.create')],
              ['label' => 'List Ledger', 'icon' => 'book-open', 'url' => route('ledgers.index'), 'add' => route('ledgers.create')],
          ]],
          ['label' => 'Inventory Master', 'icon' => 'boxes', 'children' => [
              ['label' => 'Stock Group',    'icon' => 'layers',     'url' => route('stock-groups.index'),     'add' => route('stock-groups.create')],
              ['label' => 'Stock Category', 'icon' => 'tags',       'url' => route('stock-categories.index'), 'add' => route('stock-categories.create')],
              ['label' => 'Item',           'icon' => 'boxes',      'url' => route('items.index'),            'add' => route('items.create')],
              ['label' => 'Unit',           'icon' => 'scale',      'url' => route('units.index'),            'add' => route('units.create')],
              ['label' => 'Godown',         'icon' => 'building-2', 'url' => route('godowns.index'),          'add' => route('godowns.create')],
          ]],
          ['label' => 'User Master', 'icon' => 'user-cog', 'children' => [
              ['label' => 'Users',      'icon' => 'users',       'url' => route('users')],
              ['label' => 'Department', 'icon' => 'users-round', 'url' => route('department')],
              ['label' => 'Position',   'icon' => 'user',        'url' => route('position')],
          ]],
      ],
      'Vouchers' => [
          ['label' => 'Account Vouchers', 'icon' => 'receipt', 'children' => [
              ['label' => 'Payment Register',     'icon' => 'credit-card', 'url' => route('payment-register'),  'add' => route('payment')],
              ['label' => 'Purchase Register',    'icon' => 'boxes',       'url' => route('purchase-register'), 'add' => route('purchase')],
              ['label' => 'Incoming Purchases' . ($incomingCount ? " ($incomingCount)" : ''), 'icon' => 'inbox', 'url' => route('incoming-purchases')],
              ['label' => 'Contra Register',      'icon' => 'repeat',      'url' => route('contra-register'),   'add' => route('contra')],
              ['label' => 'Receipt Register',     'icon' => 'receipt',     'url' => route('receipt-register'),  'add' => route('receipt')],
              ['label' => 'Journal Register',     'icon' => 'book-open',   'url' => route('journal-register'),  'add' => route('journal')],
              ['label' => 'Sales Register',       'icon' => 'chart-line',  'url' => route('sales-register'),    'add' => route('sales')],
              ['label' => 'Credit Note Register', 'icon' => 'scroll-text', 'url' => route('credit-note-register')],
              ['label' => 'Cheque Register',      'icon' => 'banknote',    'url' => route('cheque-register')],
          ]],
          ['label' => 'Inventory Vouchers', 'icon' => 'layers', 'children' => [
              ['label' => 'Material Inward',  'icon' => 'download',     'url' => route('material-inward-register'), 'add' => route('material-inward')],
              ['label' => 'Material Outward', 'icon' => 'upload',       'url' => route('material-outward')],
              ['label' => 'Godown Transfer',  'icon' => 'repeat',       'url' => route('godown-transfer')],
              ['label' => 'Physical Stock',   'icon' => 'square-check', 'url' => route('physical-stock')],
          ]],
          ['label' => 'Commercial Vouchers', 'icon' => 'file-text', 'children' => [
              ['label' => 'Purchase Order', 'icon' => 'file-text',   'url' => route('purchase-order')],
              ['label' => 'Rate Card',      'icon' => 'credit-card', 'url' => route('rate-card')],
              ['label' => 'Quotation',      'icon' => 'scroll-text', 'url' => route('quotation')],
          ]],
      ],
      'Reports' => [
          ['label' => 'Day Book',            'icon' => 'calendar',           'url' => route('day-book')],
          ['label' => 'Balance Sheet',       'icon' => 'scale',              'url' => route('balance-sheet')],
          ['label' => 'Group Report',        'icon' => 'folder',             'url' => route('group-report')],
          ['label' => 'Profit & Loss A/c',   'icon' => 'trending-up',        'url' => route('profit-loss')],
          ['label' => 'Ledger Summary',      'icon' => 'chart-column',       'url' => route('ledger-summary')],
          ['label' => 'Stock Summary',       'icon' => 'layers',             'url' => route('stock-summary')],
          ['label' => 'All Vouchers',        'icon' => 'scroll-text',        'url' => route('all-vouchers')],
          ['label' => 'Gst Sale',            'icon' => 'dollar-sign',        'url' => route('gst-sale')],
          ['label' => 'Payment Due Details', 'icon' => 'wallet',             'url' => route('payment-due')],
          ['label' => 'Leave Summary',       'icon' => 'calendar',           'url' => route('leave-summary')],
      ],
      'HR' => [
          ['label' => 'Daily Reports', 'icon' => 'file-text', 'url' => route('daily-reports')],
          ['label' => 'Attendance',    'icon' => 'users',     'url' => route('attendance')],
          ['label' => 'Leave',         'icon' => 'calendar',  'url' => route('leave')],
      ],
      'Account' => [
          ['label' => 'Profile',  'icon' => 'user',     'url' => route('profile')],
          ['label' => 'Settings', 'icon' => 'settings', 'url' => route('settings')],
      ],
  ];
@endphp

<aside
    id="app-sidebar"
    data-sidebar
    aria-label="Primary"
    class="fixed left-0 top-0 z-50 flex h-screen -translate-x-full flex-col border-r border-sidebar-border bg-sidebar transition-transform duration-300 lg:translate-x-0">
    <!-- Brand -->
    <div class="side-brand flex items-center justify-between gap-2 border-b border-sidebar-border px-3">
      <a href="{{ route('dashboardUser') }}" data-brand-link class="flex min-w-0 items-center gap-2">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sidebar-primary">
          <i data-lucide="building-2" class="size-4 text-sidebar-primary-foreground"></i>
        </span>
        <span data-brand-text class="flex min-w-0 flex-col leading-none">
          <span class="truncate text-sm font-bold tracking-tight text-sidebar-foreground">{{ $currentCompany->company_name ?? '' }}</span>
          <span class="text-[10px] font-medium uppercase tracking-widest text-sidebar-foreground/75">Company</span>
        </span>
      </a>
      <button data-sidebar-close class="flex size-7 items-center justify-center rounded-md text-sidebar-foreground/75 hover:bg-sidebar-accent hover:text-sidebar-foreground lg:hidden" aria-label="Close menu">
        <i data-lucide="x" class="size-4"></i>
      </button>
    </div>

    <!-- Collapse-to-rail toggle (desktop) -->
    <button data-sidebar-collapse aria-label="Collapse sidebar" aria-expanded="true" class="absolute -end-3 top-[4.5rem] z-50 hidden size-6 items-center justify-center rounded-full border border-sidebar-border bg-sidebar text-sidebar-foreground/75 shadow-sm transition-colors hover:text-sidebar-foreground lg:flex">
      <i data-lucide="chevrons-left" data-collapse-icon class="size-3.5 transition-transform"></i>
    </button>

    <!-- Nav (the side-* classes are in public/css/custom.css) -->
    <nav class="flex-1 space-y-3 overflow-y-auto px-2 py-3" data-nav>
      <ul>
        @include('components.sidebar-link', ['link' => ['label' => 'Dashboard', 'icon' => 'layout-dashboard', 'url' => route('dashboardUser')]])
      </ul>

      @foreach ($menu as $section => $links)
        <div>
          <!-- Section heading: click to hide / show its links -->
          <button type="button" data-group-toggle class="side-heading">
            <span>{{ $section }}</span>
            <i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform"></i>
          </button>

          <ul class="space-y-0.5">
            @foreach ($links as $link)
              @isset($link['children'])
                <!-- Sub-menu: closed, unless the open page is in it (see the script below) -->
                <li>
                  <button type="button" data-group-toggle class="side-link">
                    <i data-lucide="{{ $link['icon'] }}"></i>
                    <span class="flex-1 text-left">{{ $link['label'] }}</span>
                    <i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform -rotate-90"></i>
                  </button>
                  <ul class="side-sub space-y-0.5 hidden">
                    @foreach ($link['children'] as $child)
                      @include('components.sidebar-link', ['link' => $child])
                    @endforeach
                  </ul>
                </li>
              @else
                @include('components.sidebar-link', ['link' => $link])
              @endisset
            @endforeach

            @if ($section == 'Account')
              {{-- Switch Company + Logout: only on mobile (mobile-only in public/css/custom.css),
                   because on bigger screens they are already in the header --}}
              @if(\App\Models\Company::where('user_id', session('user_id'))->count() >= 2)
                <li class="mobile-only"><a href="{{ route('select-company') }}" class="side-link"><i data-lucide="repeat"></i><span>Switch Company</span></a></li>
              @endif

              <li class="mobile-only">
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="side-link text-destructive-text"><i data-lucide="log-out"></i><span>Logout</span></button>
                </form>
              </li>
            @endif
          </ul>
        </div>
      @endforeach
    </nav>

    <!-- Signed-in user -->
    <div class="border-t border-sidebar-border p-2">
      <a href="{{ route('profile') }}" data-user-row class="flex items-center gap-2.5 rounded-lg p-2 transition-colors hover:bg-sidebar-accent/60">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sidebar-primary/12 text-xs font-semibold text-sidebar-primary-text">{{ mb_strtoupper(mb_substr($user->name ?? '', 0, 1)) }}</span>
        <span data-user-meta class="min-w-0 flex-1 leading-tight">
          <span class="block truncate text-sm font-medium text-sidebar-foreground">{{ $user->name }}</span>
          <span class="block truncate text-xs text-sidebar-foreground/75">{{ $user->role }}</span>
        </span>
        <i data-lucide="chevron-right" data-user-meta class="size-4 shrink-0 text-sidebar-foreground/40"></i>
      </a>
    </div>
  </aside>

  <!-- Name bubble for the icons-only sidebar (filled and placed by the script below) -->
  <div id="side-tip" class="side-tip" hidden></div>

  <script>
    /*
     * Sidebar folded to icons only: pointing at an icon (or reaching it with Tab)
     * shows its name next to it. The bubble is "fixed", so the sidebar's scroll
     * area can't cut it off.
     */
    (function () {
      var tip = document.getElementById('side-tip');
      var nav = document.querySelector('[data-nav]');

      function show(e) {
        var link = e.target.closest('.side-link');
        if (!link || !document.documentElement.classList.contains('sidebar-collapsed')) return;

        var box = link.getBoundingClientRect();
        tip.textContent = link.textContent.trim();
        tip.style.left = (box.right + 10) + 'px';
        tip.style.top = (box.top + box.height / 2) + 'px';
        tip.hidden = false;
      }

      function hide() {
        tip.hidden = true;
      }

      nav.addEventListener('mouseover', show);
      nav.addEventListener('focusin', show);
      nav.addEventListener('mouseleave', hide);
      nav.addEventListener('focusout', hide);
      nav.addEventListener('scroll', hide);
      // Leaving one icon for another: mouseover of the next one moves the bubble
      nav.addEventListener('mouseout', function (e) {
        if (!e.relatedTarget || !e.relatedTarget.closest('.side-link')) hide();
      });
    })();

    /*
     * Highlight the link of the open page, and open the sub-menu it sits in.
     * /material-inward/5/edit -> "Material Inward" (its link is /material-inward-register),
     * /items/create -> "Item" (its link is /items).
     */
    (function () {
      var path = location.pathname.replace(/\/+$/, '');
      var best = null;
      var bestLength = 0;

      document.querySelectorAll('[data-nav] a.side-link').forEach(function (link) {
        // A link with a #part is a shortcut into another page, not a page of its own
        if (link.hash) return;

        var linkPath = new URL(link.href).pathname.replace(/\/+$/, '');
        var basePath = linkPath.replace(/-register$/, '');

        [linkPath, basePath].forEach(function (candidate) {
          var matches = candidate && (path === candidate || path.indexOf(candidate + '/') === 0);

          // The longest match wins: /items/create beats /items
          if (matches && candidate.length > bestLength) {
            best = link;
            bestLength = candidate.length;
          }
        });
      });

      if (!best) return;

      best.classList.add('active');

      // Open every closed menu above the link
      var menu = best.closest('ul');
      while (menu) {
        if (menu.classList.contains('hidden')) {
          menu.classList.remove('hidden');
          var chevron = menu.previousElementSibling && menu.previousElementSibling.querySelector('[data-chevron]');
          if (chevron) chevron.classList.remove('-rotate-90');
        }
        menu = menu.parentElement.closest('ul');
      }
    })();
  </script>
