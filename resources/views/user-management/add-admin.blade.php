@extends('layouts.app')

@section('title', 'Grant Admin')

@section('content')
      <nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-sm text-muted-foreground">
        <a href="admins.html" class="transition-colors hover:text-foreground">Admins</a>
        <i data-lucide="chevron-right" class="size-3.5"></i>
        <span class="text-foreground">Grant admin</span>
      </nav>

      <div class="mt-4">
        <h1 class="text-2xl font-bold tracking-tight text-foreground">Grant admin rights</h1>
        <p class="mt-1 text-sm text-muted-foreground">Elevated access is scoped and time-boxed by default. Every grant is recorded in the audit log.</p>
      </div>

      <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <form data-auth="Admin rights granted — the audit log has been updated" data-redirect="admins.html" class="space-y-4 xl:col-span-2">
          <div class="rounded-xl border border-border bg-card">
            <div class="border-b border-border p-4">
              <h2 class="text-base font-semibold text-foreground">Who</h2>
              <p class="text-xs text-muted-foreground">Admin rights can only be granted to an existing, active account</p>
            </div>
            <div class="space-y-4 p-5">
              <div>
                <label for="admin-user" class="block text-sm font-medium text-foreground">User</label>
                <select id="admin-user" required class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring">
                  <option value="">Select a user…</option>
                  <option selected>Clara Lindqvist — clara.lindqvist@northwind.co</option>
                  <option>Mateo Ibarra — mateo.ibarra@northwind.co</option>
                  <option>Tariq Rahman — tariq.rahman@northwind.co</option>
                  <option>Aisha Bakr — aisha.bakr@northwind.co</option>
                  <option>Kwame Mensah — kwame.mensah@northwind.co</option>
                </select>
              </div>
              <div class="flex items-start gap-3 rounded-lg border border-border bg-muted/40 p-3">
                <span class="flex size-9 shrink-0 items-center justify-center rounded-full text-xs font-semibold" style="background-color:color-mix(in oklch, var(--chart-2) 14%, transparent);color:var(--chart-2-text)">CL</span>
                <div class="min-w-0 flex-1 text-sm">
                  <p class="font-medium text-foreground">Clara Lindqvist</p>
                  <p class="text-xs text-muted-foreground">Member · Marketing · joined Feb 24, 2025</p>
                  <p class="mt-1 inline-flex items-center gap-1.5 text-xs" style="color:var(--success-text)"><i data-lucide="shield-check" class="size-3.5"></i>MFA enrolled — meets the admin prerequisite</p>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-border bg-card">
            <div class="border-b border-border p-4">
              <h2 class="text-base font-semibold text-foreground">What</h2>
              <p class="text-xs text-muted-foreground">Pick the narrowest role that covers the work</p>
            </div>
            <div class="space-y-3 p-5">
              <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-input p-4 transition-colors hover:bg-accent">
                <input type="radio" name="adminRole" checked class="mt-0.5 size-4 shrink-0 accent-primary" />
                <span class="min-w-0"><span class="block text-sm font-medium text-foreground">Team Admin</span><span class="block text-xs text-muted-foreground">Manage members, roles, and groups within a single team. Cannot touch billing or workspace settings.</span></span>
              </label>
              <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-input p-4 transition-colors hover:bg-accent">
                <input type="radio" name="adminRole" class="mt-0.5 size-4 shrink-0 accent-primary" />
                <span class="min-w-0"><span class="block text-sm font-medium text-foreground">User Admin</span><span class="block text-xs text-muted-foreground">Create, suspend, and deprovision accounts workspace-wide. Cannot change roles above Manager.</span></span>
              </label>
              <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-input p-4 transition-colors hover:bg-accent">
                <input type="radio" name="adminRole" class="mt-0.5 size-4 shrink-0 accent-primary" />
                <span class="min-w-0"><span class="block text-sm font-medium text-foreground">Billing Admin</span><span class="block text-xs text-muted-foreground">Manage plans, payment methods, and invoices. No access to user data.</span></span>
              </label>
              <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-input p-4 transition-colors hover:bg-accent">
                <input type="radio" name="adminRole" class="mt-0.5 size-4 shrink-0 accent-primary" />
                <span class="min-w-0"><span class="block text-sm font-medium text-foreground">Security Admin</span><span class="block text-xs text-muted-foreground">Set authentication policy, review risk, and revoke sessions. Cannot grant admin rights.</span></span>
              </label>
              <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors" style="border-color:color-mix(in oklch, var(--destructive) 35%, transparent)">
                <input type="radio" name="adminRole" class="mt-0.5 size-4 shrink-0 accent-primary" />
                <span class="min-w-0"><span class="flex items-center gap-1.5 text-sm font-medium text-foreground">Owner <i data-lucide="crown" class="size-3.5" style="color:var(--chart-4-text)"></i></span><span class="block text-xs text-muted-foreground">Unrestricted. Can delete the workspace and remove other owners. Requires approval from a second owner.</span></span>
              </label>
            </div>
          </div>

          <div class="rounded-xl border border-border bg-card">
            <div class="border-b border-border p-4">
              <h2 class="text-base font-semibold text-foreground">Where and how long</h2>
              <p class="text-xs text-muted-foreground">Scope and expiry keep standing privilege from accumulating</p>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2">
              <div>
                <label for="scope" class="block text-sm font-medium text-foreground">Scope</label>
                <select id="scope" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring">
                  <option>Engineering</option>
                  <option>Sales</option>
                  <option>Support</option>
                  <option selected>Marketing</option>
                  <option>Finance</option>
                  <option>People Ops</option>
                  <option>Workspace (all teams)</option>
                </select>
              </div>
              <div>
                <label for="admin-expires" class="block text-sm font-medium text-foreground">Expires</label>
                <select id="admin-expires" class="mt-1.5 h-10 w-full rounded-lg border border-input bg-background px-3 text-sm outline-none transition-colors focus:border-ring">
                  <option>30 days</option>
                  <option selected>90 days (next access review)</option>
                  <option>180 days</option>
                  <option>Does not expire</option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <label for="justification" class="block text-sm font-medium text-foreground">Justification</label>
                <textarea id="justification" rows="3" required class="mt-1.5 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none transition-colors focus:border-ring" placeholder="Why does this person need elevated access?">Taking over Marketing team administration while Beatriz Correia is on leave through Q4.</textarea>
                <p class="mt-1.5 text-xs text-muted-foreground">Shown to reviewers during the next access recertification.</p>
              </div>
            </div>
          </div>

          <div class="flex flex-wrap items-center justify-end gap-2">
            <a href="admins.html" class="rounded-lg px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground">Cancel</a>
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-primary/90"><i data-lucide="shield" class="size-4"></i> Grant admin rights</button>
          </div>
        </form>

        <aside class="space-y-4">
          <div class="rounded-xl border border-border bg-card p-4">
            <h2 class="text-sm font-semibold text-foreground">Resulting permissions</h2>
            <p class="mt-1 text-xs text-muted-foreground">Team Admin, scoped to Marketing</p>
            <ul class="mt-3 space-y-2 text-sm">
              <li class="flex items-center gap-2"><i data-lucide="check" class="size-3.5 shrink-0" style="color:var(--success-text)"></i><span class="text-foreground">Invite and remove Marketing members</span></li>
              <li class="flex items-center gap-2"><i data-lucide="check" class="size-3.5 shrink-0" style="color:var(--success-text)"></i><span class="text-foreground">Assign roles up to Manager</span></li>
              <li class="flex items-center gap-2"><i data-lucide="check" class="size-3.5 shrink-0" style="color:var(--success-text)"></i><span class="text-foreground">Manage Marketing groups</span></li>
              <li class="flex items-center gap-2"><i data-lucide="check" class="size-3.5 shrink-0" style="color:var(--success-text)"></i><span class="text-foreground">View the Marketing audit trail</span></li>
              <li class="flex items-center gap-2"><i data-lucide="x" class="size-3.5 shrink-0 text-muted-foreground"></i><span class="text-muted-foreground">Billing and payment methods</span></li>
              <li class="flex items-center gap-2"><i data-lucide="x" class="size-3.5 shrink-0 text-muted-foreground"></i><span class="text-muted-foreground">Workspace security policy</span></li>
              <li class="flex items-center gap-2"><i data-lucide="x" class="size-3.5 shrink-0 text-muted-foreground"></i><span class="text-muted-foreground">Granting admin rights to others</span></li>
            </ul>
          </div>

          <div class="rounded-xl border p-4" style="border-color:color-mix(in oklch, var(--warning) 35%, transparent);background-color:color-mix(in oklch, var(--warning) 7%, transparent)">
            <div class="flex gap-3">
              <i data-lucide="triangle-alert" class="mt-0.5 size-4 shrink-0" style="color:var(--warning-text)"></i>
              <div>
                <p class="text-sm font-semibold text-foreground">Marketing already has 1 admin</p>
                <p class="mt-0.5 text-xs text-muted-foreground">Beatriz Correia holds Team Admin for this scope. Two admins on a six-person team is worth a second look.</p>
              </div>
            </div>
          </div>

          <div class="rounded-xl border border-border bg-card p-4">
            <h2 class="text-sm font-semibold text-foreground">Least privilege</h2>
            <p class="mt-1 text-xs text-muted-foreground">Grants expire at the next access review by default. Reviewers see the justification and the account's admin activity since the grant, and can renew or revoke in one action.</p>
            <a href="permissions.html" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-primary-text underline underline-offset-2 hover:no-underline">Compare role permissions <i data-lucide="arrow-right" class="size-3"></i></a>
          </div>
        </aside>
      </div>
@endsection
