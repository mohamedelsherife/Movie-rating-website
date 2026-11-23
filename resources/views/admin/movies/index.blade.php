@extends('layouts.app')

@section('title', 'Movies')

@section('content')
    @php
        $activeDirector = $directors->firstWhere('id', request('director_id'));
        $activeGenre = $genres->firstWhere('id', request('genre_id'));

        // Each active filter becomes a removable chip (the link drops only that filter).
        $chips = collect([
            ['param' => 'search',       'icon' => 'magnifying-glass', 'label' => request()->filled('search') ? '“' . request('search') . '”' : null],
            ['param' => 'director_id',  'icon' => 'video',            'label' => $activeDirector?->name],
            ['param' => 'genre_id',     'icon' => 'masks-theater',    'label' => $activeGenre?->name],
            ['param' => 'release_year', 'icon' => 'calendar',         'label' => request('release_year')],
        ])->filter(fn ($chip) => filled($chip['label']));
    @endphp

    <x-content-type-switch active="movies" />

    <x-page-header eyebrow="Catalogue" title="Movies" subtitle="Browse, search and curate every title on the platform.">
        <x-slot:actions>
            <x-button :href="route('admin.movies.create')" icon="plus">Add movie</x-button>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('admin.movies.index') }}" method="GET" class="toolbar" role="search" data-loading>
        <x-search-input name="search" :value="request('search')" placeholder="Search movies by title…"
                        class="toolbar-search" aria-label="Search by title" />

        <x-combobox name="director_id" :options="$directors" :value="request('director_id')"
                    placeholder="Any director" icon="video" autosubmit aria-label="Filter by director"
                    empty-text="No director with that name" />

        <x-combobox name="genre_id" :options="$genres" :value="request('genre_id')"
                    placeholder="Any genre" icon="masks-theater" autosubmit aria-label="Filter by genre"
                    empty-text="No matching genre" />

        <x-combobox name="release_year" :options="$years" :value="request('release_year')"
                    placeholder="Any year" icon="calendar" autosubmit inputmode="numeric"
                    aria-label="Filter by release year" empty-text="No movies from that year" />

        <x-button icon="magnifying-glass" class="toolbar-submit">Search</x-button>
    </form>

    <div class="results-bar">
        <span class="results-count">
            <strong>{{ $movies->count() }}</strong> {{ Str::plural('movie', $movies->count()) }}
            {{ $chips->isNotEmpty() ? 'match your filters' : 'in the catalogue' }}
        </span>

        @if ($chips->isNotEmpty())
            <div class="active-filters">
                @foreach ($chips as $chip)
                    <a href="{{ route('admin.movies.index', request()->except($chip['param'])) }}" class="filter-chip"
                       title="Remove this filter">
                        <i class="fa-solid fa-{{ $chip['icon'] }}" aria-hidden="true"></i>
                        {{ $chip['label'] }}
                        <i class="fa-solid fa-xmark chip-x" aria-hidden="true"></i>
                    </a>
                @endforeach
                <a href="{{ route('admin.movies.index') }}" class="clear-link"><i class="fa-solid fa-rotate-left"></i> Reset all</a>
            </div>
        @endif
    </div>

    <div class="results" data-results>
        @if ($movies->isEmpty())
            @if ($chips->isNotEmpty())
                <x-empty-state icon="magnifying-glass" title="No movies found.">
                    <p>Nothing matches this combination of filters. Try removing one of them.</p>
                    <x-button :href="route('admin.movies.index')" variant="ghost" icon="rotate-left">Reset filters</x-button>
                </x-empty-state>
            @else
                <x-empty-state title="No movies found.">
                    <p>The catalogue is empty. Add the first title to get started.</p>
                    <x-button :href="route('admin.movies.create')" icon="plus">Add movie</x-button>
                </x-empty-state>
            @endif
        @else
            <div class="movie-grid">
                @foreach ($movies as $movie)
                    <x-movie-card :movie="$movie" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
