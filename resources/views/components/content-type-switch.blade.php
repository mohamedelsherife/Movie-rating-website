{{--
    Content type filter: switches between the Movies and TV Series catalogues.
    Filters both catalogues share (search, director, genre) are carried across.

        <x-content-type-switch active="movies" />
--}}
@props(['active'])

@php
    $shared = request()->only(['search', 'director_id', 'genre_id']);

    $types = [
        'movies' => ['label' => 'Movies',    'icon' => 'film', 'route' => 'admin.movies.index', 'count' => \App\Models\Movie::count()],
        'series' => ['label' => 'TV Series', 'icon' => 'tv',   'route' => 'admin.series.index', 'count' => \App\Models\Series::count()],
    ];
@endphp

<nav {{ $attributes->class('type-switch') }} aria-label="Content type">
    @foreach ($types as $key => $type)
        <a href="{{ route($type['route'], $key === $active ? [] : $shared) }}"
           class="type-tab {{ $key === $active ? 'is-active' : '' }}"
           @if ($key === $active) aria-current="page" @endif>
            <i class="fa-solid fa-{{ $type['icon'] }}" aria-hidden="true"></i>
            <span>{{ $type['label'] }}</span>
            <span class="type-count">{{ $type['count'] }}</span>
        </a>
    @endforeach
</nav>
