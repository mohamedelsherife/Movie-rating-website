<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * The default login is admin / admin. To change it, set ADMIN_EMAIL,
     * ADMIN_PASSWORD (and optionally ADMIN_NAME) in .env and run:
     *     php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        // updateOrCreate: running the seeder again never creates a duplicate admin.
        Admin::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin')],
            [
                'name'     => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'admin')),
            ]
        );
    }
}
