<?php

namespace Database\Seeders;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Series;
use Illuminate\Database\Seeder;

class SeriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $series = [
            ['title' => 'Breaking Bad', 'director' => 'Vince Gilligan', 'start_year' => 2008, 'end_year' => 2013, 'seasons' => 5, 'episodes' => 62, 'genres' => ['Crime', 'Drama', 'Thriller']],
            ['title' => 'Better Call Saul', 'director' => 'Vince Gilligan', 'start_year' => 2015, 'end_year' => 2022, 'seasons' => 6, 'episodes' => 63, 'genres' => ['Crime', 'Drama']],
            ['title' => 'Stranger Things', 'director' => 'The Duffer Brothers', 'start_year' => 2016, 'end_year' => 2025, 'seasons' => 5, 'episodes' => 42, 'genres' => ['Drama', 'Fantasy', 'Horror']],
            ['title' => 'The Crown', 'director' => 'Peter Morgan', 'start_year' => 2016, 'end_year' => 2023, 'seasons' => 6, 'episodes' => 60, 'genres' => ['Drama', 'History']],
            ['title' => 'Severance', 'director' => 'Ben Stiller', 'start_year' => 2022, 'end_year' => null, 'seasons' => 2, 'episodes' => 19, 'genres' => ['Drama', 'Sci-Fi', 'Thriller']],
            ['title' => 'The Bear', 'director' => 'Christopher Storer', 'start_year' => 2022, 'end_year' => null, 'seasons' => 4, 'episodes' => 38, 'genres' => ['Comedy', 'Drama']],
            ['title' => 'Arcane', 'director' => 'Christian Linke', 'start_year' => 2021, 'end_year' => 2024, 'seasons' => 2, 'episodes' => 18, 'genres' => ['Action', 'Animation', 'Fantasy']],
            ['title' => 'The Last of Us', 'director' => 'Craig Mazin', 'start_year' => 2023, 'end_year' => null, 'seasons' => 2, 'episodes' => 16, 'genres' => ['Action', 'Adventure', 'Drama']],
        ];

        foreach ($series as $data) {
            $show = Series::updateOrCreate(
                ['title' => $data['title']],
                [
                    'director_id' => Director::findOrCreateByName($data['director'])->id,
                    'start_year'  => $data['start_year'],
                    'end_year'    => $data['end_year'],
                    'seasons'     => $data['seasons'],
                    'episodes'    => $data['episodes'],
                ]
            );

            $show->genres()->sync(
                Genre::whereIn('name', $data['genres'])->pluck('id')
            );
        }
    }
}
