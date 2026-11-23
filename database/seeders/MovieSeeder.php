<?php

namespace Database\Seeders;

use App\Models\Director;
use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Database\Seeder;

class MovieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $movies = [
            ['title' => 'Inception', 'director' => 'Christopher Nolan', 'release_year' => 2010, 'genres' => ['Action', 'Sci-Fi', 'Thriller']],
            ['title' => 'Interstellar', 'director' => 'Christopher Nolan', 'release_year' => 2014, 'genres' => ['Adventure', 'Drama', 'Sci-Fi']],
            ['title' => 'Oppenheimer', 'director' => 'Christopher Nolan', 'release_year' => 2023, 'genres' => ['Drama', 'History']],
            ['title' => 'Jurassic Park', 'director' => 'Steven Spielberg', 'release_year' => 1993, 'genres' => ['Adventure', 'Sci-Fi']],
            ['title' => 'Pulp Fiction', 'director' => 'Quentin Tarantino', 'release_year' => 1994, 'genres' => ['Crime', 'Drama']],
            ['title' => 'The Departed', 'director' => 'Martin Scorsese', 'release_year' => 2006, 'genres' => ['Crime', 'Thriller']],
            ['title' => 'Dune: Part Two', 'director' => 'Denis Villeneuve', 'release_year' => 2024, 'genres' => ['Action', 'Adventure', 'Sci-Fi']],
            ['title' => 'Barbie', 'director' => 'Greta Gerwig', 'release_year' => 2023, 'genres' => ['Comedy', 'Fantasy']],
            ['title' => 'Parasite', 'director' => 'Bong Joon-ho', 'release_year' => 2019, 'genres' => ['Drama', 'Thriller']],
            ['title' => 'Spirited Away', 'director' => 'Hayao Miyazaki', 'release_year' => 2001, 'genres' => ['Animation', 'Fantasy']],
        ];

        foreach ($movies as $data) {
            $movie = Movie::updateOrCreate(
                ['title' => $data['title']],
                [
                    'director_id'  => Director::where('name', $data['director'])->value('id'),
                    'release_year' => $data['release_year'],
                ]
            );

            $movie->genres()->sync(
                Genre::whereIn('name', $data['genres'])->pluck('id')
            );
        }
    }
}
