<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'play@verlete.com'],
            [
                'name' => 'Verlete',
                'password' => Hash::make('P0nder1nk!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ],
        );
    }
}