{{-- Shared by create and edit. Expects $directors, $genres and (optionally) $movie. --}}
@php
    $movie = $movie ?? null;
    $selectedGenres = old('genre_ids', $movie ? $movie->genres->pluck('id')->all() : []);
    $posterUrl = $movie?->poster ? asset('storage/movies/' . $movie->poster) : null;
@endphp

@if ($errors->any())
    <x-alert type="error" :dismissible="false" class="form-alert">
        Please fix the highlighted {{ Str::plural('field', count($errors->keys())) }} and try again.
    </x-alert>
@endif

<div class="form-grid">
    <x-form.field label="Poster" for="poster" class="poster-field">
        <x-poster-upload name="poster" :url="$posterUrl" />
    </x-form.field>

    <div class="form-fields">
        <x-form.field label="Title" for="title">
            <x-form.input name="title" :value="$movie?->title" placeholder="e.g. Interstellar" maxlength="255" required />
        </x-form.field>

        <div class="field-row">
            <x-form.field label="Director" for="director_name" hint="Search, or type a new name" :error="['director_id', 'director_name']">
                <x-combobox
                    name="director_id"
                    text-name="director_name"
                    id="director_name"
                    :options="$directors"
                    :value="old('director_id', $movie?->director_id)"
                    :text-value="old('director_name', $movie?->director?->name)"
                    placeholder="Search or add a director…"
                    icon="video"
                    creatable
                    create-label="Add new director"
                    new-note="isn't in the catalogue yet — it will be added as a new director."
                    :invalid="$errors->hasAny(['director_id', 'director_name'])"
                    maxlength="255"
                    required />
            </x-form.field>

            <x-form.field label="Release year" for="release_year">
                <x-form.input type="number" name="release_year" :value="$movie?->release_year"
                              min="1888" max="{{ date('Y') + 5 }}" placeholder="{{ date('Y') }}" required />
            </x-form.field>
        </div>

        <x-form.field as="fieldset" label="Genres" hint="Select one or more, or add a new one"
                      :error="['genre_ids', 'genre_ids.*', 'new_genres', 'new_genres.*']">
            <x-genre-picker :genres="$genres" :selected="$selectedGenres" :new="old('new_genres', [])" />
        </x-form.field>

        <x-form.field label="Description" for="description" hint="Optional">
            <x-form.textarea name="description" :value="$movie?->description" rows="5" maxlength="2000"
                             placeholder="A short synopsis of the movie…" />
        </x-form.field>
    </div>
</div>
