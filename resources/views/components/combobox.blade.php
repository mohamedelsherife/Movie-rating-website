{{--
    Searchable select (combobox). Behaviour lives in public/js/admin.js.

    Pick from a list:
        <x-combobox name="director_id" :options="$directors" :value="request('director_id')" placeholder="Any director" />

    Pick OR create (the typed text is submitted as text-name when nothing existing is chosen):
        <x-combobox name="director_id" text-name="director_name" creatable … />

    options: models/arrays (read with value-key / label-key) or plain scalars (e.g. years).
    autosubmit: submit the surrounding form as soon as a value is chosen or cleared.
--}}
@props([
    'name',
    'options' => [],
    'value' => null,
    'valueKey' => 'id',
    'labelKey' => 'name',
    'placeholder' => 'Search…',
    'icon' => null,
    'creatable' => false,
    'textName' => null,
    'textValue' => null,
    'createLabel' => 'Create',
    'newNote' => 'will be created',
    'autosubmit' => false,
    'invalid' => false,
    'emptyText' => 'No matches found',
])

@php
    $items = collect($options)->map(fn ($option) => is_scalar($option)
        ? ['value' => (string) $option, 'label' => (string) $option]
        : ['value' => (string) data_get($option, $valueKey), 'label' => (string) data_get($option, $labelKey)]);

    $selected = filled($value) ? $items->firstWhere('value', (string) $value) : null;
    $text = $selected['label'] ?? $textValue ?? '';
    $isNew = $creatable && ! $selected && filled($text);

    $inputId = $attributes->get('id', 'combo-' . $name);
    $listId = $inputId . '-list';
@endphp

<div {{ $attributes->only('class')->class(['combo', 'is-invalid' => $invalid, 'is-new' => $isNew]) }}
     data-combobox
     @if ($creatable) data-creatable @endif
     @if ($autosubmit) data-autosubmit @endif>
    <div class="combo-control">
        @if ($icon)
            <i class="fa-solid fa-{{ $icon }} combo-icon" aria-hidden="true"></i>
        @endif

        <input type="text" id="{{ $inputId }}" class="combo-input" value="{{ $text }}" placeholder="{{ $placeholder }}"
               @if ($textName) name="{{ $textName }}" @endif
               role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="{{ $listId }}"
               autocomplete="off" spellcheck="false"
               {{ $attributes->except(['class', 'id']) }}>
        <input type="hidden" name="{{ $name }}" value="{{ $selected['value'] ?? '' }}" class="combo-value">

        <button type="button" class="combo-clear" aria-label="Clear selection" @if (blank($text)) hidden @endif>
            <i class="fa-solid fa-xmark" aria-hidden="true"></i>
        </button>
        <i class="fa-solid fa-chevron-down combo-caret" aria-hidden="true"></i>
    </div>

    <ul class="combo-list" id="{{ $listId }}" role="listbox" hidden>
        @foreach ($items as $item)
            <li class="combo-option" role="option" id="{{ $listId }}-{{ $loop->index }}"
                data-value="{{ $item['value'] }}" data-label="{{ $item['label'] }}"
                aria-selected="{{ ($selected['value'] ?? null) === $item['value'] ? 'true' : 'false' }}">
                <span class="combo-label">{{ $item['label'] }}</span>
                <i class="fa-solid fa-check combo-check" aria-hidden="true"></i>
            </li>
        @endforeach

        @if ($creatable)
            <li class="combo-create" role="option" id="{{ $listId }}-create" hidden>
                <i class="fa-solid fa-plus" aria-hidden="true"></i>
                <span>{{ $createLabel }} “<strong data-create-text></strong>”</span>
            </li>
        @endif

        <li class="combo-empty" role="presentation" hidden>
            <i class="fa-regular fa-face-meh" aria-hidden="true"></i> {{ $emptyText }}
        </li>
    </ul>

    @if ($creatable)
        <p class="combo-note" @unless ($isNew) hidden @endunless>
            <i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i>
            <span>“<strong data-note-text>{{ $isNew ? $text : '' }}</strong>” {{ $newNote }}</span>
        </p>
    @endif
</div>
