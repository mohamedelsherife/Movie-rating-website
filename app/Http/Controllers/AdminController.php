<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;

class AdminController extends Controller
{
    public function dashboard()
    {
        $movieCount = Movie::count();
        $directorCount = Director::count();
        $genreCount = Genre::count();

        $latestMovies = Movie::with(['director', 'genres'])
            ->latest()
            ->orderByDesc('id')
            ->take(4)
            ->get();

        $topGenres = Genre::withCount('movies')
            ->whereHas('movies')
            ->orderByDesc('movies_count')
            ->orderBy('name')
            ->take(8)
            ->get();

        return view('admin.dashboard', compact(
            'movieCount', 'directorCount', 'genreCount', 'latestMovies', 'topGenres'
        ));
    }
}
