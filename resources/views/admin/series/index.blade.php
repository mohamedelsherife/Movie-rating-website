@extends('layouts.app')

@section('title', 'TV Series')

@section('content')
    @php
        $activeDirector = $directors->firstWhere('id', request('director_id'));
        $activeGenre = $genres->firstWhere('id', request('genre_id'));
        $activeStatus = collect($statuses)->firstWhere('id', request('status'));

        // Each active filter becomes a removable chip (the link drops only that filter).
        $chips = collect([
            ['param' => 'search',      'icon' => 'magnifying-glass', 'label' => request()->filled('search') ? '“' . request('search') . '”' : null],
            ['param' => 'director_id', 'icon' => 'video',            'label' => $activeDirector?->name],
            ['param' => 'genre_id',    'icon' => 'masks-theater',    'label' => $activeGenre?->name],
            ['param' => 'start_year',  'icon' => 'calendar',         'label' => request('start_year')],
            ['param' => 'status',      'icon' => 'signal',           'label' => $activeStatus['name'] ?? null],
        ])->filter(fn ($chip) => filled($chip['label']));
    @endphp

    <x-content-type-switch active="series" />

    <x-page-header eyebrow="Catalogue" title="TV Series" subtitle="Browse, search and curate every series on the platform.">
        <x-slot:actions>
            <x-button :href="route('admin.series.create')" icon="plus">Add series</x-button>
        </x-slot:actions>
    </x-page-header>

    <form action="{{ route('admin.series.index') }}" method="GET" class="toolbar toolbar-wide" role="search" data-loading>
        <x-search-input name="search" :value="request('search')" placeholder="Search series by title…"
                        class="toolbar-search" aria-label="Search by title" />

        <x-combobox name="director_id" :options="$directors" :value="request('director_id')"
                    placeholder="Any director" icon="video" autosubmit aria-label="Filter by director"
                    empty-text="No director with that name" />

        <x-combobox name="genre_id" :options="$genres" :value="request('genre_id')"
                    placeholder="Any genre" icon="masks-theater" autosubmit aria-label="Filter by genre"
                    empty-text="No matching genre" />

        <x-combobox name="start_year" :options="$years" :value="request('start_year')"
                    placeholder="First aired" icon="calendar" autosubmit inputmode="numeric"
                    aria-label="Filter by first aired year" empty-text="No series from that year" />

        <x-combobox name="status" :options="$statuses" :value="request('status')"
                    placeholder="Any status" icon="signal" autosubmit aria-label="Filter by status"
                    empty-text="No matching status" />

        <x-button icon="magnifying-glass" class="toolbar-submit">Search</x-button>
    </form>

    <div class="results-bar">
        <span class="results-count">
            <strong>{{ $series->count() }}</strong> {{ Str::plural('series', $series->count()) }}
            {{ $chips->isNotEmpty() ? 'match your filters' : 'in the catalogue' }}
        </span>

        @if ($chips->isNotEmpty())
            <div class="active-filters">
                @foreach ($chips as $chip)
                    <a href="{{ route('admin.series.index', request()->except($chip['param'])) }}" class="filter-chip"
                       title="Remove this filter">
                        <i class="fa-solid fa-{{ $chip['icon'] }}" aria-hidden="true"></i>
                        {{ $chip['label'] }}
                        <i class="fa-solid fa-xmark chip-x" aria-hidden="true"></i>
                    </a>
                @endforeach
                <a href="{{ route('admin.series.index') }}" class="clear-link"><i class="fa-solid fa-rotate-left"></i> Reset all</a>
            </div>
        @endif
    </div>

    <div class="results" data-results>
        @if ($series->isEmpty())
            @if ($chips->isNotEmpty())
                <x-empty-state icon="magnifying-glass" title="No series found.">
                    <p>Nothing matches this combination of filters. Try removing one of them.</p>
                    <x-button :href="route('admin.series.index')" variant="ghost" icon="rotate-left">Reset filters</x-button>
                </x-empty-state>
            @else
                <x-empty-state icon="tv" title="No series found.">
                    <p>There are no TV series yet. Add the first one to get started.</p>
                    <x-button :href="route('admin.series.create')" icon="plus">Add series</x-button>
                </x-empty-state>
            @endif
        @else
            <div class="movie-grid">
                @foreach ($series as $show)
                    <x-series-card :series="$show" />
                @endforeach
            </div>
        @endif
    </div>
@endsection
