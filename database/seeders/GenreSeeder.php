<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $genres = [
            'Action',
            'Adventure',
            'Animation',
            'Comedy',
            'Crime',
            'Drama',
            'Fantasy',
            'History',
            'Horror',
            'Romance',
            'Sci-Fi',
            'Thriller',
        ];

        foreach ($genres as $name) {
            Genre::updateOrCreate(['name' => $name]);
        }
    }
}
