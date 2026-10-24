@php
  /*
   * Keyboard shortcuts, like Tally. One list does both jobs: the "Keyboard Shortcuts"
   * panel (keyboard button in the header, or Ctrl + /) shows it, and the script below runs it.
   *
   * A shortcut: 'label', 'keys' (pressed together, the last one is the key itself),
   * and either 'url' (opens that page) or 'action' (does something on the page, see actions below).
   * To add one, add a line here.
   */
  $shortcutGroups = [
      'Navigate to module' => [
          ['label' => 'Switch Company',        'keys' => ['F1'],                  'url' => route('select-company')],
          ['label' => 'Change Financial Year', 'keys' => ['F2'],                  'action' => 'year'],
          ['label' => 'Dashboard',             'keys' => ['Alt', 'Shift', 'H'],   'url' => route('dashboardUser')],
          ['label' => 'Company Setting',       'keys' => ['Alt', 'Shift', 'M'],   'url' => route('company-setting')],
          ['label' => 'Chart of Accounts',     'keys' => ['Alt', 'Shift', 'Y'],   'url' => route('chart-of-accounts')],
          ['label' => 'Group',                 'keys' => ['Alt', 'Shift', 'G'],   'url' => route('groups.index')],
          ['label' => 'Ledger (Account)',      'keys' => ['Alt', 'Shift', 'L'],   'url' => route('ledgers.index')],
          ['label' => 'Stock Group',           'keys' => ['Alt', 'Shift', 'X'],   'url' => route('stock-groups.index')],
          ['label' => 'Stock Category',        'keys' => ['Alt', 'Shift', 'N'],   'url' => route('stock-categories.index')],
          ['label' => 'Unit',                  'keys' => ['Alt', 'Shift', 'U'],   'url' => route('units.index')],
          ['label' => 'Godown',                'keys' => ['Alt', 'Shift', 'W'],   'url' => route('godowns.index')],
          ['label' => 'Item',                  'keys' => ['Alt', 'Shift', 'I'],   'url' => route('items.index')],
          ['label' => 'Sales',                 'keys' => ['Alt', 'Shift', 'S'],   'url' => route('sales')],
          ['label' => 'Purchase',              'keys' => ['Alt', 'Shift', 'B'],   'url' => route('purchase')],
          ['label' => 'Receipt',               'keys' => ['Alt', 'Shift', 'R'],   'url' => route('receipt')],
          ['label' => 'Payment',               'keys' => ['Alt', 'Shift', 'P'],   'url' => route('payment')],
          ['label' => 'Contra Entry',          'keys' => ['Alt', 'Ctrl', 'T'],    'url' => route('contra')],
          ['label' => 'Journal Voucher',       'keys' => ['Alt', 'Shift', 'V'],   'url' => route('journal')],
          ['label' => 'Material Inward',       'keys' => ['Alt', 'Shift', 'A'],   'url' => route('material-inward')],
          ['label' => 'Report (Day Book)',     'keys' => ['Alt', 'Shift', 'O'],   'url' => route('day-book')],
          ['label' => 'Settings',              'keys' => ['Alt', 'Ctrl', 'U'],    'url' => route('settings')],
          ['label' => 'Global Search',         'keys' => ['Ctrl', 'Shift', 'F'],  'action' => 'search'],
      ],
      'Entry level shortcut' => [
          ['label' => 'Create (on a list / register page)', 'keys' => ['Alt', 'N'], 'action' => 'create'],
          ['label' => 'Save',                               'keys' => ['Alt', 'S'], 'action' => 'save'],
          ['label' => 'Discard (Cancel / Exit)',            'keys' => ['Alt', 'D'], 'action' => 'discard'],
          ['label' => 'Open the List',                      'keys' => ['Alt', 'L'], 'action' => 'list'],
          ['label' => 'Add New Row',                        'keys' => ['Alt', 'A'], 'action' => 'add-row'],
          ['label' => 'Remove Row (the one you are in)',    'keys' => ['Alt', 'R'], 'action' => 'remove-row'],
          ['label' => 'Next field',                         'keys' => ['Enter']],
      ],
      'Other' => [
          ['label' => 'Search pages',       'keys' => ['Ctrl', 'K']],
          ['label' => 'Keyboard Shortcuts', 'keys' => ['Ctrl', '/'], 'action' => 'help'],
          ['label' => 'Close a window',     'keys' => ['Esc']],
      ],
  ];

  // The ones the script runs (Enter, Ctrl + K and Esc are handled elsewhere)
  $runnable = collect($shortcutGroups)->flatten(1)
      ->filter(fn ($shortcut) => isset($shortcut['url']) || isset($shortcut['action']))
      ->values();
@endphp

<!-- Keyboard Shortcuts panel: slides in from the right (kb-* classes are in public/css/custom.css) -->
<div id="shortcuts-panel" class="kb-overlay hidden" aria-hidden="true">
  <div role="dialog" aria-modal="true" aria-labelledby="shortcuts-title" class="kb-panel">
    <div class="flex items-center gap-2 border-b border-border px-4 py-3">
      <button type="button" data-shortcuts-close class="head-icon" aria-label="Close">
        <i data-lucide="chevron-right"></i>
      </button>
      <h2 id="shortcuts-title" class="text-base font-semibold text-foreground">Keyboard Shortcuts</h2>
    </div>

    <div class="flex-1 overflow-y-auto px-4 pb-6">
      @foreach ($shortcutGroups as $group => $shortcuts)
        <h3 class="kb-group">{{ $group }}</h3>
        <ul>
          @foreach ($shortcuts as $shortcut)
            <li class="kb-row">
              <span>{{ $shortcut['label'] }}</span>
              <span class="flex shrink-0 gap-1">
                @foreach ($shortcut['keys'] as $key)
                  <kbd class="kb-key">{{ strtoupper($key) }}</kbd>
                @endforeach
              </span>
            </li>
          @endforeach
        </ul>
      @endforeach
    </div>
  </div>
</div>

<script>
  (function () {
    var shortcuts = @json($runnable);
    var panel = document.getElementById('shortcuts-panel');

    function openPanel() {
      panel.classList.remove('hidden');
      panel.setAttribute('aria-hidden', 'false');
      panel.querySelector('[data-shortcuts-close]').focus();
    }

    function closePanel() {
      panel.classList.add('hidden');
      panel.setAttribute('aria-hidden', 'true');
    }

    document.addEventListener('click', function (e) {
      if (e.target.closest('[data-shortcuts-open]')) openPanel();
      // The close arrow, or a click on the dark area outside the panel
      if (e.target.closest('[data-shortcuts-close]') || e.target === panel) closePanel();
    });

    // The Add / Edit form of the page (the one Enter walks through), if there is one
    function pageForm() {
      return document.querySelector('main form[data-enter-next]');
    }

    // A link or button of the form by its text, e.g. "Cancel"
    function buttonWithText(root, texts) {
      return Array.prototype.find.call(root.querySelectorAll('a, button'), function (el) {
        return texts.indexOf(el.textContent.trim()) !== -1;
      });
    }

    // What each 'action' does
    var actions = {
      year: function () {
        var badge = document.querySelector('summary.head-year');
        if (badge) badge.click();
      },
      search: function () {
        var button = document.querySelector('[data-cmdk-open]');
        if (button) button.click();
      },
      help: openPanel,
      // The page's "Add" button (List pages, registers, Chart of Accounts)
      create: function () {
        var add = Array.prototype.find.call(document.querySelectorAll('main a'), function (a) {
          return /^Add( Group)?$/.test(a.textContent.trim());
        });
        if (add) add.click();
      },
      save: function () {
        var form = pageForm();
        // requestSubmit runs the form's own checks, like clicking its Save button
        if (form) form.requestSubmit();
      },
      discard: function () {
        var form = pageForm();
        var cancel = form && buttonWithText(form, ['Cancel', 'Exit']);
        if (cancel) cancel.click();
      },
      list: function () {
        var form = pageForm();
        var list = form && buttonWithText(form, ['List']);
        if (list) list.click();
      },
      'add-row': function () {
        var add = document.querySelector('main [data-add-line], main [data-add-opening], main [data-add-shipping]');
        if (add) add.click();
      },
      'remove-row': function () {
        var row = document.activeElement && document.activeElement.closest('[data-draft-row]');
        var remove = row && row.querySelector('[data-remove-line], [data-remove-opening], [data-remove-shipping]');
        if (remove) remove.click();
      }
    };

    // Does the pressed key match this shortcut's keys?
    function matches(e, keys) {
      var key = keys[keys.length - 1];
      var pressed = /^F\d+$/.test(key) ? e.key === key
        // With Alt / Shift held, e.key changes (Alt+Shift+H can give "Ó"), so the key's place (e.code) is used
        : key === '/' ? e.code === 'Slash'
        : e.code === 'Key' + key;

      return pressed
        && e.ctrlKey === (keys.indexOf('Ctrl') !== -1)
        && e.altKey === (keys.indexOf('Alt') !== -1)
        && e.shiftKey === (keys.indexOf('Shift') !== -1);
    }

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !panel.classList.contains('hidden')) {
        closePanel();
        return;
      }

      // A plain key (no Ctrl / Alt, not an F-key) is just typing
      if (!e.ctrlKey && !e.altKey && !/^F\d+$/.test(e.key)) return;

      var shortcut = shortcuts.find(function (s) { return matches(e, s.keys); });
      if (!shortcut) return;

      // Stop the browser's own use of the key (F1 = help, Alt+D = address bar, ...)
      e.preventDefault();

      if (shortcut.url) {
        window.location.href = shortcut.url;
      } else {
        actions[shortcut.action]();
      }
    });
  })();
</script>
