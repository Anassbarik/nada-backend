<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationContent;
use Illuminate\Http\Request;

class EventContentController extends Controller
{
    /**
     * Map public route types to database page_type values
     */
    private function mapPageType($type)
    {
        $mapping = [
            'conditions' => 'conditions',
            'info' => 'informations', // Map 'info' to 'informations' in database
            'faq' => 'faq',
        ];

        return $mapping[$type] ?? $type;
    }

    /**
     * Display event content page.
     * Route: GET /api/events/{event:slug}/{type}
     * Types: conditions, info, faq
     */
    public function show(Accommodation $event, $type)
    {
        // Validate event is published
        if ($event->status !== 'published') {
            return response()->json([
                'success' => false,
                'message' => 'Event not found.',
            ], 404);
        }

        // Validate type
        $validTypes = ['conditions', 'info', 'faq'];
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid content type.',
            ], 404);
        }

        // Map route type to database page_type
        $pageType = $this->mapPageType($type);

        // Get content
        $content = AccommodationContent::where('accommodation_id', $event->id)
            ->where('page_type', $pageType)
            ->first();

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Content not found.',
            ], 404);
        }

        // Format sections with points structure
        $sections = $content->sections ?? [];
        
        // Ensure sections are properly formatted (each section has title and points)
        $formattedSections = [];
        foreach ($sections as $section) {
            // Handle legacy content field - convert to points if needed
            $points = $section['points'] ?? [];
            if (empty($points) && isset($section['content'])) {
                $contentText = $section['content'] ?? '';
                $points = [];
                
                // Split by newlines and filter lines
                $lines = explode("\n", $contentText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Remove dash prefixes (hyphen, en-dash, em-dash) if present
                        $point = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', $line);
                        $point = trim($point);
                        if (!empty($point)) {
                            $points[] = $point;
                        }
                    }
                }
                
                // If no points were found, use the original content as a single point
                if (empty($points) && !empty($contentText)) {
                    $cleanedContent = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($contentText));
                    if (!empty($cleanedContent)) {
                        $points[] = $cleanedContent;
                    }
                }
            }
            
            $formattedSections[] = [
                'title' => $section['title'] ?? '',
                'points' => $points,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'event' => [
                    'id' => $event->id,
                    'name' => $event->name,
                    'slug' => $event->slug,
                    'venue' => $event->venue,
                    'start_date' => $event->start_date?->format('Y-m-d'),
                    'end_date' => $event->end_date?->format('Y-m-d'),
                ],
                'type' => $type,
                'page_type' => $content->page_type,
                'sections' => $formattedSections,
            ],
        ]);
    }
}

