<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventContent;
use Illuminate\Http\Request;

class EventContentController extends Controller
{
    /**
     * Display content management page for an event.
     */
    public function index(Event $event)
    {
        // All admins can view content pages (read-only)
        if (!$event->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this event.');
        }
        
        $event->load('contents');
        $contents = $event->contents->keyBy('page_type');
        return view('admin.events.content.index', compact('event', 'contents'));
    }

    /**
     * Show the editor for a specific content page.
     */
    public function edit(Event $event, $pageType)
    {
        // Only allow editing if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event content. Events created by super administrators can only be edited by super administrators.');
        }
        
        $validPageTypes = ['conditions', 'informations', 'faq'];
        
        if (!in_array($pageType, $validPageTypes)) {
            abort(404, 'Invalid page type.');
        }

        $content = EventContent::where('event_id', $event->id)
            ->where('page_type', $pageType)
            ->first();

        // Convert existing content to points structure if needed
        if ($content && isset($content->sections)) {
            // Get sections as array to avoid indirect modification error
            $sections = $content->sections ?? [];
            
            foreach ($sections as $index => $section) {
                // If content exists and points don't exist, parse content into points
                if (isset($section['content']) && !isset($section['points'])) {
                    $contentText = $section['content'] ?? '';
                    $points = [];
                    
                    // Split by newlines and filter lines starting with dashes
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
                    $sections[$index]['points'] = $points;
                    unset($sections[$index]['content']);
                } elseif (!isset($section['points'])) {
                    // Initialize empty points array if neither content nor points exist
                    $sections[$index]['points'] = [];
                }
            }
            
            // Set the modified sections back to the content object
            $content->sections = $sections;
        }

        $pageNames = [
            'conditions' => 'Conditions de Réservation',
            'informations' => 'Informations Générales',
            'faq' => 'FAQ',
        ];

        return view('admin.events.content.editor', [
            'event' => $event,
            'pageType' => $pageType,
            'pageName' => $pageNames[$pageType],
            'content' => $content,
        ]);
    }

    /**
     * Update the content for a specific content page.
     */
    public function update(Request $request, Event $event, $pageType)
    {
        // Only allow updating if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event content. Events created by super administrators can only be edited by super administrators.');
        }
        
        $validPageTypes = ['conditions', 'informations', 'faq'];
        
        if (!in_array($pageType, $validPageTypes)) {
            abort(404, 'Invalid page type.');
        }

        $existingContent = EventContent::where('event_id', $event->id)
            ->where('page_type', $pageType)
            ->first();

        // Check ownership if content exists
        if ($existingContent && !$existingContent->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this content.');
        }

        $validated = $request->validate([
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.points' => 'required|array|min:1',
            'sections.*.points.*' => 'required|string',
        ]);

        // Process sections to ensure points are properly formatted
        $processedSections = [];
        foreach ($validated['sections'] as $section) {
            // Filter out empty points and remove dash prefixes (hyphen, en-dash, em-dash)
            $points = array_map(function($point) {
                // Remove dash prefixes if present and trim
                $cleaned = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($point));
                return trim($cleaned);
            }, $section['points']);
            
            // Filter out empty points after cleaning
            $points = array_filter($points, function($point) {
                return !empty($point);
            });
            
            // Re-index array to ensure sequential keys
            $points = array_values($points);
            
            $processedSections[] = [
                'title' => $section['title'],
                'points' => $points,
            ];
        }

        EventContent::updateOrCreate(
            [
                'event_id' => $event->id,
                'page_type' => $pageType,
            ],
            [
                'sections' => $processedSections,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.events.content.edit', [$event, $pageType])
            ->with('success', 'Contenu sauvegardé avec succès!');
    }
}
