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
        // Avoid N+1 permission checks in views that call User::hasPermission()
        auth()->user()?->loadMissing('permissions');

        // Show all events - all admins can view them
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
        $event->created_by = auth()->id();

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
        // All admins can view events (read-only)
        if (!$event->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this event.');
        }
        
        return view('admin.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event)
    {
        // Only allow editing if user has permission
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event. Events created by super administrators can only be edited by super administrators.');
        }
        
        return view('admin.events.edit', compact('event'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        // Check ownership
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event.');
        }

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
        // Check ownership
        if (!$event->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this event.');
        }

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

    /**
     * Duplicate an event.
     */
    public function duplicate(Event $event)
    {
        // All admins can duplicate events (it creates a new event they own)
        if (!$event->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this event.');
        }
        
        $duplicate = $event->replicate();
        $duplicate->name = $event->name . ' (Copy)';
        // Append -duplicated to slug to ensure uniqueness
        $baseSlug = \Illuminate\Support\Str::slug($duplicate->name);
        $duplicate->slug = $baseSlug . '-duplicated';
        // Ensure uniqueness if -duplicated already exists
        $count = 1;
        while (\App\Models\Event::where('slug', $duplicate->slug)->exists()) {
            $duplicate->slug = $baseSlug . '-duplicated-' . $count;
            $count++;
        }
        $duplicate->status = 'draft';
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        // Copy images if they exist
        if ($event->logo_path) {
            $duplicate->logo_path = DualStorageService::copy($event->logo_path, "events/logos", 'public');
        }
        if ($event->banner_path) {
            $duplicate->banner_path = DualStorageService::copy($event->banner_path, "events/banners", 'public');
        }
        if ($event->organizer_logo) {
            $duplicate->organizer_logo = DualStorageService::copy($event->organizer_logo, "events/organizers", 'public');
        }
        $duplicate->save();

        // Copy event contents
        foreach ($event->contents as $content) {
            $content->replicate()->fill([
                'event_id' => $duplicate->id,
                'created_by' => auth()->id(),
            ])->save();
        }

        // Copy airports
        foreach ($event->airports as $airport) {
            $airport->replicate()->fill([
                'event_id' => $duplicate->id,
                'created_by' => auth()->id(),
            ])->save();
        }

        // Copy hotels and their related data
        foreach ($event->hotels as $hotel) {
            $duplicateHotel = $hotel->replicate();
            $duplicateHotel->event_id = $duplicate->id;
            // Append -duplicated to slug to ensure uniqueness
            $baseSlug = \Illuminate\Support\Str::slug($hotel->name);
            $duplicateHotel->slug = $baseSlug . '-duplicated';
            // Ensure uniqueness if -duplicated already exists
            $count = 1;
            while (\App\Models\Hotel::where('slug', $duplicateHotel->slug)->exists()) {
                $duplicateHotel->slug = $baseSlug . '-duplicated-' . $count;
                $count++;
            }
            $duplicateHotel->created_by = auth()->id();
            $duplicateHotel->save();

            // Copy hotel images
            foreach ($hotel->images as $image) {
                $duplicateImage = $image->replicate();
                $duplicateImage->hotel_id = $duplicateHotel->id;
                $duplicateImage->created_by = auth()->id();
                $duplicateImage->path = DualStorageService::copy($image->path, "hotels/{$duplicateHotel->id}", 'public');
                $duplicateImage->save();
            }

            // Copy packages
            foreach ($hotel->packages as $package) {
                $package->replicate()->fill([
                    'hotel_id' => $duplicateHotel->id,
                    'created_by' => auth()->id(),
                ])->save();
            }
        }

        return redirect()->route('admin.events.edit', $duplicate)->with('success', 'Event duplicated successfully. You can now modify it.');
    }
}
