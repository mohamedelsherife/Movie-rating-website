<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MovieController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $directorId = $request->director_id;
        $releaseYear = $request->release_year;
        $genreId = $request->genre_id;

        // Every filter is optional; the ones that are filled are combined with AND.
        $movies = Movie::with(['director', 'genres'])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%");
            })
            ->when($directorId, function ($query) use ($directorId) {
                $query->where('director_id', $directorId);
            })
            ->when($releaseYear, function ($query) use ($releaseYear) {
                $query->where('release_year', $releaseYear);
            })
            ->when($genreId, function ($query) use ($genreId) {
                $query->whereHas('genres', function ($q) use ($genreId) {
                    $q->where('genres.id', $genreId);
                });
            })
            ->orderBy('title')
            ->get();

        $directors = Director::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();
        $years = Movie::select('release_year')->distinct()
            ->orderByDesc('release_year')->pluck('release_year');

        return view('admin.movies.index', compact('movies', 'directors', 'genres', 'years'));
    }

    public function create()
    {
        $directors = Director::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('admin.movies.create', compact('directors', 'genres'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        $posterName = null;
        if ($request->hasFile('poster')) {
            $posterName = Str::uuid() . '.' . $request->file('poster')->extension();
            $request->file('poster')->storeAs('movies', $posterName, 'public');
        }

        $movie = Movie::create([
            'title'        => $request->title,
            'director_id'  => $request->director_id,
            'release_year' => $request->release_year,
            'description'  => $request->description,
            'poster'       => $posterName,
        ]);

        $movie->genres()->sync($request->genre_ids);

        return redirect()->route('admin.movies.index')->with('success', 'Movie created successfully');
    }

    public function edit($id)
    {
        $movie = Movie::with('genres')->findOrFail($id);
        $directors = Director::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('admin.movies.edit', compact('movie', 'directors', 'genres'));
    }

    public function update(Request $request, $id)
    {
        $movie = Movie::findOrFail($id);

        $request->validate($this->rules());

        $data = [
            'title'        => $request->title,
            'director_id'  => $request->director_id,
            'release_year' => $request->release_year,
            'description'  => $request->description,
        ];

        if ($request->hasFile('poster')) {
            if ($movie->poster) {
                Storage::disk('public')->delete('movies/' . $movie->poster);
            }
            $posterName = Str::uuid() . '.' . $request->file('poster')->extension();
            $request->file('poster')->storeAs('movies', $posterName, 'public');
            $data['poster'] = $posterName;
        }

        $movie->update($data);
        $movie->genres()->sync($request->genre_ids);

        return redirect()->route('admin.movies.index')->with('success', 'Movie updated successfully');
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);

        if ($movie->poster) {
            Storage::disk('public')->delete('movies/' . $movie->poster);
        }

        // Rows in movie_genres are removed automatically (cascadeOnDelete).
        $movie->delete();

        return redirect()->route('admin.movies.index')->with('success', 'Movie deleted successfully');
    }

    private function rules()
    {
        return [
            'title'        => 'required|string|max:255',
            'director_id'  => 'required|integer|exists:directors,id',
            'release_year' => 'required|integer|between:1888,' . (date('Y') + 5),
            'description'  => 'nullable|string|max:2000',
            'genre_ids'    => 'required|array|min:1',
            'genre_ids.*'  => 'integer|distinct|exists:genres,id',
            'poster'       => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }
}
