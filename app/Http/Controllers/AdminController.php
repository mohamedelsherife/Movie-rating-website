<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /** Content type filter options for the dashboard. */
    private const TYPES = ['all' => 'All', 'movies' => 'Movies', 'series' => 'TV Series'];

    public function loginForm()
    {
        // A few real posters from the catalogue for the background wall.
        $posters = Movie::whereNotNull('poster')->inRandomOrder()->take(12)->pluck('poster');

        return view('admin.login', compact('posters'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|string|max:255',
            'password' => 'required|string',
        ]);

        // Checks the hashed password stored in the admins table.
        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'login' => 'Invalid email or password. Please try again.',
        ])->onlyInput('email', 'remember');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'You have been logged out.');
    }

    public function dashboard(Request $request)
    {
        $type = array_key_exists($request->type, self::TYPES) ? $request->type : 'all';
        $types = self::TYPES;

        $movieCount = Movie::count();
        $seriesCount = Series::count();
        $directorCount = Director::count();
        $genreCount = Genre::count();

        // Clicking a popular genre filters the dashboard to every movie and series in it.
        $activeGenre = $request->filled('genre_id') ? Genre::find($request->genre_id) : null;

        $titlesQuery = function ($model) use ($activeGenre) {
            $query = $model::with(['director', 'genres'])->latest()->orderByDesc('id');

            return $activeGenre
                ? $query->whereHas('genres', fn ($q) => $q->whereKey($activeGenre->id))->get()
                : $query->take(4)->get();
        };

        $latestMovies = $type === 'series' ? collect() : $titlesQuery(Movie::class);
        $latestSeries = $type === 'movies' ? collect() : $titlesQuery(Series::class);

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
            'latestMovies', 'latestSeries', 'topGenres', 'activeGenre'
        ));
    }
}
