{{--
    Native <dialog> modal. Buttons inside close it and set dialog.returnValue to their value.
    <x-modal id="confirm-dialog" icon="trash-can" title="Delete?">
        <p>…</p>
        <x-slot:actions>…</x-slot:actions>
    </x-modal>
--}}
@props([
    'id',
    'title' => null,
    'icon' => null,
    'tone' => 'danger',
])

<dialog id="{{ $id }}" {{ $attributes->class('modal') }} aria-labelledby="{{ $id }}-title">
    <form method="dialog" class="modal-box">
        @if ($icon)
            <div class="modal-icon modal-icon-{{ $tone }}"><i class="fa-solid fa-{{ $icon }}" aria-hidden="true"></i></div>
        @endif
        @if ($title)
            <h3 class="modal-title" id="{{ $id }}-title" data-modal-title>{{ $title }}</h3>
        @endif

        {{ $slot }}

        @isset($actions)
            <div class="modal-actions">{{ $actions }}</div>
        @endisset
    </form>
</dialog>
