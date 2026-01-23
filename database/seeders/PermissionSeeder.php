<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Events
            ['resource' => 'events', 'action' => 'view', 'name' => 'View Events', 'description' => 'Can view events list'],
            ['resource' => 'events', 'action' => 'create', 'name' => 'Create Events', 'description' => 'Can create new events'],
            ['resource' => 'events', 'action' => 'edit', 'name' => 'Edit Events', 'description' => 'Can edit existing events'],
            ['resource' => 'events', 'action' => 'delete', 'name' => 'Delete Events', 'description' => 'Can delete events'],

            // Hotels
            ['resource' => 'hotels', 'action' => 'view', 'name' => 'View Hotels', 'description' => 'Can view hotels list'],
            ['resource' => 'hotels', 'action' => 'create', 'name' => 'Create Hotels', 'description' => 'Can create new hotels'],
            ['resource' => 'hotels', 'action' => 'edit', 'name' => 'Edit Hotels', 'description' => 'Can edit existing hotels'],
            ['resource' => 'hotels', 'action' => 'delete', 'name' => 'Delete Hotels', 'description' => 'Can delete hotels'],

            // Packages
            ['resource' => 'packages', 'action' => 'view', 'name' => 'View Packages', 'description' => 'Can view packages list'],
            ['resource' => 'packages', 'action' => 'create', 'name' => 'Create Packages', 'description' => 'Can create new packages'],
            ['resource' => 'packages', 'action' => 'edit', 'name' => 'Edit Packages', 'description' => 'Can edit existing packages'],
            ['resource' => 'packages', 'action' => 'delete', 'name' => 'Delete Packages', 'description' => 'Can delete packages'],

            // Partners
            ['resource' => 'partners', 'action' => 'view', 'name' => 'View Partners', 'description' => 'Can view partners list'],
            ['resource' => 'partners', 'action' => 'create', 'name' => 'Create Partners', 'description' => 'Can create new partners'],
            ['resource' => 'partners', 'action' => 'edit', 'name' => 'Edit Partners', 'description' => 'Can edit existing partners'],
            ['resource' => 'partners', 'action' => 'delete', 'name' => 'Delete Partners', 'description' => 'Can delete partners'],

            // Bookings
            ['resource' => 'bookings', 'action' => 'view', 'name' => 'View Bookings', 'description' => 'Can view bookings list'],
            ['resource' => 'bookings', 'action' => 'edit', 'name' => 'Edit Bookings', 'description' => 'Can edit booking status'],
            ['resource' => 'bookings', 'action' => 'delete', 'name' => 'Delete Bookings', 'description' => 'Can delete bookings'],

            // Invoices
            ['resource' => 'invoices', 'action' => 'view', 'name' => 'View Invoices', 'description' => 'Can view invoices list'],
            ['resource' => 'invoices', 'action' => 'create', 'name' => 'Create Invoices', 'description' => 'Can create new invoices'],
            ['resource' => 'invoices', 'action' => 'edit', 'name' => 'Edit Invoices', 'description' => 'Can edit existing invoices'],
            ['resource' => 'invoices', 'action' => 'delete', 'name' => 'Delete Invoices', 'description' => 'Can delete invoices'],

            // Admins
            ['resource' => 'admins', 'action' => 'view', 'name' => 'View Admins', 'description' => 'Can view admins list'],
            ['resource' => 'admins', 'action' => 'create', 'name' => 'Create Admins', 'description' => 'Can create new admins'],
            ['resource' => 'admins', 'action' => 'edit', 'name' => 'Edit Admins', 'description' => 'Can edit existing admins'],
            ['resource' => 'admins', 'action' => 'delete', 'name' => 'Delete Admins', 'description' => 'Can delete admins'],

            // Flights
            ['resource' => 'flights', 'action' => 'view', 'name' => 'View Flights', 'description' => 'Can view flights list'],
            ['resource' => 'flights', 'action' => 'create', 'name' => 'Create Flights', 'description' => 'Can create new flights'],
            ['resource' => 'flights', 'action' => 'edit', 'name' => 'Edit Flights', 'description' => 'Can edit existing flights'],
            ['resource' => 'flights', 'action' => 'delete', 'name' => 'Delete Flights', 'description' => 'Can delete flights'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['resource' => $permission['resource'], 'action' => $permission['action']],
                $permission
            );
        }
    }
}
