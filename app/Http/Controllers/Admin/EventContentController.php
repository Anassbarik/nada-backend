<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationContent;
use Illuminate\Http\Request;

class EventContentController extends Controller
{
    /**
     * Display content management page for an event.
     */
    public function index(Accommodation $event)
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
    public function edit(Accommodation $event, $pageType)
    {
        // Only allow editing if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event content. Events created by super administrators can only be edited by super administrators.');
        }
        
        $validPageTypes = ['conditions', 'informations', 'faq'];
        
        if (!in_array($pageType, $validPageTypes)) {
            abort(404, 'Invalid page type.');
        }

        $content = AccommodationContent::where('accommodation_id', $event->id)
            ->where('page_type', $pageType)
            ->first();

        // Helper function to convert content to points structure
        $convertToPoints = function($sections) {
            if (!$sections) return [];
            
            foreach ($sections as $index => $section) {
                if (isset($section['content']) && !isset($section['points'])) {
                    $contentText = $section['content'] ?? '';
                    $points = [];
                    
                    $lines = explode("\n", $contentText);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (!empty($line)) {
                            $point = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', $line);
                            $point = trim($point);
                            if (!empty($point)) {
                                $points[] = $point;
                            }
                        }
                    }
                    
                    if (empty($points) && !empty($contentText)) {
                        $cleanedContent = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($contentText));
                        if (!empty($cleanedContent)) {
                            $points[] = $cleanedContent;
                        }
                    }
                    
                    $sections[$index]['points'] = $points;
                    unset($sections[$index]['content']);
                } elseif (!isset($section['points'])) {
                    $sections[$index]['points'] = [];
                }
            }
            
            return $sections;
        };

        // Get English and French sections, with fallback to sections
        if ($content) {
            $sectionsEn = $content->sections_en ?? $content->sections ?? [];
            $sectionsFr = $content->sections_fr ?? $content->sections ?? [];
            
            // Convert to points structure if needed
            $sectionsEn = $convertToPoints($sectionsEn);
            $sectionsFr = $convertToPoints($sectionsFr);
            
            // Set them back to the content object for the view
            $content->sections_en = $sectionsEn;
            $content->sections_fr = $sectionsFr;
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

        $existingContent = AccommodationContent::where('accommodation_id', $event->id)
            ->where('page_type', $pageType)
            ->first();

        // Check ownership if content exists
        if ($existingContent && !$existingContent->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this content.');
        }

        $validated = $request->validate([
            'sections_en' => 'required|array|min:1',
            'sections_en.*.title' => 'required|string|max:255',
            'sections_en.*.points' => 'required|array|min:1',
            'sections_en.*.points.*' => 'required|string',
            'sections_fr' => 'required|array|min:1',
            'sections_fr.*.title' => 'required|string|max:255',
            'sections_fr.*.points' => 'required|array|min:1',
            'sections_fr.*.points.*' => 'required|string',
        ]);

        // Process English sections
        $processedSectionsEn = [];
        foreach ($validated['sections_en'] as $section) {
            $points = array_map(function($point) {
                $cleaned = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($point));
                return trim($cleaned);
            }, $section['points']);
            
            $points = array_filter($points, function($point) {
                return !empty($point);
            });
            
            $points = array_values($points);
            
            $processedSectionsEn[] = [
                'title' => $section['title'],
                'points' => $points,
            ];
        }

        // Process French sections
        $processedSectionsFr = [];
        foreach ($validated['sections_fr'] as $section) {
            $points = array_map(function($point) {
                $cleaned = preg_replace('/^[-\x{2013}\x{2014}]\s*/u', '', trim($point));
                return trim($cleaned);
            }, $section['points']);
            
            $points = array_filter($points, function($point) {
                return !empty($point);
            });
            
            $points = array_values($points);
            
            $processedSectionsFr[] = [
                'title' => $section['title'],
                'points' => $points,
            ];
        }

        AccommodationContent::updateOrCreate(
            [
                'accommodation_id' => $event->id,
                'page_type' => $pageType,
            ],
            [
                'sections' => $processedSectionsEn, // Keep English as default/fallback
                'sections_en' => $processedSectionsEn,
                'sections_fr' => $processedSectionsFr,
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.events.content.edit', [$event, $pageType])
            ->with('success', 'Contenu sauvegardé avec succès!');
    }
}
