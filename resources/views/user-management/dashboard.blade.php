@extends('layouts.app')

@section('title', 'Overview')

@section('content')
      <!-- Page header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <h1 class="text-2xl font-bold tracking-tight text-foreground">Overview</h1>
          <p class="mt-1 text-sm text-muted-foreground">Everyone with access to the Northwind Labs workspace, at a glance.</p>
        </div>
        <div class="flex gap-2">
          <button type="button" data-toast="Report queued — you'll get an email when it's ready" class="inline-flex items-center gap-2 rounded-lg border border-input px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-accent"><i data-lucide="download" class="size-4"></i> Export</button>
          <a href="add-user.html" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"><i data-lucide="user-plus" class="size-4"></i> Invite user</a>
        </div>
      </div>

      <!-- KPI row — [data-sortable] makes these drag-reorderable and remembers the order -->
      <div class="mt-6 flex items-center justify-between gap-3">
        <p class="text-xs text-muted-foreground">Drag a card by its grip to reorder, or focus one and press <kbd class="rounded border border-border px-1 font-mono text-[10px]">⌘</kbd>+<kbd class="rounded border border-border px-1 font-mono text-[10px]">←</kbd>/<kbd class="rounded border border-border px-1 font-mono text-[10px]">→</kbd>.</p>
        <button type="button" data-sort-reset="overview-kpis" class="shrink-0 rounded-lg border border-input px-3 py-1.5 text-xs font-medium transition-colors hover:bg-accent">Reset order</button>
      </div>
      <div data-sortable="overview-kpis" class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div data-sort-item="kpi-total-users" data-sort-label="Total users" class="group/card relative flex flex-col gap-3 overflow-hidden rounded-xl border border-border bg-card p-4">
          <span class="absolute inset-x-0 top-0 h-0.5" style="background-color:var(--chart-1)"></span><button type="button" data-sort-handle aria-label="Reorder this card" class="absolute end-2 top-2 flex size-6 cursor-grab items-center justify-center rounded-md text-muted-foreground/0 transition-colors hover:bg-accent hover:text-foreground group-hover/card:text-muted-foreground focus-visible:text-muted-foreground active:cursor-grabbing"><i data-lucide="grip-vertical" class="size-3.5"></i></button>
          <div class="flex items-start justify-between">
            <span class="flex size-9 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--chart-1) 14%, transparent)"><i data-lucide="users" class="size-[18px]" style="color:var(--chart-1-text)"></i></span>
            <span data-sparkline="4820,5140,5390,5610,6020,6480,6810,7240,7690,8130,8520,8942" data-color="chart-1" data-w="84" data-h="30" data-area></span>
          </div>
          <div class="space-y-1">
            <p class="text-sm text-muted-foreground">Total users</p>
            <div class="flex items-baseline gap-2">
              <p class="font-mono text-2xl font-bold tracking-tight tabular-nums text-foreground">8,942</p>
              <span class="inline-flex items-center gap-0.5 font-mono text-xs font-medium tabular-nums text-success-text"><i data-lucide="trending-up" class="size-3.5"></i>4.9%</span>
            </div>
            <p class="text-xs text-muted-foreground">vs. last month</p>
          </div>
        </div>

        <div data-sort-item="kpi-active" data-sort-label="Active this month" class="group/card relative flex flex-col gap-3 overflow-hidden rounded-xl border border-border bg-card p-4">
          <span class="absolute inset-x-0 top-0 h-0.5" style="background-color:var(--chart-2)"></span><button type="button" data-sort-handle aria-label="Reorder this card" class="absolute end-2 top-2 flex size-6 cursor-grab items-center justify-center rounded-md text-muted-foreground/0 transition-colors hover:bg-accent hover:text-foreground group-hover/card:text-muted-foreground focus-visible:text-muted-foreground active:cursor-grabbing"><i data-lucide="grip-vertical" class="size-3.5"></i></button>
          <div class="flex items-start justify-between">
            <span class="flex size-9 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--chart-2) 14%, transparent)"><i data-lucide="activity" class="size-[18px]" style="color:var(--chart-2-text)"></i></span>
            <span data-sparkline="3410,3680,3820,3910,4280,4620,4870,5180,5490,5810,6120,6437" data-color="chart-2" data-w="84" data-h="30" data-area></span>
          </div>
          <div class="space-y-1">
            <p class="text-sm text-muted-foreground">Active this month</p>
            <div class="flex items-baseline gap-2">
              <p class="font-mono text-2xl font-bold tracking-tight tabular-nums text-foreground">6,437</p>
              <span class="inline-flex items-center gap-0.5 font-mono text-xs font-medium tabular-nums text-success-text"><i data-lucide="trending-up" class="size-3.5"></i>5.2%</span>
            </div>
            <p class="text-xs text-muted-foreground">72% of all users</p>
          </div>
        </div>

        <div data-sort-item="kpi-invites" data-sort-label="Pending invites" class="group/card relative flex flex-col gap-3 overflow-hidden rounded-xl border border-border bg-card p-4">
          <span class="absolute inset-x-0 top-0 h-0.5" style="background-color:var(--chart-4)"></span><button type="button" data-sort-handle aria-label="Reorder this card" class="absolute end-2 top-2 flex size-6 cursor-grab items-center justify-center rounded-md text-muted-foreground/0 transition-colors hover:bg-accent hover:text-foreground group-hover/card:text-muted-foreground focus-visible:text-muted-foreground active:cursor-grabbing"><i data-lucide="grip-vertical" class="size-3.5"></i></button>
          <div class="flex items-start justify-between">
            <span class="flex size-9 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--chart-4) 14%, transparent)"><i data-lucide="mail-plus" class="size-[18px]" style="color:var(--chart-4-text)"></i></span>
            <span data-sparkline="86,94,112,131,148,162,151,144,138,141,134,128" data-color="chart-4" data-w="84" data-h="30" data-area></span>
          </div>
          <div class="space-y-1">
            <p class="text-sm text-muted-foreground">Pending invites</p>
            <div class="flex items-baseline gap-2">
              <p class="font-mono text-2xl font-bold tracking-tight tabular-nums text-foreground">128</p>
              <span class="inline-flex items-center gap-0.5 font-mono text-xs font-medium tabular-nums text-success-text"><i data-lucide="trending-down" class="size-3.5"></i>4.5%</span>
            </div>
            <p class="text-xs text-muted-foreground">19 expire within 7 days</p>
          </div>
        </div>

        <div data-sort-item="kpi-privileged" data-sort-label="Privileged accounts" class="group/card relative flex flex-col gap-3 overflow-hidden rounded-xl border border-border bg-card p-4">
          <span class="absolute inset-x-0 top-0 h-0.5" style="background-color:var(--chart-3)"></span><button type="button" data-sort-handle aria-label="Reorder this card" class="absolute end-2 top-2 flex size-6 cursor-grab items-center justify-center rounded-md text-muted-foreground/0 transition-colors hover:bg-accent hover:text-foreground group-hover/card:text-muted-foreground focus-visible:text-muted-foreground active:cursor-grabbing"><i data-lucide="grip-vertical" class="size-3.5"></i></button>
          <div class="flex items-start justify-between">
            <span class="flex size-9 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--chart-3) 14%, transparent)"><i data-lucide="shield-check" class="size-[18px]" style="color:var(--chart-3-text)"></i></span>
            <span data-sparkline="41,42,42,44,45,45,46,46,47,47,48,48" data-color="chart-3" data-w="84" data-h="30" data-area></span>
          </div>
          <div class="space-y-1">
            <p class="text-sm text-muted-foreground">Privileged accounts</p>
            <div class="flex items-baseline gap-2">
              <p class="font-mono text-2xl font-bold tracking-tight tabular-nums text-foreground">48</p>
              <span class="inline-flex items-center gap-0.5 font-mono text-xs font-medium tabular-nums text-warning-text"><i data-lucide="trending-up" class="size-3.5"></i>2</span>
            </div>
            <p class="text-xs text-muted-foreground">6 pending recertification</p>
          </div>
        </div>
      </div>

      <!-- Growth + role mix
      <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-border bg-card xl:col-span-2">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border p-4">
            <div>
              <h2 class="text-base font-semibold text-foreground">User growth</h2>
              <p class="text-xs text-muted-foreground">Total vs. monthly active, trailing 12 months</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
              <span class="inline-flex items-center gap-1.5 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-1)"></span>Total</span>
              <span class="inline-flex items-center gap-1.5 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-2)"></span>Active</span>
            </div>
          </div>
          <div class="p-2">
            <div data-chart="area" data-series="userGrowth" data-height="290"></div>
          </div>
        </div>

        <div class="rounded-xl border border-border bg-card">
          <div class="border-b border-border p-4">
            <h2 class="text-base font-semibold text-foreground">Users by role</h2>
            <p class="text-xs text-muted-foreground">Current role assignment</p>
          </div>
          <div class="p-2">
            <div data-chart="donut" data-series="usersByRole" data-height="220"></div>
          </div>
          <ul class="space-y-2 border-t border-border p-4 pt-3">
            <li class="flex items-center justify-between text-sm"><span class="inline-flex items-center gap-2 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-1)"></span>Member</span><span class="font-mono font-medium tabular-nums text-foreground">5,218</span></li>
            <li class="flex items-center justify-between text-sm"><span class="inline-flex items-center gap-2 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-2)"></span>Editor</span><span class="font-mono font-medium tabular-nums text-foreground">2,140</span></li>
            <li class="flex items-center justify-between text-sm"><span class="inline-flex items-center gap-2 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-3)"></span>Manager</span><span class="font-mono font-medium tabular-nums text-foreground">1,024</span></li>
            <li class="flex items-center justify-between text-sm"><span class="inline-flex items-center gap-2 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-4)"></span>Admin</span><span class="font-mono font-medium tabular-nums text-foreground">512</span></li>
            <li class="flex items-center justify-between text-sm"><span class="inline-flex items-center gap-2 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-5)"></span>Owner</span><span class="font-mono font-medium tabular-nums text-foreground">48</span></li>
          </ul>
        </div>
      </div> -->

      <!-- Signups vs churn + weekly sign-ins
      <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-border bg-card xl:col-span-2">
          <div class="flex flex-wrap items-center justify-between gap-3 border-b border-border p-4">
            <div>
              <h2 class="text-base font-semibold text-foreground">Signups vs. churn</h2>
              <p class="text-xs text-muted-foreground">New accounts against deactivations</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
              <span class="inline-flex items-center gap-1.5 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-1)"></span>Signups</span>
              <span class="inline-flex items-center gap-1.5 text-muted-foreground"><span class="size-2 rounded-full" style="background-color:var(--chart-4)"></span>Churned</span>
            </div>
          </div>
          <div class="p-2">
            <div data-chart="column" data-series="signupsChurn" data-height="270"></div>
          </div>
        </div>

        <div class="rounded-xl border border-border bg-card">
          <div class="border-b border-border p-4">
            <h2 class="text-base font-semibold text-foreground">Sign-ins this week</h2>
            <p class="text-xs text-muted-foreground">Unique users per day</p>
          </div>
          <div class="p-2">
            <div data-chart="column" data-series="loginActivity" data-height="200"></div>
          </div>
          <div class="grid grid-cols-2 gap-px border-t border-border bg-border">
            <div class="bg-card p-4">
              <p class="text-xs text-muted-foreground">Weekday average</p>
              <p class="mt-0.5 font-mono text-lg font-bold tabular-nums text-foreground">4,376</p>
            </div>
            <div class="bg-card p-4">
              <p class="text-xs text-muted-foreground">Peak hour</p>
              <p class="mt-0.5 font-mono text-lg font-bold tabular-nums text-foreground">10:00</p>
            </div>
          </div>
        </div>
      </div> -->

      <!-- Recent users + activity
      <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-border bg-card xl:col-span-2">
          <div class="flex items-center justify-between border-b border-border p-4">
            <h2 class="text-base font-semibold text-foreground">Recently added</h2>
            <a href="users.html" class="inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground">View all <i data-lucide="arrow-right" class="size-3.5"></i></a>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="border-b border-border text-left text-xs font-medium text-muted-foreground">
                  <th class="px-4 py-2.5">User</th>
                  <th class="px-4 py-2.5">Role</th>
                  <th class="px-4 py-2.5">Team</th>
                  <th class="px-4 py-2.5">Status</th>
                  <th class="px-4 py-2.5">Added</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-border">
                <tr class="transition-colors hover:bg-muted/40">
                  <td class="px-4 py-2.5"><a href="user-detail.html" class="flex items-center gap-2.5"><span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-chart-1/12 text-[11px] font-semibold" style="color:var(--chart-1-text)">HM</span><span><span class="block font-medium text-foreground">Hannah Mbeki</span><span class="block text-xs text-muted-foreground">hannah.mbeki@northwind.co</span></span></a></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Editor</td>
                  <td class="px-4 py-2.5 text-muted-foreground">Marketing</td>
                  <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color:color-mix(in oklch, var(--status-active) 14%, transparent);color:var(--status-active-text)"><span class="size-1.5 rounded-full" style="background-color:var(--status-active)"></span>Active</span></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Aug 18, 2026</td>
                </tr>
                <tr class="transition-colors hover:bg-muted/40">
                  <td class="px-4 py-2.5"><a href="user-detail.html" class="flex items-center gap-2.5"><span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-chart-2/12 text-[11px] font-semibold" style="color:var(--chart-2-text)">DK</span><span><span class="block font-medium text-foreground">Dmitri Kovalenko</span><span class="block text-xs text-muted-foreground">d.kovalenko@northwind.co</span></span></a></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Member</td>
                  <td class="px-4 py-2.5 text-muted-foreground">Engineering</td>
                  <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color:color-mix(in oklch, var(--status-invited) 14%, transparent);color:var(--status-invited-text)"><span class="size-1.5 rounded-full" style="background-color:var(--status-invited)"></span>Invited</span></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Aug 18, 2026</td>
                </tr>
                <tr class="transition-colors hover:bg-muted/40">
                  <td class="px-4 py-2.5"><a href="user-detail.html" class="flex items-center gap-2.5"><span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-chart-3/12 text-[11px] font-semibold" style="color:var(--chart-3-text)">SO</span><span><span class="block font-medium text-foreground">Sofia Oliveira</span><span class="block text-xs text-muted-foreground">sofia.oliveira@northwind.co</span></span></a></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Manager</td>
                  <td class="px-4 py-2.5 text-muted-foreground">Support</td>
                  <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color:color-mix(in oklch, var(--status-active) 14%, transparent);color:var(--status-active-text)"><span class="size-1.5 rounded-full" style="background-color:var(--status-active)"></span>Active</span></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Aug 17, 2026</td>
                </tr>
                <tr class="transition-colors hover:bg-muted/40">
                  <td class="px-4 py-2.5"><a href="user-detail.html" class="flex items-center gap-2.5"><span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-chart-4/12 text-[11px] font-semibold" style="color:var(--chart-4-text)">RA</span><span><span class="block font-medium text-foreground">Rafael Antunes</span><span class="block text-xs text-muted-foreground">rafael.antunes@northwind.co</span></span></a></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Member</td>
                  <td class="px-4 py-2.5 text-muted-foreground">Sales</td>
                  <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color:color-mix(in oklch, var(--status-active) 14%, transparent);color:var(--status-active-text)"><span class="size-1.5 rounded-full" style="background-color:var(--status-active)"></span>Active</span></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Aug 17, 2026</td>
                </tr>
                <tr class="transition-colors hover:bg-muted/40">
                  <td class="px-4 py-2.5"><a href="user-detail.html" class="flex items-center gap-2.5"><span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-chart-5/12 text-[11px] font-semibold" style="color:var(--chart-5-text)">YT</span><span><span class="block font-medium text-foreground">Yuki Tanabe</span><span class="block text-xs text-muted-foreground">yuki.tanabe@northwind.co</span></span></a></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Editor</td>
                  <td class="px-4 py-2.5 text-muted-foreground">Engineering</td>
                  <td class="px-4 py-2.5"><span class="inline-flex items-center gap-1.5 rounded-full px-2 py-0.5 text-xs font-medium" style="background-color:color-mix(in oklch, var(--status-inactive) 14%, transparent);color:var(--status-inactive-text)"><span class="size-1.5 rounded-full" style="background-color:var(--status-inactive)"></span>Inactive</span></td>
                  <td class="px-4 py-2.5 text-muted-foreground">Aug 16, 2026</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="rounded-xl border border-border bg-card">
          <div class="flex items-center justify-between border-b border-border p-4">
            <h2 class="text-base font-semibold text-foreground">Recent activity</h2>
            <a href="audit-log.html" class="text-sm text-muted-foreground transition-colors hover:text-foreground">Audit log</a>
          </div>
          <ul class="divide-y divide-border">
            <li class="flex gap-3 p-4">
              <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" style="background-color:color-mix(in oklch, var(--chart-1) 14%, transparent)"><i data-lucide="user-plus" class="size-4" style="color:var(--chart-1-text)"></i></span>
              <div class="min-w-0">
                <p class="text-sm text-foreground"><span class="font-medium">Priya Raghavan</span> invited 6 users to <span class="font-medium">Sales</span></p>
                <p class="mt-0.5 text-xs text-muted-foreground">12 minutes ago</p>
              </div>
            </li>
            <li class="flex gap-3 p-4">
              <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" style="background-color:color-mix(in oklch, var(--chart-3) 14%, transparent)"><i data-lucide="key-round" class="size-4" style="color:var(--chart-3-text)"></i></span>
              <div class="min-w-0">
                <p class="text-sm text-foreground"><span class="font-medium">Tomas Berg</span> changed the <span class="font-medium">Billing Admin</span> role scope</p>
                <p class="mt-0.5 text-xs text-muted-foreground">48 minutes ago</p>
              </div>
            </li>
            <li class="flex gap-3 p-4">
              <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" style="background-color:color-mix(in oklch, var(--destructive) 14%, transparent)"><i data-lucide="shield-alert" class="size-4" style="color:var(--destructive-text)"></i></span>
              <div class="min-w-0">
                <p class="text-sm text-foreground">3 failed sign-in attempts blocked for <span class="font-medium">l.chen@northwind.co</span></p>
                <p class="mt-0.5 text-xs text-muted-foreground">2 hours ago</p>
              </div>
            </li>
            <li class="flex gap-3 p-4">
              <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" style="background-color:color-mix(in oklch, var(--chart-2) 14%, transparent)"><i data-lucide="users-round" class="size-4" style="color:var(--chart-2-text)"></i></span>
              <div class="min-w-0">
                <p class="text-sm text-foreground"><span class="font-medium">Design</span> team was merged into <span class="font-medium">Product</span></p>
                <p class="mt-0.5 text-xs text-muted-foreground">5 hours ago</p>
              </div>
            </li>
            <li class="flex gap-3 p-4">
              <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" style="background-color:color-mix(in oklch, var(--chart-4) 14%, transparent)"><i data-lucide="user-minus" class="size-4" style="color:var(--chart-4-text)"></i></span>
              <div class="min-w-0">
                <p class="text-sm text-foreground"><span class="font-medium">Lucia Ferrero</span> deprovisioned 12 dormant accounts</p>
                <p class="mt-0.5 text-xs text-muted-foreground">Yesterday, 4:20 PM</p>
              </div>
            </li>
            <li class="flex gap-3 p-4">
              <span class="mt-0.5 flex size-8 shrink-0 items-center justify-center rounded-full" style="background-color:color-mix(in oklch, var(--chart-5) 14%, transparent)"><i data-lucide="fingerprint" class="size-4" style="color:var(--chart-5-text)"></i></span>
              <div class="min-w-0">
                <p class="text-sm text-foreground">MFA enforcement enabled for <span class="font-medium">Engineering</span></p>
                <p class="mt-0.5 text-xs text-muted-foreground">Yesterday, 11:02 AM</p>
              </div>
            </li>
          </ul>
        </div>
      </div> -->

      <!-- Team distribution + attention
      <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="rounded-xl border border-border bg-card xl:col-span-2">
          <div class="border-b border-border p-4">
            <h2 class="text-base font-semibold text-foreground">Users by team</h2>
            <p class="text-xs text-muted-foreground">Assigned members across the workspace</p>
          </div>
          <div class="p-2">
            <div data-chart="bar" data-series="usersByTeam" data-height="260"></div>
          </div>
        </div>

        <div class="rounded-xl border border-border bg-card">
          <div class="border-b border-border p-4">
            <h2 class="text-base font-semibold text-foreground">Needs attention</h2>
            <p class="text-xs text-muted-foreground">Open items across access and hygiene</p>
          </div>
          <ul class="divide-y divide-border">
            <li><a href="admins.html" class="flex items-center gap-3 p-4 transition-colors hover:bg-muted/40"><span class="flex size-8 shrink-0 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--destructive) 14%, transparent)"><i data-lucide="shield-alert" class="size-4" style="color:var(--destructive-text)"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-medium text-foreground">6 admins overdue recertification</span><span class="block text-xs text-muted-foreground">Q3 access review closes in 9 days</span></span><i data-lucide="chevron-right" class="size-4 shrink-0 text-muted-foreground"></i></a></li>
            <li><a href="sessions.html" class="flex items-center gap-3 p-4 transition-colors hover:bg-muted/40"><span class="flex size-8 shrink-0 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--warning) 14%, transparent)"><i data-lucide="monitor-smartphone" class="size-4" style="color:var(--warning-text)"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-medium text-foreground">14 sessions on unrecognised devices</span><span class="block text-xs text-muted-foreground">Review or revoke from Sessions</span></span><i data-lucide="chevron-right" class="size-4 shrink-0 text-muted-foreground"></i></a></li>
            <li><a href="users.html" class="flex items-center gap-3 p-4 transition-colors hover:bg-muted/40"><span class="flex size-8 shrink-0 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--warning) 14%, transparent)"><i data-lucide="user-x" class="size-4" style="color:var(--warning-text)"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-medium text-foreground">74 accounts dormant 90+ days</span><span class="block text-xs text-muted-foreground">Reclaiming these frees 74 seats</span></span><i data-lucide="chevron-right" class="size-4 shrink-0 text-muted-foreground"></i></a></li>
            <li><a href="invitations.html" class="flex items-center gap-3 p-4 transition-colors hover:bg-muted/40"><span class="flex size-8 shrink-0 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--chart-2) 14%, transparent)"><i data-lucide="mail-plus" class="size-4" style="color:var(--chart-2-text)"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-medium text-foreground">19 invitations expiring soon</span><span class="block text-xs text-muted-foreground">Resend before they lapse</span></span><i data-lucide="chevron-right" class="size-4 shrink-0 text-muted-foreground"></i></a></li>
            <li><a href="security.html" class="flex items-center gap-3 p-4 transition-colors hover:bg-muted/40"><span class="flex size-8 shrink-0 items-center justify-center rounded-lg" style="background-color:color-mix(in oklch, var(--chart-1) 14%, transparent)"><i data-lucide="fingerprint" class="size-4" style="color:var(--chart-1-text)"></i></span><span class="min-w-0 flex-1"><span class="block text-sm font-medium text-foreground">1,967 users without MFA</span><span class="block text-xs text-muted-foreground">78% enrolled workspace-wide</span></span><i data-lucide="chevron-right" class="size-4 shrink-0 text-muted-foreground"></i></a></li>
          </ul>
        </div>
      </div>
      -->
@endsection
