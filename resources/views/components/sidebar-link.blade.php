{{-- One sidebar link. $link = ['label', 'icon', 'url', 'add' (optional: its "Add" page, shown as a + on the right)] --}}
<li class="side-row">
  <a href="{{ $link['url'] }}" class="side-link">
    <i data-lucide="{{ $link['icon'] }}"></i>
    <span>{{ $link['label'] }}</span>
  </a>
  @isset($link['add'])
    <a href="{{ $link['add'] }}" class="side-add" title="Add new" aria-label="Add new ({{ $link['label'] }})">
      <i data-lucide="plus"></i>
    </a>
  @endisset
</li>
