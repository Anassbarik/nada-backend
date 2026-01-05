<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    /**
     * Display a listing of hotels for an event.
     */
    public function index(Event $event)
    {
        $hotels = $event->hotels()->with(['packages', 'images'])->latest()->paginate(15);
        return view('admin.hotels.index', compact('event', 'hotels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        return view('admin.hotels.create', compact('event'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'duration' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $hotel = new Hotel();
        $hotel->event_id = $event->id;
        $hotel->name = $validated['name'];
        $hotel->location = $validated['location'];
        $hotel->location_url = $validated['location_url'] ?? null;
        $hotel->duration = $validated['duration'] ?? null;
        $hotel->description = $validated['description'] ?? null;
        $hotel->website = $validated['website'] ?? null;
        $hotel->rating = $validated['rating'] ?? null;
        $hotel->review_count = $validated['review_count'] ?? null;
        $hotel->status = $validated['status'];
        $hotel->save();

        return redirect()->route('admin.events.hotels.index', $event)->with('success', 'Hotel created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hotel $hotel)
    {
        return view('admin.hotels.show', compact('hotel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        return view('admin.hotels.edit', compact('hotel'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'duration' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $hotel->name = $validated['name'];
        $hotel->location = $validated['location'];
        $hotel->location_url = $validated['location_url'] ?? null;
        $hotel->duration = $validated['duration'] ?? null;
        $hotel->description = $validated['description'] ?? null;
        $hotel->website = $validated['website'] ?? null;
        $hotel->rating = $validated['rating'] ?? null;
        $hotel->review_count = $validated['review_count'] ?? null;
        $hotel->status = $validated['status'];
        $hotel->save();

        return redirect()->route('admin.events.hotels.index', $hotel->event)->with('success', 'Hotel updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotel $hotel)
    {
        $event = $hotel->event;
        
        // Delete all hotel images (cascade will handle this, but we delete files manually)
        foreach ($hotel->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $hotel->delete();

        return redirect()->route('admin.events.hotels.index', $event)->with('success', 'Hotel deleted successfully.');
    }
}
