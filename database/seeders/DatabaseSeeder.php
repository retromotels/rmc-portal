<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // RMC head-office admin account.
        User::updateOrCreate(
            ['email' => 'admin@retromotel.co'],
            [
                'name'             => 'RMC Head Office',
                'password'         => Hash::make('change-me-now'),
                'role'             => 'admin',
                'details_complete' => true,
            ]
        );
    }
}
