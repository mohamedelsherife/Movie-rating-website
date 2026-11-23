<?php

namespace Database\Seeders;

use App\Models\Director;
use Illuminate\Database\Seeder;

class DirectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $directors = [
            'Christopher Nolan',
            'Steven Spielberg',
            'Quentin Tarantino',
            'Martin Scorsese',
            'Denis Villeneuve',
            'Greta Gerwig',
            'Bong Joon-ho',
            'Hayao Miyazaki',
            'Ridley Scott',
            'James Cameron',
        ];

        foreach ($directors as $name) {
            Director::updateOrCreate(['name' => $name]);
        }
    }
}
