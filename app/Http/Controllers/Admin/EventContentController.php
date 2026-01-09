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
            'sections.*.content' => 'required|string',
        ]);

        EventContent::updateOrCreate(
            [
                'event_id' => $event->id,
                'page_type' => $pageType,
            ],
            [
                'sections' => $validated['sections'],
                'created_by' => auth()->id(),
            ]
        );

        return redirect()
            ->route('admin.events.content.edit', [$event, $pageType])
            ->with('success', 'Contenu sauvegardé avec succès!');
    }
}
