@props([
    'icon',
    'value',
    'label',
    'href' => null,
])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class('stat-card') }}>
    <span class="stat-icon"><i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i></span>
    <span class="stat-value">{{ $value }}</span>
    <span class="stat-label">{{ $label }}</span>
</{{ $tag }}>
