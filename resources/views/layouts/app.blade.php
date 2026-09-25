<!doctype html>
<html lang="en">

<head>
  <title>@yield('title', 'Dashboard') · Itallic</title>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)" />
  <meta name="theme-color" content="#1b1330" media="(prefers-color-scheme: dark)" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" />

  <!-- Apply saved appearance before paint so there is no flash of the wrong theme -->
  <script>
    (function() {
      try {
        var root = document.documentElement;
        var t = localStorage.getItem("usermgmt-theme") || "light";
        var dark = t === "dark" || (t === "system" && matchMedia("(prefers-color-scheme: dark)").matches);
        if (dark) root.classList.add("dark");

        var d = localStorage.getItem("usermgmt-density");
        root.classList.add("density-" + (d && ["compact", "comfortable", "spacious"].includes(d) ? d : "comfortable"));

        // Keep in sync with PRESETS in src/js/modules/preset.js
        // [hue, chroma, textLightness(light), textLightness(dark)]
        var presets = {
          violet: [293, 0.24, 0.494, 0.75],
          blue: [262, 0.19, 0.484, 0.717],
          teal: [175, 0.13, 0.444, 0.674],
          rose: [357, 0.16, 0.493, 0.716]
        };
        var p = presets[localStorage.getItem("usermgmt-color-preset")];
        if (p) {
          var fill = "oklch(" + (dark ? 0.68 : 0.589) + " " + p[1] + " " + p[0] + ")";
          ["--primary", "--sidebar-primary", "--chart-1", "--ring", "--sidebar-ring"].forEach(function(v) {
            root.style.setProperty(v, fill);
          });
          var text = "oklch(" + (dark ? p[3] : p[2]) + " " + p[1] + " " + p[0] + ")";
          ["--primary-text", "--sidebar-primary-text", "--chart-1-text"].forEach(function(v) {
            root.style.setProperty(v, text);
          });
          root.style.setProperty("--sidebar-accent-foreground", dark ? "oklch(0.95 " + p[1] * 0.12 + " " + p[0] + ")" : "oklch(0.40 " + p[1] * 0.62 + " " + p[0] + ")");
        }

        if (localStorage.getItem("usermgmt-sidebar") === "collapsed") root.classList.add("sidebar-collapsed");
      } catch (e) {}
    })();
  </script>
  <script type="module" crossorigin src="{{ asset('assets/main-KQ41WFZu.js') }}"></script>
  <link rel="stylesheet" href="{{ asset('assets/main-BDUTwv5H.css') }}">
  <!-- Our own small styles (e.g. form-grid for side-by-side form fields) -->
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  @stack('head')
</head>

<body class="min-h-screen">
  <!-- Skip to content (keyboard users) -->
  <a href="#main-content" class="skip-link">Skip to content</a>

  <!-- Mobile overlay -->
  <div data-sidebar-overlay class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>

  @include('components.sidebar')

  <div data-app-shell class="flex min-h-screen min-w-0 flex-col lg:ms-[264px]">
    @include('components.header')

    <main id="main-content" tabindex="-1" class="min-w-0 flex-1 p-4 sm:p-6">
      @yield('content')
    </main>

    @include('components.footer')
  </div>

  <!-- Row action menus: click the 3-line button to show the options, click outside / Esc to close.
       The panel is position:fixed so it is not cut off by the table's overflow scroll. -->
  <script>
    (function() {
      var openPanel = null;

      function closeMenu() {
        if (!openPanel) return;
        openPanel.classList.add("hidden");
        openPanel.parentNode.querySelector("[data-action-menu-toggle]").setAttribute("aria-expanded", "false");
        openPanel = null;
      }

      function openMenu(toggle, panel) {
        panel.classList.remove("hidden");
        toggle.setAttribute("aria-expanded", "true");

        // Place below the button, aligned to its right edge; flip above if there is no room
        var r = toggle.getBoundingClientRect();
        var left = Math.max(8, r.right - panel.offsetWidth);
        var top = r.bottom + 4;
        if (top + panel.offsetHeight > window.innerHeight - 8) top = r.top - panel.offsetHeight - 4;
        panel.style.left = left + "px";
        panel.style.top = Math.max(8, top) + "px";
        openPanel = panel;
      }

      document.addEventListener("click", function(e) {
        var toggle = e.target.closest("[data-action-menu-toggle]");
        if (toggle) {
          var panel = toggle.parentNode.querySelector("[data-action-menu-panel]");
          var wasOpen = panel === openPanel;
          closeMenu();
          if (!wasOpen) openMenu(toggle, panel);
          return;
        }
        if (!e.target.closest("[data-action-menu-panel]")) closeMenu();
      });

      document.addEventListener("keydown", function(e) {
        if (e.key === "Escape") closeMenu();
      });

      // A fixed panel would drift away from its button on scroll/resize, so just close it
      window.addEventListener("scroll", closeMenu, true);
      window.addEventListener("resize", closeMenu);
    })();
  </script>

  @stack('scripts')
</body>

</html>
