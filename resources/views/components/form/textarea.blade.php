@props([
    'name',
    'value' => null,
])

<textarea name="{{ $name }}" id="{{ $attributes->get('id', $name) }}"
          {{ $attributes->except('id')->class(['input', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>
