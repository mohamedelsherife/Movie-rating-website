@props([
    'icon' => 'film',
    'title',
])

<div {{ $attributes->class('empty-state') }}>
    <div class="empty-art" aria-hidden="true">
        <span class="empty-reel"><i class="fa-solid fa-{{ $icon }}"></i></span>
    </div>
    <h2>{{ $title }}</h2>
    {{ $slot }}
</div>
