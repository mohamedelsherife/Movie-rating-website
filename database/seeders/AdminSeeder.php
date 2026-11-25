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
     * The default login is madaramy@gmail.com / 1234mada. To change it, set
     * ADMIN_EMAIL, ADMIN_PASSWORD (and optionally ADMIN_NAME) in .env and run:
     *     php artisan db:seed --class=AdminSeeder
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'madaramy@gmail.com');

        // There is a single admin account: drop any old one (e.g. the former "admin" login).
        Admin::where('email', '!=', $email)->delete();

        // updateOrCreate: running the seeder again never creates a duplicate admin.
        Admin::updateOrCreate(
            ['email' => $email],
            [
                'name'     => env('ADMIN_NAME', 'Administrator'),
                'password' => Hash::make(env('ADMIN_PASSWORD', '1234mada')),
            ]
        );
    }
}
