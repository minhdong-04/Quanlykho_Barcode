<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        // Create initial admin user if not exists
        User::firstOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@example.com'),
        ], [
            'name' => env('ADMIN_NAME', 'Administrator'),
            'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            'role' => 'admin',
        ]);
    }
}
