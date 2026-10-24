<!doctype html>
<html lang="en">

{{-- Layout of the pages outside the app: Login, Register, Register Company, Select Company.
     Same look as the app's forms (form-card in public/css/custom.css), with no sidebar or header. --}}
<head>
  <title>@yield('title') · Itallic</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" />

  <!-- Same saved appearance as the app (light / dark, colour), before paint -->
  <script>
    (function() {
      try {
        var root = document.documentElement;
        var t = localStorage.getItem("usermgmt-theme") || "light";
        var dark = t === "dark" || (t === "system" && matchMedia("(prefers-color-scheme: dark)").matches);
        if (dark) root.classList.add("dark");

        var presets = {
          violet: [293, 0.24, 0.494, 0.75],
          blue: [262, 0.19, 0.484, 0.717],
          teal: [175, 0.13, 0.444, 0.674],
          rose: [357, 0.16, 0.493, 0.716]
        };
        // Violet is the app's colour when none was picked
        var p = presets[localStorage.getItem("usermgmt-color-preset") || "violet"];
        if (p) {
          root.style.setProperty("--primary", "oklch(" + (dark ? 0.68 : 0.589) + " " + p[1] + " " + p[0] + ")");
          root.style.setProperty("--primary-text", "oklch(" + (dark ? p[3] : p[2]) + " " + p[1] + " " + p[0] + ")");
        }
      } catch (e) {}
    })();
  </script>
  <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body class="auth-page">
  <main class="auth-main">
    <div class="auth-box @yield('width')">
      <!-- Brand -->
      <a href="{{ route('home') }}" class="auth-brand">
        <span class="auth-logo">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/><path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/><path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/><path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/></svg>
        </span>
        <span>
          <span class="block text-lg font-bold leading-tight text-foreground">Itallic</span>
          <span class="block text-xs text-muted-foreground">Billing &amp; Accounting</span>
        </span>
      </a>

      @yield('content')
    </div>
  </main>

  @include('components.footer')
</body>

</html>
