{{--
  Green (+) next to a dropdown's label: opens that master's Add page in a new tab,
  so the half-filled form on this page is not lost.
  $route : the Add page's route name, e.g. 'godowns.create'
  $title : tooltip, e.g. 'Add new godown'

  Use: @include('components.add-new', ['route' => 'godowns.create', 'title' => 'Add new godown'])

  The icon is written out as <svg>: "circle-plus" is not one of the icons the theme
  (assets/main-*.js) comes with, so <i data-lucide="circle-plus"> would stay empty.
--}}
<a href="{{ route($route) }}" target="_blank" title="{{ $title }}" aria-label="{{ $title }}" class="text-success-text">
  <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/><path d="M12 8v8"/></svg>
</a>
