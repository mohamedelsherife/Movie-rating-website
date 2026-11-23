{{-- Shared by create and edit. Expects $directors, $genres and (optionally) $movie. --}}
@php
    $movie = $movie ?? null;
    $selectedGenres = old('genre_ids', $movie ? $movie->genres->pluck('id')->all() : []);
    $posterUrl = $movie && $movie->poster ? asset('storage/movies/' . $movie->poster) : null;
@endphp

@if ($errors->any())
    <div class="form-alert" role="alert">
        <i class="fa-solid fa-triangle-exclamation"></i>
        <span>Please fix the highlighted fields and try again.</span>
    </div>
@endif

<div class="form-grid">
    <aside class="poster-upload">
        <span class="field-label">Poster</span>
        <label for="poster" class="poster-drop {{ $posterUrl ? 'has-image' : '' }}">
            <img id="poster-preview" @if($posterUrl) src="{{ $posterUrl }}" @else hidden @endif alt="Poster preview">
            <span class="poster-placeholder">
                <i class="fa-solid fa-image"></i>
                <strong>Upload poster</strong>
                <small>JPG, PNG or WEBP · max 10 MB</small>
            </span>
            <span class="poster-change"><i class="fa-solid fa-arrow-up-from-bracket"></i> Change</span>
        </label>
        <input type="file" name="poster" id="poster" accept=".jpg,.jpeg,.png,.webp" class="visually-hidden">
        @error('poster') <p class="field-error">{{ $message }}</p> @enderror
    </aside>

    <div class="form-fields">
        <div class="field">
            <label for="title" class="field-label">Title</label>
            <input type="text" name="title" id="title" class="input @error('title') is-invalid @enderror"
                   value="{{ old('title', $movie->title ?? '') }}" placeholder="e.g. Interstellar" required>
            @error('title') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="field-row">
            <div class="field">
                <label for="director_id" class="field-label">Director</label>
                <select name="director_id" id="director_id" class="input @error('director_id') is-invalid @enderror" required>
                    <option value="">Select a director</option>
                    @foreach($directors as $director)
                        <option value="{{ $director->id }}" @selected(old('director_id', $movie->director_id ?? '') == $director->id)>{{ $director->name }}</option>
                    @endforeach
                </select>
                @error('director_id') <p class="field-error">{{ $message }}</p> @enderror
            </div>

            <div class="field">
                <label for="release_year" class="field-label">Release year</label>
                <input type="number" name="release_year" id="release_year" class="input @error('release_year') is-invalid @enderror"
                       min="1888" max="{{ date('Y') + 5 }}" placeholder="{{ date('Y') }}"
                       value="{{ old('release_year', $movie->release_year ?? '') }}" required>
                @error('release_year') <p class="field-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <fieldset class="field">
            <legend class="field-label">Genres <span class="field-hint">Pick one or more</span></legend>
            <div class="genre-picker">
                @foreach($genres as $genre)
                    <label class="genre-toggle">
                        <input type="checkbox" name="genre_ids[]" value="{{ $genre->id }}"
                               @checked(in_array($genre->id, $selectedGenres))>
                        <span>{{ $genre->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('genre_ids') <p class="field-error">{{ $message }}</p> @enderror
            @error('genre_ids.*') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>

        <div class="field">
            <label for="description" class="field-label">Description <span class="field-hint">Optional</span></label>
            <textarea name="description" id="description" rows="5" class="input @error('description') is-invalid @enderror"
                      placeholder="A short synopsis of the movie…">{{ old('description', $movie->description ?? '') }}</textarea>
            @error('description') <p class="field-error">{{ $message }}</p> @enderror
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.getElementById('poster').addEventListener('change', function () {
            const file = this.files[0];
            if (!file) {
                return;
            }
            const preview = document.getElementById('poster-preview');
            preview.src = URL.createObjectURL(file);
            preview.hidden = false;
            preview.closest('.poster-drop').classList.add('has-image');
        });
    </script>
@endpush
