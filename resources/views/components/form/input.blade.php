{{-- <x-form.input name="title" :value="$movie?->title" /> — repopulates from old() and flags errors --}}
@props([
    'name',
    'value' => null,
    'type' => 'text',
])

<input type="{{ $type }}" name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
       value="{{ old($name, $value) }}"
       {{ $attributes->except('id')->class(['input', 'is-invalid' => $errors->has($name)]) }}>
