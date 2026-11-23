@props(['series'])

{{-- Same card design as <x-movie-card>, plus a TV badge, year range and season count. --}}
<article {{ $attributes->class('movie-card series-card') }}>
    <div class="movie-poster" style="--hue: {{ ($series->id * 71 + 200) % 360 }}">
        @if ($series->poster)
            <img src="{{ asset('storage/series/' . $series->poster) }}" alt="{{ $series->title }} poster" loading="lazy">
        @else
            <div class="poster-fallback" aria-hidden="true">
                <span class="poster-monogram">{{ Str::upper(Str::substr($series->title, 0, 1)) }}</span>
                <span class="poster-note"><i class="fa-solid fa-tv"></i> No poster</span>
            </div>
        @endif
        <span class="type-chip" title="TV series"><i class="fa-solid fa-tv" aria-hidden="true"></i><span class="visually-hidden">TV series</span></span>
        <span class="year-chip">{{ $series->yearsLabel() }}</span>
    </div>

    <div class="movie-body">
        <h3 class="movie-title" title="{{ $series->title }}">{{ $series->title }}</h3>
        <p class="movie-director"><i class="fa-solid fa-video" aria-hidden="true"></i> {{ $series->director->name }}</p>

        <p class="series-meta">
            <span>{{ $series->seasons }} {{ Str::plural('season', $series->seasons) }}</span>
            @if ($series->episodes)
                <span>{{ $series->episodes }} {{ Str::plural('episode', $series->episodes) }}</span>
            @endif
            <span class="status-dot {{ $series->isOngoing() ? 'is-ongoing' : 'is-ended' }}">
                {{ $series->isOngoing() ? 'Ongoing' : 'Ended' }}
            </span>
        </p>

        <ul class="genre-list" aria-label="Genres">
            @foreach ($series->genres as $genre)
                <li class="genre-pill">{{ $genre->name }}</li>
            @endforeach
        </ul>

        <div class="movie-actions">
            <x-button :href="route('admin.series.edit', $series->id)" variant="ghost" size="sm" icon="pen">Edit</x-button>

            <form action="{{ route('admin.series.destroy', $series->id) }}" method="POST"
                  data-confirm="“{{ $series->title }}” will be permanently removed from the catalogue."
                  data-confirm-title="Delete this series?">
                @csrf
                @method('DELETE')
                <x-button variant="danger-soft" size="sm" icon="trash-can">Delete</x-button>
            </form>
        </div>
    </div>
</article>
