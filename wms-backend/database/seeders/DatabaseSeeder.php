<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'phanvominhdong1143@gmail.com',
                'password' => '123',
                'role' => 'admin',
                'token_name' => 'admin-token',
                'abilities' => ['admin'],
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@mail.com',
                'password' => '123',
                'role' => 'manager',
                'token_name' => 'manager-token',
                'abilities' => ['view-reports', 'stock-in', 'stock-out'],
            ],
            [
                'name' => 'Warehouse Staff',
                'email' => 'staff@mail.com',
                'password' => '123',
                'role' => 'warehouse_staff',
                'token_name' => 'warehouse-staff-token',
                'abilities' => ['stock-in', 'stock-out'],
            ],
        ];

        foreach ($users as $userData) {
            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                    'role' => $userData['role'],
                ],
            );

            $user->tokens()->delete();
            $token = $user->createToken($userData['token_name'], $userData['abilities'])->plainTextToken;

            if ($this->command) {
                $this->command->warn($userData['name'] . ' token: ' . $token);
            }
        }
    }
}
