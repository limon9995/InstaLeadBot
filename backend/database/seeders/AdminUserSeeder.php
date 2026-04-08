<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@leadbot.local')],
            [
                'name'     => 'Admin',
                'email'    => env('ADMIN_EMAIL', 'admin@leadbot.local'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'Admin@12345')),
            ]
        );

        $this->command->info('Admin user created/verified.');
    }
}
