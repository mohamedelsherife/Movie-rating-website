@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <x-page-header eyebrow="Overview" title="Dashboard" subtitle="A quick look at what's in the CineRate catalogue.">
        <x-slot:actions>
            <x-button :href="route('admin.series.create')" variant="ghost" icon="tv">Add series</x-button>
            <x-button :href="route('admin.movies.create')" icon="plus">Add movie</x-button>
        </x-slot:actions>
    </x-page-header>

    <section class="stats stats-4">
        <x-stat-card icon="film" :value="$movieCount" label="Movies" :href="route('admin.movies.index')" />
        <x-stat-card icon="tv" :value="$seriesCount" label="TV Series" :href="route('admin.series.index')" />
        <x-stat-card icon="video" :value="$directorCount" label="Directors" />
        <x-stat-card icon="masks-theater" :value="$genreCount" label="Genres" />
    </section>

    {{-- Content type filter: narrows the popular genres and recently added sections. --}}
    <nav class="segmented" aria-label="Filter by content type">
        @foreach ($types as $key => $label)
            <a href="{{ route('admin.dashboard', $key === 'all' ? [] : ['type' => $key]) }}"
               class="segment {{ $type === $key ? 'is-active' : '' }}"
               @if ($type === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($topGenres->isNotEmpty())
        <section class="section">
            <h2 class="section-title">Popular genres</h2>
            <div class="genre-cloud">
                @foreach ($topGenres as $genre)
                    <a href="{{ route($type === 'series' ? 'admin.series.index' : 'admin.movies.index', ['genre_id' => $genre->id]) }}" class="genre-stat">
                        <span>{{ $genre->name }}</span>
                        <strong>{{ $genre->titles_count }}</strong>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if ($type !== 'series')
        <section class="section">
            <div class="section-head">
                <h2 class="section-title">Recently added movies</h2>
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
    @endif

    @if ($type !== 'movies')
        <section class="section">
            <div class="section-head">
                <h2 class="section-title">Recently added TV series</h2>
                <a href="{{ route('admin.series.index') }}" class="clear-link">View all <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            @if ($latestSeries->isEmpty())
                <x-empty-state icon="tv" title="No TV series yet.">
                    <p>Add the first series to the catalogue.</p>
                    <x-button :href="route('admin.series.create')" icon="plus">Add series</x-button>
                </x-empty-state>
            @else
                <div class="movie-grid">
                    @foreach ($latestSeries as $show)
                        <x-series-card :series="$show" />
                    @endforeach
                </div>
            @endif
        </section>
    @endif
@endsection
