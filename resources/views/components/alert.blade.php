{{--
    Notification / alert.
    <x-alert type="success" :timeout="5000">Saved!</x-alert>
    Types: success, error, info
--}}
@props([
    'type' => 'info',
    'dismissible' => true,
    'timeout' => null,
])

@php
    $icon = [
        'success' => 'circle-check',
        'error' => 'triangle-exclamation',
        'info' => 'circle-info',
    ][$type] ?? 'circle-info';
@endphp

<div {{ $attributes->class(['alert', 'alert-' . $type]) }}
     role="{{ $type === 'error' ? 'alert' : 'status' }}"
     @if ($timeout) data-autodismiss="{{ $timeout }}" @endif>
    <i class="fa-solid fa-{{ $icon }} alert-icon" aria-hidden="true"></i>
    <div class="alert-body">{{ $slot }}</div>
    @if ($dismissible)
        <button type="button" class="alert-close" data-dismiss aria-label="Dismiss">
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
    @endif
</div>
