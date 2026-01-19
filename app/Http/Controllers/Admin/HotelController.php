<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Hotel;
use App\Models\ResourcePermission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelController extends Controller
{
    /**
     * Display a listing of hotels for an event.
     */
    public function index(Accommodation $event)
    {
        // All admins can view hotels (read-only)
        if (!$event->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this event.');
        }
        
        $hotels = $event->hotels()->with(['packages', 'images'])->latest()->paginate(15);
        return view('admin.hotels.index', compact('event', 'hotels'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Accommodation $event)
    {
        // Only allow creating hotels if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Get regular admins for sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin() 
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        $subPermissions = collect();
        
        return view('admin.hotels.create', compact('event', 'admins', 'subPermissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Accommodation $event)
    {
        // Only allow creating hotels if user can edit the event
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this event. Events created by super administrators can only be modified by super administrators.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stars' => 'required|integer|between:1,5',
            'location' => 'required|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'duration' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'sub_permissions' => 'nullable|array',
            'sub_permissions.*' => 'exists:users,id',
        ]);

        // Filter out empty inclusions
        if (isset($validated['inclusions'])) {
            $validated['inclusions'] = array_filter($validated['inclusions'], function($item) {
                return !empty(trim($item));
            });
            $validated['inclusions'] = array_values($validated['inclusions']); // Re-index array
        }

        $hotel = new Hotel();
        $hotel->accommodation_id = $event->id;
        $hotel->name = $validated['name'];
        $hotel->stars = $validated['stars'];
        $hotel->location = $validated['location'];
        $hotel->location_url = $validated['location_url'] ?? null;
        $hotel->duration = $validated['duration'] ?? null;
        $hotel->description = $validated['description'] ?? null;
        $hotel->description_en = $validated['description_en'] ?? null;
        $hotel->description_fr = $validated['description_fr'] ?? null;
        $hotel->inclusions = $validated['inclusions'] ?? null;
        $hotel->website = $validated['website'] ?? null;
        $hotel->rating = $validated['rating'] ?? null;
        $hotel->review_count = $validated['review_count'] ?? null;
        $hotel->status = $validated['status'];
        $hotel->created_by = auth()->id();
        $hotel->save();

        // Handle sub-permissions (only for super-admin)
        if (auth()->user()->isSuperAdmin() && isset($validated['sub_permissions'])) {
            foreach ($validated['sub_permissions'] as $adminId) {
                ResourcePermission::firstOrCreate([
                    'resource_type' => 'hotel',
                    'resource_id' => $hotel->id,
                    'user_id' => $adminId,
                ]);
            }
        }

        return redirect()->route('admin.events.hotels.index', $event)->with('success', 'Hotel created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hotel $hotel)
    {
        // All admins can view hotels (read-only)
        if (!$hotel->accommodation->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this hotel.');
        }
        
        return view('admin.hotels.show', compact('hotel'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hotel $hotel)
    {
        // Only allow editing if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this hotel. Events created by super administrators can only be edited by super administrators.');
        }
        
        // Get regular admins for sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin() 
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current sub-permissions for this hotel
        $subPermissions = $hotel->resourcePermissions()->pluck('user_id')->toArray();
        
        return view('admin.hotels.edit', compact('hotel', 'admins', 'subPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hotel $hotel)
    {
        // Only allow updating if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this hotel. Events created by super administrators can only be edited by super administrators.');
        }

        // Also check hotel ownership (in case hotel was created by a different admin)
        if (!$hotel->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this hotel.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'stars' => 'required|integer|between:1,5',
            'location' => 'required|string|max:255',
            'location_url' => 'nullable|url|max:500',
            'duration' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'inclusions.*' => 'nullable|string|max:500',
            'website' => 'nullable|url|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
            'sub_permissions' => 'nullable|array',
            'sub_permissions.*' => 'exists:users,id',
        ]);

        // Filter out empty inclusions
        if (isset($validated['inclusions'])) {
            $validated['inclusions'] = array_filter($validated['inclusions'], function($item) {
                return !empty(trim($item));
            });
            $validated['inclusions'] = array_values($validated['inclusions']); // Re-index array
        }

        $hotel->name = $validated['name'];
        $hotel->stars = $validated['stars'];
        $hotel->location = $validated['location'];
        $hotel->location_url = $validated['location_url'] ?? null;
        $hotel->duration = $validated['duration'] ?? null;
        $hotel->description = $validated['description'] ?? null;
        $hotel->description_en = $validated['description_en'] ?? null;
        $hotel->description_fr = $validated['description_fr'] ?? null;
        $hotel->inclusions = $validated['inclusions'] ?? null;
        $hotel->website = $validated['website'] ?? null;
        $hotel->rating = $validated['rating'] ?? null;
        $hotel->review_count = $validated['review_count'] ?? null;
        $hotel->status = $validated['status'];
        $hotel->save();

        // Handle sub-permissions (only for super-admin)
        if (auth()->user()->isSuperAdmin()) {
            // Remove all existing sub-permissions for this hotel
            ResourcePermission::where('resource_type', 'hotel')
                ->where('resource_id', $hotel->id)
                ->delete();

            // Add new sub-permissions
            if (isset($validated['sub_permissions'])) {
                foreach ($validated['sub_permissions'] as $adminId) {
                    ResourcePermission::create([
                        'resource_type' => 'hotel',
                        'resource_id' => $hotel->id,
                        'user_id' => $adminId,
                    ]);
                }
            }
        }

        return redirect()->route('admin.events.hotels.index', $hotel->accommodation)->with('success', 'Hotel updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hotel $hotel)
    {
        // Only allow deleting if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this hotel. Events created by super administrators can only be modified by super administrators.');
        }

        // Also check hotel ownership
        if (!$hotel->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this hotel.');
        }

        $event = $hotel->accommodation;
        
        // Delete all hotel images (cascade will handle this, but we delete files manually)
        foreach ($hotel->images as $image) {
            \App\Services\DualStorageService::delete($image->path, 'public');
        }

        $hotel->delete();

        return redirect()->route('admin.events.hotels.index', $event)->with('success', 'Hotel deleted successfully.');
    }

    /**
     * Duplicate a hotel.
     */
    public function duplicate(Hotel $hotel)
    {
        // Only allow duplicating if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
        $duplicate = $hotel->replicate();
        $duplicate->name = $hotel->name . ' (Copy)';
        // Append -duplicated to slug to ensure uniqueness
        $baseSlug = \Illuminate\Support\Str::slug($hotel->name);
        $duplicate->slug = $baseSlug . '-duplicated';
        // Ensure uniqueness if -duplicated already exists
        $count = 1;
        while (Hotel::where('slug', $duplicate->slug)->exists()) {
            $duplicate->slug = $baseSlug . '-duplicated-' . $count;
            $count++;
        }
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        // Copy hotel images
        foreach ($hotel->images as $image) {
            $duplicateImage = $image->replicate();
            $duplicateImage->hotel_id = $duplicate->id;
            $duplicateImage->created_by = auth()->id();
            $duplicateImage->path = \App\Services\DualStorageService::copy($image->path, "hotels/{$duplicate->id}", 'public');
            $duplicateImage->save();
        }

        // Copy packages
        foreach ($hotel->packages as $package) {
            $package->replicate()->fill([
                'hotel_id' => $duplicate->id,
                'created_by' => auth()->id(),
            ])->save();
        }

        return redirect()->route('admin.events.hotels.index', $hotel->accommodation)->with('success', 'Hotel duplicated successfully.');
    }
}
