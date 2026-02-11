<?php

namespace Database\Seeders;

use App\Models\VehicleClass;
use Illuminate\Database\Seeder;

class VehicleClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classes = [
            'Classe Standard',
            'Classe Affaires',
            'Van/SUV Affaires',
        ];

        foreach ($classes as $name) {
            VehicleClass::firstOrCreate(
                ['name' => $name],
                ['created_by' => 1] // Assuming first user is super admin
            );
        }
    }
}
