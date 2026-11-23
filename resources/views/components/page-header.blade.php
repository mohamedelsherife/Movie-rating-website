@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
])

<header {{ $attributes->class('page-head') }} data-enter>
    <div>
        @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)<p class="page-sub">{{ $subtitle }}</p>@endif
    </div>

    @isset($actions)
        <div class="page-actions">{{ $actions }}</div>
    @endisset
</header>
