{{--
    Multi-select genre chips with inline "add new genre".
    Existing genres submit genre_ids[]; new ones submit new_genres[] (created on save, de-duplicated).
--}}
@props([
    'genres',
    'selected' => [],
    'new' => [],
])

@php
    $selected = array_map('intval', (array) $selected);
@endphp

<div {{ $attributes->class('genre-box') }} data-genre-picker>
    <div class="genre-picker" data-genre-list>
        @foreach ($genres as $genre)
            <label class="genre-toggle" data-name="{{ $genre->name }}">
                <input type="checkbox" name="genre_ids[]" value="{{ $genre->id }}" @checked(in_array($genre->id, $selected))>
                <span>{{ $genre->name }}</span>
            </label>
        @endforeach

        @foreach ((array) $new as $name)
            @if (filled($name))
                <label class="genre-toggle is-new" data-name="{{ $name }}">
                    <input type="checkbox" name="new_genres[]" value="{{ $name }}" checked>
                    <span>{{ $name }}</span>
                </label>
            @endif
        @endforeach
    </div>

    <div class="genre-add">
        <i class="fa-solid fa-plus genre-add-icon" aria-hidden="true"></i>
        <input type="text" class="genre-add-input" placeholder="Missing a genre? Type it and press Enter"
               maxlength="50" aria-label="New genre name" data-genre-new>
        <x-button type="button" variant="ghost" size="sm" data-genre-add>Add</x-button>
    </div>

    <template data-genre-template>
        <label class="genre-toggle is-new" data-name="">
            <input type="checkbox" name="new_genres[]" value="" checked>
            <span></span>
        </label>
    </template>
</div>
