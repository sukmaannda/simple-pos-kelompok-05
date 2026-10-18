<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seeder untuk Admin
        User::firstOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name'     => 'Admin Kafe',
                'role'     => 'admin',
                'password' => Hash::make('password'), // Menggunakan Hash agar aman
            ]
        );

        // Seeder untuk Kasir
        User::firstOrCreate(
            ['email' => 'kasir@pos.test'],
            [
                'name'     => 'Kasir Kafe',
                'role'     => 'kasir',
                'password' => Hash::make('password'),
            ]
        );
    }
}
