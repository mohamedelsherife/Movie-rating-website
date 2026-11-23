<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /** Content type filter options for the dashboard. */
    private const TYPES = ['all' => 'All', 'movies' => 'Movies', 'series' => 'TV Series'];

    public function dashboard(Request $request)
    {
        $type = array_key_exists($request->type, self::TYPES) ? $request->type : 'all';
        $types = self::TYPES;

        $movieCount = Movie::count();
        $seriesCount = Series::count();
        $directorCount = Director::count();
        $genreCount = Genre::count();

        $latestMovies = $type === 'series' ? collect() : Movie::with(['director', 'genres'])
            ->latest()
            ->orderByDesc('id')
            ->take(4)
            ->get();

        $latestSeries = $type === 'movies' ? collect() : Series::with(['director', 'genres'])
            ->latest()
            ->orderByDesc('id')
            ->take(4)
            ->get();

        // Popular genres count titles of the selected content type.
        $topGenres = Genre::withCount(['movies', 'series'])->get()
            ->each(function ($genre) use ($type) {
                $genre->titles_count = match ($type) {
                    'movies' => $genre->movies_count,
                    'series' => $genre->series_count,
                    default  => $genre->movies_count + $genre->series_count,
                };
            })
            ->filter(fn ($genre) => $genre->titles_count > 0)
            ->sortBy([['titles_count', 'desc'], ['name', 'asc']])
            ->take(8)
            ->values();

        return view('admin.dashboard', compact(
            'type', 'types', 'movieCount', 'seriesCount', 'directorCount', 'genreCount',
            'latestMovies', 'latestSeries', 'topGenres'
        ));
    }
}
