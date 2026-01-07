<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        if (User::where('email', 'admin@example.com')->exists()) {
            $this->command->info('Super admin user already exists!');
            return;
        }

        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // Change this password after first login!
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super admin user created successfully!');
        $this->command->warn('Email: admin@example.com');
        $this->command->warn('Password: password');
        $this->command->warn('WARNING: Please change the password after first login!');
    }
}
