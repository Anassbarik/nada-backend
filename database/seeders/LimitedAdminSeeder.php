<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LimitedAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure permissions exist
        $this->call(PermissionSeeder::class);

        // Create the limited admin user
        $admin = User::firstOrCreate(
            ['email' => 'limited@admin.com'],
            [
                'name' => 'Limited Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Get all permissions for events, hotels, and packages
        $permissions = Permission::whereIn('resource', ['events', 'hotels', 'packages'])->get();

        // Sync permissions (this will replace any existing permissions)
        $admin->permissions()->sync($permissions->pluck('id'));

        $this->command->info("Limited admin created successfully!");
        $this->command->info("Email: limited@admin.com");
        $this->command->info("Password: password123");
        $this->command->info("Permissions: Events, Hotels (including hotel images), and Packages");
    }
}
