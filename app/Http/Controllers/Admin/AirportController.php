<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Airport;
use Illuminate\Http\Request;

class AirportController extends Controller
{
    /**
     * Display a listing of airports for an event.
     */
    public function index(Accommodation $event)
    {
        // All admins can view airports (read-only)
        if (!$event->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this event.');
        }
        
        $airports = $event->airports()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
            
        return view('admin.airports.index', compact('event', 'airports'));
    }

    /**
     * Show the form for creating a new airport.
     */
    public function create(Accommodation $event)
    {
        // Only allow creating airports if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        return view('admin.airports.create', compact('event'));
    }

    /**
     * Store a newly created airport.
     */
    public function store(Request $request, Accommodation $event)
    {
        // Only allow creating airports if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'distance_from_venue' => 'nullable|numeric|min:0',
            'distance_unit' => 'nullable|in:km,miles',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
        ]);

        $validated['accommodation_id'] = $event->id;
        $validated['created_by'] = auth()->id();
        $validated['distance_unit'] = $validated['distance_unit'] ?? 'km';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['active'] = $validated['active'] ?? true;

        $airport = Airport::create($validated);

        return redirect()->route('admin.events.airports.index', $event)
            ->with('success', 'Airport created successfully.');
    }

    /**
     * Show the form for editing the specified airport.
     */
    public function edit(Accommodation $event, Airport $airport)
    {
        // Only allow editing if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Ensure airport belongs to event (use loose comparison to handle type mismatches)
        if ($airport->accommodation_id != $event->id) {
            abort(404, 'Airport does not belong to this event.');
        }

        return view('admin.airports.edit', compact('event', 'airport'));
    }

    /**
     * Update the specified airport.
     */
    public function update(Request $request, Event $event, Airport $airport)
    {
        // Only allow updating if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Ensure airport belongs to event (use loose comparison to handle type mismatches)
        if ($airport->accommodation_id != $event->id) {
            abort(404, 'Airport does not belong to this event.');
        }

        // Check ownership
        if (!$airport->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this airport.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'distance_from_venue' => 'nullable|numeric|min:0',
            'distance_unit' => 'nullable|in:km,miles',
            'sort_order' => 'nullable|integer|min:0',
            'active' => 'nullable|boolean',
        ]);

        $validated['distance_unit'] = $validated['distance_unit'] ?? 'km';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $airport->update($validated);

        return redirect()->route('admin.events.airports.index', $event)
            ->with('success', 'Airport updated successfully.');
    }

    /**
     * Remove the specified airport.
     */
    public function destroy(Event $event, Airport $airport)
    {
        // Only allow deleting if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Ensure airport belongs to event (use loose comparison to handle type mismatches)
        if ($airport->accommodation_id != $event->id) {
            abort(404, 'Airport does not belong to this event.');
        }

        // Check ownership
        if (!$airport->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this airport.');
        }

        $airport->delete();

        return redirect()->route('admin.events.airports.index', $event)
            ->with('success', 'Airport deleted successfully.');
    }

    /**
     * Duplicate an airport.
     */
    public function duplicate(Event $event, Airport $airport)
    {
        // Only allow duplicating if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Ensure airport belongs to event (use loose comparison to handle type mismatches)
        if ($airport->accommodation_id != $event->id) {
            abort(404, 'Airport does not belong to this event.');
        }

        $duplicate = $airport->replicate();
        $duplicate->name = $airport->name . ' (Copy)';
        $duplicate->accommodation_id = $event->id;
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        return redirect()->route('admin.events.airports.index', $event)
            ->with('success', 'Airport duplicated successfully.');
    }
}
