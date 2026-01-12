<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EventContent;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all event contents
        $eventContents = EventContent::all();
        
        foreach ($eventContents as $content) {
            if (!$content->sections || !is_array($content->sections)) {
                continue;
            }
            
            $sections = $content->sections;
            $modified = false;
            
            // Process each section
            foreach ($sections as $sectionIndex => $section) {
                if (isset($section['points']) && is_array($section['points'])) {
                    // Clean each point by removing dash prefixes (hyphen, en-dash, em-dash)
                    $cleanedPoints = [];
                    foreach ($section['points'] as $point) {
                        if (is_string($point)) {
                            // Remove various dash types at the start: hyphen (-), en-dash (–), em-dash (—)
                            $cleaned = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($point));
                            if ($cleaned !== trim($point)) {
                                $modified = true;
                            }
                            $cleanedPoints[] = trim($cleaned);
                        } else {
                            $cleanedPoints[] = $point;
                        }
                    }
                    
                    // Update the section with cleaned points
                    $sections[$sectionIndex]['points'] = $cleanedPoints;
                }
            }
            
            // Save if modified
            if ($modified) {
                // Use DB::table to avoid model casting issues
                DB::table('event_contents')
                    ->where('id', $content->id)
                    ->update(['sections' => json_encode($sections)]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as we don't know which points originally had "-"
        // The frontend will handle adding "-" when displaying
    }
};
