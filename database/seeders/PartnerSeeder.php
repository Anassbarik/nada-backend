<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partners = [
            ['name' => 'Coybib', 'sort_order' => 1],
            ['name' => 'Sendrity', 'sort_order' => 2],
            ['name' => 'Hideaway', 'sort_order' => 3],
            ['name' => 'Earthy', 'sort_order' => 4],
            ['name' => 'Nook', 'sort_order' => 5],
            ['name' => 'Homely', 'sort_order' => 6],
        ];

        foreach ($partners as $partnerData) {
            Partner::create([
                'name' => $partnerData['name'],
                'logo_path' => 'partners/' . Str::slug($partnerData['name']) . '.png', // Placeholder path
                'url' => 'https://' . Str::slug($partnerData['name']) . '.com',
                'sort_order' => $partnerData['sort_order'],
                'active' => true,
            ]);
        }
    }
}
