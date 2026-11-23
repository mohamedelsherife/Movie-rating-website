<article class="movie-card">
    <div class="movie-poster" style="--hue: {{ ($movie->id * 47) % 360 }}">
        @if($movie->poster)
            <img src="{{ asset('storage/movies/' . $movie->poster) }}" alt="{{ $movie->title }} poster" loading="lazy">
        @else
            <div class="poster-fallback" aria-hidden="true">
                <span class="poster-monogram">{{ Str::upper(Str::substr($movie->title, 0, 1)) }}</span>
                <span class="poster-note"><i class="fa-solid fa-film"></i> No poster</span>
            </div>
        @endif
        <span class="year-chip">{{ $movie->release_year }}</span>
    </div>

    <div class="movie-body">
        <h3 class="movie-title" title="{{ $movie->title }}">{{ $movie->title }}</h3>
        <p class="movie-director"><i class="fa-solid fa-video"></i> {{ $movie->director->name }}</p>

        <ul class="genre-list">
            @foreach($movie->genres as $genre)
                <li class="genre-pill">{{ $genre->name }}</li>
            @endforeach
        </ul>

        <div class="movie-actions">
            <a href="{{ route('admin.movies.edit', $movie->id) }}" class="btn btn-sm btn-ghost">
                <i class="fa-solid fa-pen"></i> Edit
            </a>
            <form action="{{ route('admin.movies.destroy', $movie->id) }}" method="POST"
                  data-confirm="&quot;{{ $movie->title }}&quot; will be permanently removed from the catalogue.">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger-soft">
                    <i class="fa-solid fa-trash-can"></i> Delete
                </button>
            </form>
        </div>
    </div>
</article>
