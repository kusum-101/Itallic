<aside
    id="app-sidebar"
    data-sidebar
    aria-label="Primary"
    class="fixed left-0 top-0 z-50 flex h-screen w-[264px] -translate-x-full flex-col border-r border-sidebar-border bg-sidebar transition-transform duration-300 lg:translate-x-0">
    <!-- Brand -->
    <div class="flex h-16 items-center justify-between gap-3 border-b border-sidebar-border px-4">
      <a href="{{ route('dashboardUser') }}" data-brand-link class="flex items-center gap-2.5">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sidebar-primary">
          <i data-lucide="building-2" class="size-4 text-sidebar-primary-foreground"></i>
        </span>
        <span data-brand-text class="flex flex-col leading-none">
          <span class="text-sm font-bold tracking-tight text-sidebar-foreground">{{ $currentCompany->company_name ?? '' }}</span>
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

    <!-- Nav -->
    <nav class="flex-1 space-y-4 overflow-y-auto px-3 py-4" data-nav>
      <div>
        <button type="button" data-group-toggle class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-0.5 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/75 hover:text-sidebar-foreground"><span>Overview</span><i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform -rotate-90        "></i></button>
        <ul class="space-y-0.5 hidden">
          <li><a href="{{ route('dashboardUser') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="layout-dashboard" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Dashboard</span></a></li>
        </ul>
      </div>

      <div>
        <!-- MASTERS Heading -->
        <button
          type="button"
          data-group-toggle
          class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-0.5 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/75 hover:text-sidebar-foreground">
          <span>MASTERS</span>
          <i
            data-lucide="chevron-down"
            data-chevron
            class="size-3.5 shrink-0 transition-transform -rotate-90
"></i>
        </button>

        <ul class="space-y-0.5 hidden">

          <!-- Company -->

          <li>
            <a
              href="{{ route('company-setting') }}"
              class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <i
                data-lucide="settings"
                class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
              <span>Company Setting</span>
            </a>
          </li>


          <!-- Account Master -->
          <li>

            <button
              type="button"
              data-group-toggle
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <span class="flex items-center gap-3">
                <i
                  data-lucide="user-plus"
                  class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>

                <span>Account Master</span>
              </span>

              <i
                data-lucide="chevron-down"
                data-chevron
                class="size-3.5 shrink-0 transition-transform -rotate-90
"></i>
            </button>

            <!-- Account Master Sub Menu -->
            <ul class="space-y-0.5 pl-9 hidden ">

              <!-- List Group -->
              <li>
                <a
                  href="{{ route('list-group') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="folder"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>List Group</span>
                </a>
              </li>

              <!-- List Ledger -->
              <li>
                <a
                  href="{{ route('list-ledger') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="book-open"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>List Ledger</span>
                </a>
              </li>

            </ul>
          </li>

          <li>
            <!-- Inventory Master -->
            <button
              type="button"
              data-group-toggle
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <span class="flex items-center gap-3">
                <i
                  data-lucide="mail-plus"
                  class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>

                <span>Inventory Master</span>
              </span>

              <i
                data-lucide="chevron-down"
                data-chevron
                class="size-3.5 shrink-0 transition-transform"></i>
            </button>

            <!-- Inventory Master Sub Menu -->
            <ul class="ml-6 space-y-0.5 hidden">

              <li>
                <a
                  href="{{ route('stock-group') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="layers"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Stock Group</span>
                </a>
              </li>

              <li>
                <a
                  href="{{ route('stock-category') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="tags"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Stock Category</span>
                </a>
              </li>

              <li>
                <a
                  href="{{ route('item') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="layers"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Item</span>
                </a>
              </li>
              <li>
                <a
                  href="{{ route('unit') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="repeat"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Unit</span>
                </a>
              </li>

              <li>
                <a
                  href="{{ route('godown') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="building-2"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Godown</span>
                </a>
              </li>

              <li>
                <a
                  href="{{ route('copy-allocation-details') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="copy"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Copy Allocation Details</span>
                </a>
              </li>

            </ul>
          </li>


          <!-- User Master -->
          <li>

            <button
              type="button"
              data-group-toggle
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <span class="flex items-center gap-3">
                <i
                  data-lucide="user-cog"
                  class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>

                <span>User Master</span>
              </span>

              <i
                data-lucide="chevron-down"
                data-chevron
                class="size-3.5 shrink-0 transition-transform -rotate-90
"></i>
            </button>

            <!-- User Master Sub Menu -->
            <ul class="ml-6 space-y-0.5 hidden">

              <!-- Users -->
              <li>
                <a
                  href="{{ route('users') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="users"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Users</span>
                </a>
              </li>

              <!-- Department -->
              <li>
                <a
                  href="{{ route('department') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="users-round"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Department</span>
                </a>
              </li>

              <!-- Position -->
              <li>
                <a
                  href="{{ route('position') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="user"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Position</span>
                </a>
              </li>

            </ul>
          </li>


        </ul>
      </div>


      <div>
        <button type="button" data-group-toggle class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-0.5 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/75 hover:text-sidebar-foreground"><span>Vouchers</span><i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform -rotate-90
"></i></button>
        <ul class="space-y-0.5 hidden">
          <!-- Account Vouchers -->
          <li>

            <button
              type="button"
              data-group-toggle
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <span class="flex items-center gap-3">
                <i
                  data-lucide="user-cog"
                  class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>

                <span>Account Vouchers</span>
              </span>

              <i
                data-lucide="chevron-down"
                data-chevron
                class="size-3.5 shrink-0 transition-transform -rotate-90
"></i>
            </button>

            <!-- Account Vouchers Sub Menu -->
            <ul class="ml-6 space-y-0.5 hidden">

              <!-- Payment Register -->
              <li>
                <a
                  href="{{ route('payment-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="credit-card" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Payment Register</span>
                </a>
              </li>

              <!-- Purchase Register -->
              <li>
                <a
                  href="{{ route('purchase-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="boxes" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Purchase Register</span>
                </a>
              </li>

              <!-- Contra Register -->
              <li>
                <a
                  href="{{ route('contra-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="repeat" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Contra Register</span>
                </a>
              </li>

              <!-- Receipt Register -->
              <li>
                <a
                  href="{{ route('receipt-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="receipt" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Receipt Register</span>
                </a>
              </li>

              <!-- Journal Register -->
              <li>
                <a
                  href="{{ route('journal-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="book-open" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Journal Register</span>
                </a>
              </li>

              <!-- Sales Register -->
              <li>
                <a
                  href="{{ route('sales-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="chart-line" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Sales Register</span>
                </a>
              </li>

              <!-- Credit Note Register -->
              <li>
                <a
                  href="{{ route('credit-note-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="scroll-text" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Credit Note Register</span>
                </a>
              </li>

              <!-- Cheque Register -->
              <li>
                <a
                  href="{{ route('cheque-register') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i data-lucide="credit-card" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Cheque Register</span>
                </a>
              </li>

            </ul>
          </li>

          <!-- Inventory Vouchers -->
          <li>

            <button
              type="button"
              data-group-toggle
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <span class="flex items-center gap-3">
                <i
                  data-lucide="key-round"
                  class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>

                <span>Inventory Vouchers</span>
              </span>

              <i
                data-lucide="chevron-down"
                data-chevron
                class="size-3.5 shrink-0 transition-transform -rotate-90
"></i>
            </button>

            <!-- Inventory Vouchers Sub Menu -->
            <ul class="ml-6 space-y-0.5 hidden">

              <!-- Material Inward -->
              <li>
                <a
                  href="{{ route('material-inward') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="download"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Material Inward</span>
                </a>
              </li>

              <!-- Material Outward -->
              <li>
                <a
                  href="{{ route('material-outward') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="upload"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Material Outward</span>
                </a>
              </li>

              <!-- Material Internal Transfer -->
              <li>
                <a
                  href="{{ route('material-internal-transfer') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="repeat"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Material Internal Transfer</span>
                </a>
              </li>

            </ul>
          </li>
          <!-- Commercial Vouchers -->
          <li>

            <button
              type="button"
              data-group-toggle
              class="flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
              <span class="flex items-center gap-3">
                <i
                  data-lucide="lock"
                  class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>

                <span>Commercial Vouchers</span>
              </span>

              <i
                data-lucide="chevron-down"
                data-chevron
                class="size-3.5 shrink-0 transition-transform -rotate-90
"></i>
            </button>

            <!-- Commercial Vouchers Sub Menu -->
            <ul class="ml-6 space-y-0.5 hidden">

              <!-- Purchase Order -->
              <li>
                <a
                  href="{{ route('purchase-order') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="clipboard"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Purchase Order</span>
                </a>
              </li>

              <!-- Rate Card -->
              <li>
                <a
                  href="{{ route('rate-card') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="credit-card"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Rate Card</span>
                </a>
              </li>

              <!-- Quotation -->
              <li>
                <a
                  href="{{ route('quotation') }}"
                  class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground">
                  <i
                    data-lucide="scroll-text"
                    class="size-[18px] shrink-0 text-sidebar-foreground/60"></i>
                  <span>Quotation</span>
                </a>
              </li>

            </ul>
          </li>


        </ul>
      </div>



      <div>
        <button type="button" data-group-toggle class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-0.5 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/75 hover:text-sidebar-foreground"><span>REPORTS</span><i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform -rotate-90
"></i></button>
        <ul class="space-y-0.5 hidden">
          <li><a href="{{ route('day-book') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="users-round" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Day Book</span></a></li>
          <li><a href="{{ route('balance-sheet') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="boxes" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Balance Sheet</span></a></li>
          <li><a href="{{ route('group-report') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="monitor-smartphone" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Group Report</span></a></li>
          <li><a href="{{ route('profit-loss') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="scroll-text" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Profit & Loss A/c</span></a></li>
          <li><a href="{{ route('ledger-summary') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="chart-column" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Ledger Summary</span></a></li>
          <li><a href="{{ route('stock-summary') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="layers" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Stock Summary</span></a></li>
          <li><a href="{{ route('all-vouchers') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="chart-line" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>All Vouchers</span></a></li>
          <li><a href="{{ route('gst-sale') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="dollar-sign" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Gst Sale</span></a></li>
          <li><a href="{{ route('payment-due') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="credit-card" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Payment Due Details</span></a></li>
          <li><a href="{{ route('leave-summary') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="calendar" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Leave Summary</span></a></li>
        </ul>
      </div>

      <div>
        <button type="button" data-group-toggle class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-0.5 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/75 hover:text-sidebar-foreground"><span>HR</span><i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform -rotate-90
"></i></button>
        <ul class="space-y-0.5 hidden">
          <li><a href="{{ route('daily-reports') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="credit-card" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Daily Reports</span></a></li>
          <li><a href="{{ route('attendance') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="repeat" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Attendance</span></a></li>
          <li><a href="{{ route('leave') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="layers" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Leave</span></a></li>
        </ul>
      </div>

      <div>
        <button type="button" data-group-toggle class="flex w-full items-center justify-between rounded-md px-3 pb-1.5 pt-0.5 text-[10px] font-semibold uppercase tracking-widest text-sidebar-foreground/75 hover:text-sidebar-foreground "><span>Account</span><i data-lucide="chevron-down" data-chevron class="size-3.5 shrink-0 transition-transform 
"></i></button>
        <ul class="space-y-0.5 h">
          <li><a href="{{ route('profile') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="user" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Profile</span></a></li>
          <li><a href="{{ route('settings') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="settings" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Settings</span></a></li>

          {{-- Switch Company + Logout: only on mobile (mobile-only in public/css/custom.css),
               because on bigger screens they are already in the header --}}
          @if(\App\Models\Company::where('user_id', session('user_id'))->count() >= 2)
          <li class="mobile-only"><a href="{{ route('select-company') }}" class="group flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-sidebar-foreground/75 transition-colors hover:bg-sidebar-accent/60 hover:text-sidebar-foreground"><i data-lucide="repeat" class="size-[18px] shrink-0 text-sidebar-foreground/60"></i><span>Switch Company</span></a></li>
          @endif

          <li class="mobile-only">
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="group flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-destructive-text transition-colors hover:bg-sidebar-accent/60"><i data-lucide="log-out" class="size-[18px] shrink-0"></i><span>Logout</span></button>
            </form>
          </li>

        </ul>
      </div>
    </nav>

    <!-- Signed-in user -->
    <div class="border-t border-sidebar-border p-3">
      <a href="{{ route('profile') }}" data-user-row class="flex items-center gap-3 rounded-lg p-2 transition-colors hover:bg-sidebar-accent/60">
        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sidebar-primary/12 text-xs font-semibold text-sidebar-primary-text">{{ mb_strtoupper(mb_substr($user->name ?? '', 0, 1)) }}</span>
        <span data-user-meta class="min-w-0 flex-1 leading-tight">
          <span class="block truncate text-sm font-medium text-sidebar-foreground">{{ $user->name }}</span>
          <span class="block truncate text-xs text-sidebar-foreground/75">{{ $user->role }}</span>
        </span>
        <i data-lucide="chevron-right" data-user-meta class="size-4 shrink-0 text-sidebar-foreground/40"></i>
      </a>
    </div>
  </aside>