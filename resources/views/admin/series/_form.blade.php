{{-- Shared by create and edit. Expects $directors, $genres and (optionally) $series. --}}
@php
    $series = $series ?? null;
    $selectedGenres = old('genre_ids', $series ? $series->genres->pluck('id')->all() : []);
    $posterUrl = $series?->poster ? asset('storage/series/' . $series->poster) : null;
    $maxYear = date('Y') + 5;
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
            <x-form.input name="title" :value="$series?->title" placeholder="e.g. Breaking Bad" maxlength="255" required />
        </x-form.field>

        <x-form.field label="Director / Creator" for="director_name" hint="Search, or type a new name" :error="['director_id', 'director_name']">
            <x-combobox
                name="director_id"
                text-name="director_name"
                id="director_name"
                :options="$directors"
                :value="old('director_id', $series?->director_id)"
                :text-value="old('director_name', $series?->director?->name)"
                placeholder="Search or add a director…"
                icon="video"
                creatable
                create-label="Add new director"
                new-note="isn't in the catalogue yet — it will be added as a new director."
                :invalid="$errors->hasAny(['director_id', 'director_name'])"
                maxlength="255"
                required />
        </x-form.field>

        <div class="field-row">
            <x-form.field label="First aired" for="start_year">
                <x-form.input type="number" name="start_year" :value="$series?->start_year"
                              min="1928" max="{{ $maxYear }}" placeholder="{{ date('Y') }}" required />
            </x-form.field>

            <x-form.field label="Final year" for="end_year" hint="Leave empty if still running">
                <x-form.input type="number" name="end_year" :value="$series?->end_year"
                              min="1928" max="{{ $maxYear }}" placeholder="Ongoing" />
            </x-form.field>
        </div>

        <div class="field-row">
            <x-form.field label="Seasons" for="seasons">
                <x-form.input type="number" name="seasons" :value="$series?->seasons"
                              min="1" max="100" placeholder="e.g. 5" required />
            </x-form.field>

            <x-form.field label="Episodes" for="episodes" hint="Optional · total across all seasons">
                <x-form.input type="number" name="episodes" :value="$series?->episodes"
                              min="1" max="10000" placeholder="e.g. 62" />
            </x-form.field>
        </div>

        <x-form.field as="fieldset" label="Genres" hint="Select one or more, or add a new one"
                      :error="['genre_ids', 'genre_ids.*', 'new_genres', 'new_genres.*']">
            <x-genre-picker :genres="$genres" :selected="$selectedGenres" :new="old('new_genres', [])" />
        </x-form.field>

        <x-form.field label="Description" for="description" hint="Optional">
            <x-form.textarea name="description" :value="$series?->description" rows="5" maxlength="2000"
                             placeholder="A short synopsis of the series…" />
        </x-form.field>
    </div>
</div>
