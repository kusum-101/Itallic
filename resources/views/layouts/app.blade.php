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

  <div data-app-shell class="flex min-h-screen min-w-0 flex-col">
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

  <!-- Forms with data-enter-next: Enter moves to the next field (like Tally) instead of saving the form.
       The form is saved only with its Save button. Enter in a textarea still adds a new line. -->
  <script>
    document.addEventListener("keydown", function(e) {
      if (e.key !== "Enter" || e.shiftKey || e.ctrlKey || e.altKey) return;

      var field = e.target;
      var form = field.closest && field.closest("form[data-enter-next]");
      if (!form || !field.matches("input, select")) return;

      e.preventDefault();

      // Visible, editable fields and buttons of the form, in page order
      var fields = Array.prototype.filter.call(
        form.querySelectorAll("input, select, textarea, button"),
        function(el) {
          return el.type !== "hidden" && !el.disabled && !el.readOnly && el.offsetParent !== null;
        }
      );

      var next = fields[fields.indexOf(field) + 1];
      if (next) next.focus();
    });
  </script>

  <!-- Keyboard shortcuts (Tally-like) and their panel -->
  @include('components.shortcuts')

  <!-- Page search: opened by the header's search box or Ctrl + K.
       It lists every page of the sidebar (so a new sidebar link is searchable by itself)
       plus the "Add ..." pages below. Type to filter, Up / Down to move, Enter to open. -->
  <div id="page-search" class="fixed inset-0 z-[60] hidden items-start justify-center bg-black/40 p-4 pt-[12vh] backdrop-blur-sm">
    <div role="dialog" aria-modal="true" aria-label="Search pages" class="w-full max-w-lg overflow-hidden rounded-xl border border-border bg-popover text-popover-foreground shadow-2xl">
      <div class="flex items-center gap-2 border-b border-border px-3">
        <i data-lucide="search" class="size-4 shrink-0 text-muted-foreground"></i>
        <input id="page-search-input" type="text" autocomplete="off" placeholder="Search pages…" class="h-11 w-full bg-transparent text-sm outline-none placeholder:text-muted-foreground">
        <kbd class="rounded border border-border px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground">ESC</kbd>
      </div>
      <div id="page-search-list" class="max-h-80 overflow-y-auto p-2"></div>
    </div>
  </div>

  <script>
    (function() {
      var box = document.getElementById("page-search");
      var input = document.getElementById("page-search-input");
      var list = document.getElementById("page-search-list");

      // Pages that have no sidebar link of their own
      var extraPages = [
        { label: "Add Group", href: "{{ route('groups.create') }}" },
        { label: "Add Ledger", href: "{{ route('ledgers.create') }}" },
        { label: "Add Stock Group", href: "{{ route('stock-groups.create') }}" },
        { label: "Add Stock Category", href: "{{ route('stock-categories.create') }}" },
        { label: "Add Item", href: "{{ route('items.create') }}" },
        { label: "Add Unit", href: "{{ route('units.create') }}" },
        { label: "Add Godown", href: "{{ route('godowns.create') }}" },
        @foreach(\App\Http\Controllers\Voucher\VoucherController::TYPES + \App\Http\Controllers\Voucher\StockVoucherController::TYPES as $searchType => $searchVoucher)
        { label: "Add {{ $searchVoucher['label'] }}", href: "{{ route($searchType) }}" },
        @endforeach
      ];

      var pages = null;   // every page, filled the first time the search opens
      var shown = [];     // the pages that match what is typed
      var active = 0;     // which of them is highlighted
      var lastFocus = null;

      function collectPages() {
        pages = [];

        // Sidebar links. The heading is the menu the link sits under, e.g. "Account Vouchers"
        document.querySelectorAll("[data-nav] a.side-link[href]").forEach(function(link) {
          var menu = link.closest("ul");
          var toggle = menu && menu.previousElementSibling;

          pages.push({
            label: link.textContent.trim(),
            href: link.href,
            group: toggle && toggle.matches("[data-group-toggle]") ? toggle.textContent.trim() : "Pages",
            icon: link.querySelector("svg")
          });
        });

        extraPages.forEach(function(page) {
          pages.push({ label: page.label, href: page.href, group: "Add New", icon: null });
        });
      }

      function render() {
        list.innerHTML = "";

        if (!shown.length) {
          var empty = document.createElement("p");
          empty.className = "px-3 py-6 text-center text-sm text-muted-foreground";
          empty.textContent = "No pages found";
          list.appendChild(empty);
          return;
        }

        var group = null;

        shown.forEach(function(page, index) {
          // A small heading each time the menu changes
          if (page.group !== group) {
            group = page.group;
            var heading = document.createElement("p");
            heading.className = "px-2 pb-1 pt-2 text-xs font-medium text-muted-foreground";
            heading.textContent = group;
            list.appendChild(heading);
          }

          var item = document.createElement("a");
          item.href = page.href;
          item.dataset.index = index;
          item.className = "flex w-full items-center gap-3 rounded-lg px-3 py-2 text-start text-sm";

          // Same icon as in the sidebar (a copy of it); "Add ..." pages get a plus
          if (page.icon) {
            var icon = page.icon.cloneNode(true);
            icon.setAttribute("class", "size-4 shrink-0 text-muted-foreground");
            item.appendChild(icon);
          } else {
            item.insertAdjacentHTML("beforeend", '<svg xmlns="http://www.w3.org/2000/svg" class="size-4 shrink-0 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>');
          }

          item.appendChild(document.createTextNode(page.label));
          item.addEventListener("mousemove", function() {
            if (active !== index) {
              active = index;
              highlight();
            }
          });
          list.appendChild(item);
        });

        highlight();
      }

      function highlight() {
        list.querySelectorAll("a").forEach(function(item) {
          var on = Number(item.dataset.index) === active;
          item.classList.toggle("bg-accent", on);
          item.classList.toggle("text-accent-foreground", on);
          if (on) item.scrollIntoView({ block: "nearest" });
        });
      }

      // Every typed word must be in the page name or its menu name: "sales reg" finds Sales Register
      function filter() {
        var words = input.value.toLowerCase().split(/\s+/).filter(Boolean);

        shown = pages.filter(function(page) {
          var text = (page.label + " " + page.group).toLowerCase();
          return words.every(function(word) { return text.indexOf(word) !== -1; });
        });

        active = 0;
        render();
      }

      function isOpen() {
        return !box.classList.contains("hidden");
      }

      function open() {
        if (!pages) collectPages();
        lastFocus = document.activeElement;
        box.classList.remove("hidden");
        box.classList.add("flex");
        input.value = "";
        filter();
        input.focus();
      }

      function close() {
        box.classList.add("hidden");
        box.classList.remove("flex");
        if (lastFocus && lastFocus.focus) lastFocus.focus();
      }

      input.addEventListener("input", filter);

      // These two listeners run in the capture phase (the "true" at the end) and stop the event,
      // so the theme's own demo search (assets/main-*.js, a fixed list of pages this app
      // doesn't have) never opens.
      document.addEventListener("click", function(e) {
        if (e.target.closest("[data-cmdk-open]")) {
          e.stopPropagation();
          open();
          return;
        }
        // A click on the dark area outside the box closes it
        if (e.target === box) close();
      }, true);

      document.addEventListener("keydown", function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === "k") {
          e.preventDefault();
          e.stopPropagation();
          isOpen() ? close() : open();
          return;
        }

        if (!isOpen()) return;

        if (e.key === "Escape") {
          e.preventDefault();
          e.stopPropagation();
          close();
        } else if (e.key === "ArrowDown") {
          e.preventDefault();
          active = Math.min(active + 1, shown.length - 1);
          highlight();
        } else if (e.key === "ArrowUp") {
          e.preventDefault();
          active = Math.max(active - 1, 0);
          highlight();
        } else if (e.key === "Enter") {
          e.preventDefault();
          e.stopPropagation();
          if (shown[active]) window.location.href = shown[active].href;
        }
      }, true);
    })();
  </script>

  <!-- Draft (forms with data-keep-draft): whatever is typed in the form is kept in this tab
       (sessionStorage), so it is still there after the page is reloaded (Ctrl + R / F5).
       Opening the page any other way (sidebar link, Cancel and back, new tab) starts fresh.
       Saving the form throws the draft away.

       Rows added with a "+ Add" button (Opening Stock, Shipping Address) come back too:
         <tbody data-draft-list="openings" data-draft-template="opening-template">  -> the list, its row <template>
         <tr data-draft-row>                                                        -> one row of it
       Their fields are named openings[3][quantity], so the 3 tells which row a value belongs to.

       This runs before the page's own scripts (@stack('scripts') below), so those scripts
       start from the restored values, as if the page had come with them. -->
  <script>
    (function() {
      var forms = document.querySelectorAll("form[data-keep-draft]");
      if (!forms.length) return;

      // Fields that can't or mustn't be kept: files can't be put back into a file input
      function keep(el) {
        return el.name && el.name !== "_token" && el.name !== "_method" && el.type !== "file" && el.type !== "password";
      }

      // The row number in a name: openings[3][quantity] -> "3"
      function rowIndex(name, prefix) {
        var match = name.match(new RegExp("^" + prefix + "\\[(\\d+)\\]"));
        return match ? match[1] : null;
      }

      forms.forEach(function(form, n) {
        var key = "draft-{{ session('company_id') }}-" + location.pathname + location.search + "-" + n;
        var submitting = false;

        function save() {
          if (submitting) return;

          var fields = Array.prototype.filter.call(form.elements, keep).map(function(el) {
            return el.type === "checkbox" || el.type === "radio"
              ? { name: el.name, value: el.value, checked: el.checked }
              : { name: el.name, value: el.value };
          });

          try {
            sessionStorage.setItem(key, JSON.stringify(fields));
          } catch (e) {
            // Storage blocked (e.g. private window): the form still works, just no draft
          }
        }

        function restore(fields) {
          var names = fields.map(function(field) { return field.name; });

          // 1. Rows: remove the ones that were removed, add the ones that were added
          form.querySelectorAll("[data-draft-list]").forEach(function(list) {
            var prefix = list.dataset.draftList;
            var template = document.getElementById(list.dataset.draftTemplate);
            var wanted = [];

            names.forEach(function(name) {
              var index = rowIndex(name, prefix);
              if (index !== null && wanted.indexOf(index) === -1) wanted.push(index);
            });

            var present = [];
            list.querySelectorAll("[data-draft-row]").forEach(function(row) {
              var field = row.querySelector("[name]");
              var index = field ? rowIndex(field.name, prefix) : null;
              if (wanted.indexOf(index) === -1) {
                row.remove();
              } else {
                present.push(index);
              }
            });

            wanted.forEach(function(index) {
              if (present.indexOf(index) === -1) {
                list.insertAdjacentHTML("beforeend", template.innerHTML.replace(/__INDEX__/g, index));
              }
            });
          });

          // 2. Values
          fields.forEach(function(field) {
            form.querySelectorAll('[name="' + field.name + '"]').forEach(function(el) {
              if (el.type === "checkbox" || el.type === "radio") {
                if (el.value === field.value) el.checked = field.checked;
                return;
              }

              el.value = field.value;

              // A dropdown whose options come later (e.g. District after the State's list loads):
              // data-value is the one its script selects once they are there
              if (el.tagName === "SELECT" && el.value !== field.value) {
                el.dataset.value = field.value;
              }
            });
          });
        }

        // Only a reload brings the draft back; any other visit throws it away
        var navigation = performance.getEntriesByType("navigation")[0];
        var isReload = navigation && navigation.type === "reload";

        try {
          if (!isReload) {
            sessionStorage.removeItem(key);
          }
          // When the form comes back from the server with an error, its own values win
          else if ({{ session()->hasOldInput() ? 'false' : 'true' }}) {
            var fields = JSON.parse(sessionStorage.getItem(key));
            if (fields) restore(fields);
          }
        } catch (e) {}

        form.addEventListener("input", save);
        form.addEventListener("change", save);
        // Rows removed with a button, and anything else: save once more as the page goes away
        window.addEventListener("pagehide", save);

        // Saved: no draft any more
        form.addEventListener("submit", function() {
          submitting = true;
          try {
            sessionStorage.removeItem(key);
          } catch (e) {}
        });
      });
    })();
  </script>

  @stack('scripts')
</body>

</html>
