<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\DualStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $events = Event::latest()->paginate(15);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.events.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:500',
            'google_maps_url' => 'nullable|url|max:500',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'website_url' => 'nullable|url|max:500',
            'organizer_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:5120',
            'menu_links' => 'nullable|array',
            'menu_links.*.label' => 'required_with:menu_links|string',
            'menu_links.*.url' => 'required_with:menu_links|url',
            'status' => 'required|in:draft,published,archived',
        ]);

        $event = new Event();
        $event->name = $validated['name'];
        $event->venue = $validated['venue'] ?? null;
        $event->location = $validated['location'] ?? null;
        $event->google_maps_url = $validated['google_maps_url'] ?? null;
        $event->start_date = $validated['start_date'] ?? null;
        $event->end_date = $validated['end_date'] ?? null;
        $event->website_url = $validated['website_url'] ?? null;
        $event->description = $validated['description'] ?? null;
        $event->menu_links = $validated['menu_links'] ?? null;
        $event->status = $validated['status'];

        if ($request->hasFile('organizer_logo')) {
            $event->organizer_logo = DualStorageService::store($request->file('organizer_logo'), 'events/organizers', 'public');
        }

        if ($request->hasFile('logo')) {
            $event->logo_path = DualStorageService::store($request->file('logo'), 'events/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            $event->banner_path = DualStorageService::store($request->file('banner'), 'events/banners', 'public');
        }

        $event->save();

        return redirect()->route('admin.events.index')->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'venue' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:500',
            'google_maps_url' => 'nullable|url|max:500',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'website_url' => 'nullable|url|max:500',
            'organizer_logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:5120',
            'menu_links' => 'nullable|array',
            'menu_links.*.label' => 'required_with:menu_links|string',
            'menu_links.*.url' => 'required_with:menu_links|url',
            'status' => 'required|in:draft,published,archived',
        ]);

        $event->name = $validated['name'];
        $event->venue = $validated['venue'] ?? null;
        $event->location = $validated['location'] ?? null;
        $event->google_maps_url = $validated['google_maps_url'] ?? null;
        $event->start_date = $validated['start_date'] ?? null;
        $event->end_date = $validated['end_date'] ?? null;
        $event->website_url = $validated['website_url'] ?? null;
        $event->description = $validated['description'] ?? null;
        $event->menu_links = $validated['menu_links'] ?? null;
        $event->status = $validated['status'];

        if ($request->hasFile('organizer_logo')) {
            if ($event->organizer_logo) {
                DualStorageService::delete($event->organizer_logo, 'public');
            }
            $event->organizer_logo = DualStorageService::store($request->file('organizer_logo'), 'events/organizers', 'public');
        }

        if ($request->hasFile('logo')) {
            if ($event->logo_path) {
                DualStorageService::delete($event->logo_path, 'public');
            }
            $event->logo_path = DualStorageService::store($request->file('logo'), 'events/logos', 'public');
        }

        if ($request->hasFile('banner')) {
            if ($event->banner_path) {
                DualStorageService::delete($event->banner_path, 'public');
            }
            $event->banner_path = DualStorageService::store($request->file('banner'), 'events/banners', 'public');
        }

        $event->save();

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        if ($event->logo_path) {
            DualStorageService::delete($event->logo_path, 'public');
        }
        if ($event->banner_path) {
            DualStorageService::delete($event->banner_path, 'public');
        }
        if ($event->organizer_logo) {
            DualStorageService::delete($event->organizer_logo, 'public');
        }

        $event->delete();

        return redirect()->route('admin.events.index')->with('success', 'Event deleted successfully.');
    }
}
