@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <header class="page-head">
        <div>
            <p class="eyebrow">Overview</p>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-sub">A quick look at what's in the CineRate catalogue.</p>
        </div>
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus"></i> Add movie
        </a>
    </header>

    <section class="stats">
        <a href="{{ route('admin.movies.index') }}" class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-film"></i></span>
            <span class="stat-value">{{ $movieCount }}</span>
            <span class="stat-label">Movies</span>
        </a>
        <div class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-video"></i></span>
            <span class="stat-value">{{ $directorCount }}</span>
            <span class="stat-label">Directors</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-masks-theater"></i></span>
            <span class="stat-value">{{ $genreCount }}</span>
            <span class="stat-label">Genres</span>
        </div>
    </section>

    @if($topGenres->isNotEmpty())
        <section class="section">
            <h2 class="section-title">Popular genres</h2>
            <div class="genre-cloud">
                @foreach($topGenres as $genre)
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

        @if($latestMovies->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-film"></i>
                <h2>No movies yet.</h2>
                <a href="{{ route('admin.movies.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add movie</a>
            </div>
        @else
            <div class="movie-grid">
                @foreach($latestMovies as $movie)
                    @include('admin.movies._card', ['movie' => $movie])
                @endforeach
            </div>
        @endif
    </section>
@endsection
