<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventContent;
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
    public function show(Event $event, $type)
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
        $content = EventContent::where('event_id', $event->id)
            ->where('page_type', $pageType)
            ->first();

        if (!$content) {
            return response()->json([
                'success' => false,
                'message' => 'Content not found.',
            ], 404);
        }

        // Format response - use content field if available, otherwise fall back to sections
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
                'content' => $content->content ?? ($content->sections ? json_encode($content->sections) : ''),
                'hero_image' => $content->hero_image_url,
                'hero_image_path' => $content->hero_image,
                'sections' => $content->sections ?? [],
            ],
        ]);
    }
}

