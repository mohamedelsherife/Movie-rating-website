{{--
    Button / link button.
    <x-button icon="plus">Save</x-button>
    <x-button variant="ghost" :href="route('...')">Cancel</x-button>
    Variants: primary, ghost, danger, danger-soft · Sizes: sm
--}}
@props([
    'variant' => 'primary',
    'size' => null,
    'icon' => null,
    'href' => null,
    'type' => 'submit',
])

@php
    $iconClass = $icon ? (str_contains($icon, ' ') ? $icon : 'fa-solid fa-' . $icon) : null;
    $classes = 'btn btn-' . $variant . ($size ? ' btn-' . $size : '');
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>
        @if ($iconClass)<i class="{{ $iconClass }}" aria-hidden="true"></i>@endif
        <span>{{ $slot }}</span>
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>
        @if ($iconClass)<i class="{{ $iconClass }}" aria-hidden="true"></i>@endif
        <span>{{ $slot }}</span>
    </button>
@endif
