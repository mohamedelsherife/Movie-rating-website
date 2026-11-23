{{--
    Label + control + validation message.
    <x-form.field label="Title" for="title"> <x-form.input name="title" /> </x-form.field>
    :error accepts one key or several (the first message found is shown).
    as="fieldset" renders a <fieldset>/<legend> for groups such as checkboxes.
--}}
@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'error' => null,
    'as' => 'div',
])

@php
    $errorKeys = (array) ($error ?? $for);
    $message = collect($errorKeys)->filter()->map(fn ($key) => $errors->first($key))->filter()->first();
    $labelTag = $as === 'fieldset' ? 'legend' : 'label';
@endphp

<{{ $as }} {{ $attributes->class(['field', 'has-error' => $message]) }}>
    @if ($label)
        <{{ $labelTag }} @if ($labelTag === 'label' && $for) for="{{ $for }}" @endif class="field-label">
            {{ $label }}
            @if ($hint)<span class="field-hint">{{ $hint }}</span>@endif
        </{{ $labelTag }}>
    @endif

    {{ $slot }}

    @if ($message)
        <p class="field-error"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i> {{ $message }}</p>
    @endif
</{{ $as }}>
