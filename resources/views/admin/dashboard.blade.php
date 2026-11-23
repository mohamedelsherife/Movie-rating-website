@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header eyebrow="Overview" title="Dashboard" subtitle="A quick look at what's in the CineRate catalogue.">
        <x-slot:actions>
            <x-button :href="route('admin.movies.create')" icon="plus">Add movie</x-button>
        </x-slot:actions>
    </x-page-header>

    <section class="stats">
        <x-stat-card icon="film" :value="$movieCount" label="Movies" :href="route('admin.movies.index')" />
        <x-stat-card icon="video" :value="$directorCount" label="Directors" />
        <x-stat-card icon="masks-theater" :value="$genreCount" label="Genres" />
    </section>

    @if ($topGenres->isNotEmpty())
        <section class="section">
            <h2 class="section-title">Popular genres</h2>
            <div class="genre-cloud">
                @foreach ($topGenres as $genre)
                    <a href="{{ route('admin.movies.index', ['genre_id' => $genre->id]) }}" class="genre-stat">
                        <span>{{ $genre->name }}</span>
                        <strong>{{ $genre->movies_count }}</strong>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="section">
        <div class="section-head">
            <h2 class="section-title">Recently added</h2>
            <a href="{{ route('admin.movies.index') }}" class="clear-link">View all <i class="fa-solid fa-arrow-right"></i></a>
        </div>

        @if ($latestMovies->isEmpty())
            <x-empty-state title="No movies yet.">
                <p>Start building the catalogue.</p>
                <x-button :href="route('admin.movies.create')" icon="plus">Add movie</x-button>
            </x-empty-state>
        @else
            <div class="movie-grid">
                @foreach ($latestMovies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
