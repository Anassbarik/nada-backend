<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\EventContent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Increase memory limit for potentially large data processing
        ini_set('memory_limit', '512M');

        // Process EventContent records in chunks to avoid memory issues
        EventContent::chunk(100, function ($contents) {
            foreach ($contents as $content) {
                $sections = $content->sections;
                $changed = false;

                if (is_array($sections)) {
                    foreach ($sections as $sectionIndex => $section) {
                        // Check if section has old 'content' field and no 'points' field
                        if (isset($section['content']) && !isset($section['points'])) {
                            $contentText = $section['content'] ?? '';
                            $points = [];
                            
                            // Split by newlines and filter lines
                            $lines = explode("\n", $contentText);
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (!empty($line)) {
                                    // Remove dash prefixes (hyphen, en-dash, em-dash) if present and trim whitespace
                                    $point = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', $line);
                                    $point = trim($point);
                                    if (!empty($point)) {
                                        $points[] = $point;
                                    }
                                }
                            }
                            
                            // If no points were found, use the original content as a single point (without dash)
                            if (empty($points) && !empty($contentText)) {
                                $cleanedContent = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($contentText));
                                if (!empty($cleanedContent)) {
                                    $points[] = $cleanedContent;
                                }
                            }
                            
                            // Replace content with points
                            $sections[$sectionIndex]['points'] = $points;
                            unset($sections[$sectionIndex]['content']);
                            $changed = true;
                        } elseif (!isset($section['points'])) {
                            // Initialize empty points array if neither content nor points exist
                            $sections[$sectionIndex]['points'] = [];
                            $changed = true;
                        }
                    }
                }

                if ($changed) {
                    $content->update(['sections' => $sections]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is irreversible as we cannot reliably restore the original content format
        // from the points array (we don't know the original line breaks, formatting, etc.)
    }
};
