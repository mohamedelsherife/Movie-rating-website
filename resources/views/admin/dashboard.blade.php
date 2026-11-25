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

    @php
        // Query string helpers: the type filter and the genre filter are kept together.
        $typeParam = $type === 'all' ? [] : ['type' => $type];
        $genreParam = $activeGenre ? ['genre_id' => $activeGenre->id] : [];
    @endphp

    {{-- Content type filter: narrows the popular genres and recently added sections. --}}
    <nav class="segmented" aria-label="Filter by content type">
        @foreach ($types as $key => $label)
            <a href="{{ route('admin.dashboard', ($key === 'all' ? [] : ['type' => $key]) + $genreParam) }}"
               class="segment {{ $type === $key ? 'is-active' : '' }}"
               @if ($type === $key) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    @if ($topGenres->isNotEmpty())
        <section class="section">
            <h2 class="section-title">Popular genres</h2>
            <div class="genre-cloud">
                @foreach ($topGenres as $genre)
                    @php $isActive = $activeGenre?->id === $genre->id; @endphp
                    {{-- Clicking the active genre again clears the filter. --}}
                    <a href="{{ route('admin.dashboard', $typeParam + ($isActive ? [] : ['genre_id' => $genre->id])) }}"
                       class="genre-stat {{ $isActive ? 'is-active' : '' }}"
                       @if ($isActive) aria-current="true" @endif>
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
                <h2 class="section-title">{{ $activeGenre ? "{$activeGenre->name} movies ({$latestMovies->count()})" : 'Recently added movies' }}</h2>
                <a href="{{ route('admin.movies.index', $genreParam) }}" class="clear-link">View all <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            @if ($latestMovies->isEmpty() && $activeGenre)
                <x-empty-state title="No {{ $activeGenre->name }} movies yet." />
            @elseif ($latestMovies->isEmpty())
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
                <h2 class="section-title">{{ $activeGenre ? "{$activeGenre->name} TV series ({$latestSeries->count()})" : 'Recently added TV series' }}</h2>
                <a href="{{ route('admin.series.index', $genreParam) }}" class="clear-link">View all <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            @if ($latestSeries->isEmpty() && $activeGenre)
                <x-empty-state icon="tv" title="No {{ $activeGenre->name }} TV series yet." />
            @elseif ($latestSeries->isEmpty())
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
