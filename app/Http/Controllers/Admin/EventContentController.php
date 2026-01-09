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
        $event->load('contents');
        $contents = $event->contents->keyBy('page_type');
        return view('admin.events.content.index', compact('event', 'contents'));
    }

    /**
     * Show the editor for a specific content page.
     */
    public function edit(Event $event, $pageType)
    {
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
        $validPageTypes = ['conditions', 'informations', 'faq'];
        
        if (!in_array($pageType, $validPageTypes)) {
            abort(404, 'Invalid page type.');
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
            ]
        );

        return redirect()
            ->route('admin.events.content.edit', [$event, $pageType])
            ->with('success', 'Contenu sauvegardé avec succès!');
    }
}
