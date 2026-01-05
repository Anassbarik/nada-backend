<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\EventContent;

class EventContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find Seafood4Africa event
        $event = Event::where('name', 'LIKE', '%Seafood%')->orWhere('slug', 'seafood4africa')->first();
        
        if (!$event) {
            $this->command->warn('Seafood4Africa event not found. Skipping event content seeding.');
            return;
        }

        // Conditions content
        EventContent::updateOrCreate(
            [
                'event_id' => $event->id,
                'page_type' => 'conditions',
            ],
            [
                'content' => "**CONDITIONS GÉNÉRALES**\n\n1. Annulation gratuite jusqu'à 30 jours avant l'événement\n2. 50% si moins de 30 jours\n3. Non remboursable < 7 jours",
                'sections' => null,
                'hero_image' => null,
            ]
        );

        // Informations Générales content
        EventContent::updateOrCreate(
            [
                'event_id' => $event->id,
                'page_type' => 'info',
            ],
            [
                'content' => "**INFORMATIONS GÉNÉRALES**\n\n**Dates:** 04-06 Février 2026\n**Lieu:** Dahlia Expo Center, Casablanca\n**Organisateur:** FENIP",
                'sections' => null,
                'hero_image' => null,
            ]
        );

        // FAQ content
        EventContent::updateOrCreate(
            [
                'event_id' => $event->id,
                'page_type' => 'faq',
            ],
            [
                'content' => "**FAQ**\n\n**Q: Puis-je annuler?**\nA: Oui, voir conditions...\n\n**Q: Hôtel inclus?**\nA: Oui, packages hébergement disponibles",
                'sections' => null,
                'hero_image' => null,
            ]
        );

        $this->command->info('Event content seeded successfully for ' . $event->name);
    }
}
