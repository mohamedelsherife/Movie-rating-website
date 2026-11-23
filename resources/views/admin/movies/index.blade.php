@extends('layouts.app')

@section('title', 'Movies')

@section('content')
    @php
        $activeDirector = $directors->firstWhere('id', request('director_id'));
        $activeGenre = $genres->firstWhere('id', request('genre_id'));
        $hasFilters = request()->filled('search') || $activeDirector || $activeGenre || request()->filled('release_year');
    @endphp

    <header class="page-head">
        <div>
            <p class="eyebrow">Catalogue</p>
            <h1 class="page-title">Movies</h1>
            <p class="page-sub">Browse, search and curate every title on the platform.</p>
        </div>
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add movie
        </a>
    </header>

    <form action="{{ route('admin.movies.index') }}" method="GET" class="toolbar" role="search">
        <div class="search-field">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by title…" aria-label="Search by title">
        </div>

        <div class="select-field">
            <i class="fa-solid fa-video"></i>
            <select name="director_id" aria-label="Filter by director">
                <option value="">All directors</option>
                @foreach($directors as $director)
                    <option value="{{ $director->id }}" @selected(request('director_id') == $director->id)>{{ $director->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="select-field">
            <i class="fa-regular fa-calendar"></i>
            <select name="release_year" aria-label="Filter by release year">
                <option value="">All years</option>
                @foreach($years as $year)
                    <option value="{{ $year }}" @selected(request('release_year') == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </div>

        <div class="select-field">
            <i class="fa-solid fa-masks-theater"></i>
            <select name="genre_id" aria-label="Filter by genre">
                <option value="">All genres</option>
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}" @selected(request('genre_id') == $genre->id)>{{ $genre->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Apply</button>
    </form>

    <div class="results-bar">
        <span class="results-count">{{ $movies->count() }} {{ Str::plural('movie', $movies->count()) }}</span>

        @if($hasFilters)
            <div class="active-filters">
                @if(request()->filled('search'))
                    <span class="filter-chip"><i class="fa-solid fa-magnifying-glass"></i> “{{ request('search') }}”</span>
                @endif
                @if($activeDirector)
                    <span class="filter-chip"><i class="fa-solid fa-video"></i> {{ $activeDirector->name }}</span>
                @endif
                @if(request()->filled('release_year'))
                    <span class="filter-chip"><i class="fa-regular fa-calendar"></i> {{ request('release_year') }}</span>
                @endif
                @if($activeGenre)
                    <span class="filter-chip"><i class="fa-solid fa-masks-theater"></i> {{ $activeGenre->name }}</span>
                @endif
                <a href="{{ route('admin.movies.index') }}" class="clear-link">Clear all</a>
            </div>
        @endif
    </div>

    @if($movies->isEmpty())
        <div class="empty-state">
            <i class="fa-solid fa-film"></i>
            <h2>No movies found.</h2>
            @if($hasFilters)
                <p>Nothing matches these filters. Try removing one of them.</p>
                <a href="{{ route('admin.movies.index') }}" class="btn btn-ghost">Clear filters</a>
            @else
                <p>The catalogue is empty. Add the first title to get started.</p>
                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add movie</a>
            @endif
        </div>
    @else
        <div class="movie-grid">
            @foreach($movies as $movie)
                @include('admin.movies._card', ['movie' => $movie])
            @endforeach
        </div>
    @endif
@endsection
