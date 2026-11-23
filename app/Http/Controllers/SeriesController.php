<?php

namespace App\Http\Controllers;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeriesController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $directorId = $request->director_id;
        $startYear = $request->start_year;
        $genreId = $request->genre_id;
        $status = $request->status;

        // Every filter is optional; the ones that are filled are combined with AND.
        $series = Series::with(['director', 'genres'])
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'LIKE', "%{$search}%");
            })
            ->when($directorId, function ($query) use ($directorId) {
                $query->where('director_id', $directorId);
            })
            ->when($startYear, function ($query) use ($startYear) {
                $query->where('start_year', $startYear);
            })
            ->when($genreId, function ($query) use ($genreId) {
                $query->whereHas('genres', function ($q) use ($genreId) {
                    $q->where('genres.id', $genreId);
                });
            })
            ->when($status === 'ongoing', function ($query) {
                $query->whereNull('end_year');
            })
            ->when($status === 'ended', function ($query) {
                $query->whereNotNull('end_year');
            })
            ->orderBy('title')
            ->get();

        $directors = Director::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();
        $years = Series::select('start_year')->distinct()
            ->orderByDesc('start_year')->pluck('start_year');
        $statuses = self::statuses();

        return view('admin.series.index', compact('series', 'directors', 'genres', 'years', 'statuses'));
    }

    public function create()
    {
        $directors = Director::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('admin.series.create', compact('directors', 'genres'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules(), $this->messages());

        $posterName = null;
        if ($request->hasFile('poster')) {
            $posterName = Str::uuid() . '.' . $request->file('poster')->extension();
            $request->file('poster')->storeAs('series', $posterName, 'public');
        }

        DB::transaction(function () use ($request, $posterName) {
            $series = Series::create([
                'title'       => $request->title,
                'director_id' => $this->resolveDirectorId($request),
                'start_year'  => $request->start_year,
                'end_year'    => $request->end_year,
                'seasons'     => $request->seasons,
                'episodes'    => $request->episodes,
                'description' => $request->description,
                'poster'      => $posterName,
            ]);

            $series->genres()->sync($this->resolveGenreIds($request));
        });

        return redirect()->route('admin.series.index')->with('success', 'TV series created successfully');
    }

    public function edit($id)
    {
        $series = Series::with(['director', 'genres'])->findOrFail($id);
        $directors = Director::orderBy('name')->get();
        $genres = Genre::orderBy('name')->get();

        return view('admin.series.edit', compact('series', 'directors', 'genres'));
    }

    public function update(Request $request, $id)
    {
        $series = Series::findOrFail($id);

        $request->validate($this->rules(), $this->messages());

        $data = [
            'title'       => $request->title,
            'start_year'  => $request->start_year,
            'end_year'    => $request->end_year,
            'seasons'     => $request->seasons,
            'episodes'    => $request->episodes,
            'description' => $request->description,
        ];

        if ($request->hasFile('poster')) {
            if ($series->poster) {
                Storage::disk('public')->delete('series/' . $series->poster);
            }
            $posterName = Str::uuid() . '.' . $request->file('poster')->extension();
            $request->file('poster')->storeAs('series', $posterName, 'public');
            $data['poster'] = $posterName;
        }

        DB::transaction(function () use ($request, $series, $data) {
            $data['director_id'] = $this->resolveDirectorId($request);

            $series->update($data);
            $series->genres()->sync($this->resolveGenreIds($request));
        });

        return redirect()->route('admin.series.index')->with('success', 'TV series updated successfully');
    }

    public function destroy($id)
    {
        $series = Series::findOrFail($id);

        if ($series->poster) {
            Storage::disk('public')->delete('series/' . $series->poster);
        }

        // Rows in series_genres are removed automatically (cascadeOnDelete).
        $series->delete();

        return redirect()->route('admin.series.index')->with('success', 'TV series deleted successfully');
    }

    /** Options for the status filter. */
    public static function statuses()
    {
        return [
            ['id' => 'ongoing', 'name' => 'Ongoing'],
            ['id' => 'ended',   'name' => 'Ended'],
        ];
    }

    /**
     * An existing director is chosen with director_id; otherwise the typed
     * director_name is matched against existing directors or created.
     */
    private function resolveDirectorId(Request $request)
    {
        if ($request->filled('director_id')) {
            return (int) $request->director_id;
        }

        return Director::findOrCreateByName($request->director_name)->id;
    }

    /**
     * Combine the selected genre ids with any newly typed genre names
     * (new names are reused if they already exist).
     */
    private function resolveGenreIds(Request $request)
    {
        $ids = collect($request->genre_ids ?? [])->map(fn ($id) => (int) $id);

        foreach ($request->new_genres ?? [] as $name) {
            $ids->push(Genre::findOrCreateByName($name)->id);
        }

        return $ids->unique()->values()->all();
    }

    private function rules()
    {
        $maxYear = date('Y') + 5;

        return [
            'title'         => 'required|string|max:255',
            'director_id'   => 'nullable|integer|exists:directors,id',
            'director_name' => 'required_without:director_id|nullable|string|max:255',
            'start_year'    => 'required|integer|between:1928,' . $maxYear,
            'end_year'      => 'nullable|integer|between:1928,' . $maxYear . '|gte:start_year',
            'seasons'       => 'required|integer|between:1,100',
            'episodes'      => 'nullable|integer|between:1,10000|gte:seasons',
            'description'   => 'nullable|string|max:2000',
            'genre_ids'     => 'required_without:new_genres|array',
            'genre_ids.*'   => 'integer|distinct|exists:genres,id',
            'new_genres'    => 'nullable|array|max:10',
            'new_genres.*'  => 'required|string|max:50',
            'poster'        => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }

    private function messages()
    {
        return [
            'director_name.required_without' => 'Choose an existing director or type a new director name.',
            'genre_ids.required_without'     => 'Select at least one genre, or add a new one.',
            'new_genres.*.max'               => 'Genre names may not be longer than 50 characters.',
            'end_year.gte'                   => 'The final year cannot be before the first year.',
            'episodes.gte'                   => 'A series needs at least one episode per season.',
        ];
    }
}
