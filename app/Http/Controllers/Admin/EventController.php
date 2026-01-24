<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\ResourcePermission;
use App\Models\User;
use App\Services\DualStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

        // Show all accommodations - all admins can view them
        $events = Accommodation::latest()->paginate(15);
        return view('admin.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get regular admins for sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin() 
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current sub-permissions (empty for create)
        $subPermissions = collect();
        $flightsSubPermissions = collect();

        return view('admin.events.create', compact('admins', 'subPermissions', 'flightsSubPermissions'));
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
            'organizer_name' => 'required|string|max:255',
            'organizer_email' => 'required|email|max:255|unique:users,email',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:5120',
            'menu_links' => 'nullable|array',
            'menu_links.*.label' => 'required_with:menu_links|string',
            'menu_links.*.url' => 'required_with:menu_links|url',
            'status' => 'required|in:draft,published,archived',
            'show_flight_prices' => 'nullable|boolean',
            'sub_permissions' => 'nullable|array',
            'sub_permissions.*' => 'exists:users,id',
            'flights_sub_permissions' => 'nullable|array',
            'flights_sub_permissions.*' => 'exists:users,id',
        ]);

        $event = new Accommodation();
        $event->name = $validated['name'];
        $event->venue = $validated['venue'] ?? null;
        $event->location = $validated['location'] ?? null;
        $event->google_maps_url = $validated['google_maps_url'] ?? null;
        $event->start_date = $validated['start_date'] ?? null;
        $event->end_date = $validated['end_date'] ?? null;
        $event->website_url = $validated['website_url'] ?? null;
        $event->description = $validated['description'] ?? null;
        $event->description_en = $validated['description_en'] ?? null;
        $event->description_fr = $validated['description_fr'] ?? null;
        $event->menu_links = $validated['menu_links'] ?? null;
        $event->status = $validated['status'];
        $event->show_flight_prices_public = $request->has('show_flight_prices_public') ? (bool) $request->input('show_flight_prices_public') : true;
        $event->show_flight_prices_client_dashboard = $request->has('show_flight_prices_client_dashboard') ? (bool) $request->input('show_flight_prices_client_dashboard') : true;
        $event->show_flight_prices_organizer_dashboard = $request->has('show_flight_prices_organizer_dashboard') ? (bool) $request->input('show_flight_prices_organizer_dashboard') : true;
        $event->created_by = auth()->id();

        // Generate password for organizer
        $organizerPassword = Str::random(12);

        // Create organizer user
        $organizer = User::create([
            'name' => $validated['organizer_name'],
            'email' => $validated['organizer_email'],
            'password' => Hash::make($organizerPassword),
            'role' => 'organizer',
            'email_verified_at' => now(),
        ]);

        // Link organizer to event
        $event->organizer_id = $organizer->id;

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

        // Handle sub-permissions (only for super-admin)
        if (auth()->user()->isSuperAdmin() && isset($validated['sub_permissions'])) {
            foreach ($validated['sub_permissions'] as $adminId) {
                ResourcePermission::firstOrCreate([
                    'resource_type' => 'event',
                    'resource_id' => $event->id,
                    'user_id' => $adminId,
                ]);
            }
        }

        // Handle flights sub-permissions (only for super-admin)
        if (auth()->user()->isSuperAdmin() && isset($validated['flights_sub_permissions'])) {
            foreach ($validated['flights_sub_permissions'] as $adminId) {
                ResourcePermission::firstOrCreate([
                    'resource_type' => 'flight',
                    'resource_id' => $event->id,
                    'user_id' => $adminId,
                ]);
            }
        }

        // Generate organizer credentials PDF
        try {
            $pdf = Pdf::loadView('admin.organizers.credentials', [
                'event' => $event,
                'organizer' => $organizer,
                'password' => $organizerPassword,
            ]);
            
            DualStorageService::makeDirectory('organizers');
            $relativePath = "organizers/{$organizer->id}-credentials.pdf";
            DualStorageService::put($relativePath, $pdf->output(), 'public');
            
            return redirect()->route('admin.events.index')
                ->with('success', 'Event created successfully.')
                ->with('organizer_pdf_url', route('admin.organizers.download-credentials', $organizer));
        } catch (\Throwable $e) {
            \Log::error('Failed to generate organizer credentials PDF', [
                'organizer_id' => $organizer->id,
                'error' => $e->getMessage(),
            ]);
            
            return redirect()->route('admin.events.index')
                ->with('success', 'Event created successfully. Organizer created but PDF generation failed.')
                ->with('organizer_password', $organizerPassword)
                ->with('organizer_email', $organizer->email);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Accommodation $event)
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
    public function edit(Accommodation $event)
    {
        // Only allow editing if user has permission
        if (!$event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this event. Events created by super administrators can only be edited by super administrators.');
        }
        
        // Get regular admins for sub-permissions assignment (only for super-admin)
        $admins = auth()->user()->isSuperAdmin() 
            ? User::where('role', 'admin')->orderBy('name')->get()
            : collect();

        // Get current sub-permissions for this event
        $subPermissions = $event->resourcePermissions()->pluck('user_id')->toArray();
        
        // Load organizer relationship
        $event->load('organizer');
        
        return view('admin.events.edit', compact('event', 'admins', 'subPermissions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Accommodation $event)
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
            'description_en' => 'nullable|string',
            'description_fr' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:5120',
            'menu_links' => 'nullable|array',
            'menu_links.*.label' => 'required_with:menu_links|string',
            'menu_links.*.url' => 'required_with:menu_links|url',
            'status' => 'required|in:draft,published,archived',
            'show_flight_prices_public' => 'nullable|boolean',
            'show_flight_prices_client_dashboard' => 'nullable|boolean',
            'show_flight_prices_organizer_dashboard' => 'nullable|boolean',
            'sub_permissions' => 'nullable|array',
            'sub_permissions.*' => 'exists:users,id',
            'flights_sub_permissions' => 'nullable|array',
            'flights_sub_permissions.*' => 'exists:users,id',
        ]);

        $event->name = $validated['name'];
        $event->venue = $validated['venue'] ?? null;
        $event->location = $validated['location'] ?? null;
        $event->google_maps_url = $validated['google_maps_url'] ?? null;
        $event->start_date = $validated['start_date'] ?? null;
        $event->end_date = $validated['end_date'] ?? null;
        $event->website_url = $validated['website_url'] ?? null;
        $event->description = $validated['description'] ?? null;
        $event->description_en = $validated['description_en'] ?? null;
        $event->description_fr = $validated['description_fr'] ?? null;
        $event->menu_links = $validated['menu_links'] ?? null;
        $event->status = $validated['status'];
        $event->show_flight_prices_public = $request->has('show_flight_prices_public') ? (bool) $request->input('show_flight_prices_public') : true;
        $event->show_flight_prices_client_dashboard = $request->has('show_flight_prices_client_dashboard') ? (bool) $request->input('show_flight_prices_client_dashboard') : true;
        $event->show_flight_prices_organizer_dashboard = $request->has('show_flight_prices_organizer_dashboard') ? (bool) $request->input('show_flight_prices_organizer_dashboard') : true;

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

        // Handle sub-permissions (only for super-admin)
        if (auth()->user()->isSuperAdmin()) {
            // Remove all existing sub-permissions for this event
            ResourcePermission::where('resource_type', 'event')
                ->where('resource_id', $event->id)
                ->delete();

            // Add new sub-permissions
            if (isset($validated['sub_permissions'])) {
                foreach ($validated['sub_permissions'] as $adminId) {
                    ResourcePermission::create([
                        'resource_type' => 'event',
                        'resource_id' => $event->id,
                        'user_id' => $adminId,
                    ]);
                }
            }
        }

        return redirect()->route('admin.events.index')->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Accommodation $event)
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
    public function duplicate(Accommodation $event)
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
        while (\App\Models\Accommodation::where('slug', $duplicate->slug)->exists()) {
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

        // Copy accommodation contents
        foreach ($event->contents as $content) {
            $content->replicate()->fill([
                'accommodation_id' => $duplicate->id,
                'created_by' => auth()->id(),
            ])->save();
        }

        // Copy airports
        foreach ($event->airports as $airport) {
            $airport->replicate()->fill([
                'accommodation_id' => $duplicate->id,
                'created_by' => auth()->id(),
            ])->save();
        }

        // Copy hotels and their related data
        foreach ($event->hotels as $hotel) {
            $duplicateHotel = $hotel->replicate();
            $duplicateHotel->accommodation_id = $duplicate->id;
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

    /**
     * Download organizer credentials PDF.
     */
    public function downloadOrganizerCredentials(User $organizer)
    {
        // Only super-admin can download credentials
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'You do not have permission to download organizer credentials.');
        }

        // Verify user is an organizer
        if (!$organizer->isOrganizer()) {
            abort(404, 'User is not an organizer.');
        }

        $event = $organizer->organizedAccommodations()->first();

        if (!$event) {
            abort(404, 'Organizer has no associated event.');
        }

        // Check if PDF exists
        $pdfPath = "organizers/{$organizer->id}-credentials.pdf";
        if (file_exists(public_path('storage/' . $pdfPath))) {
            return response()->file(public_path('storage/' . $pdfPath));
        }

        // If PDF doesn't exist, return error (password is not stored)
        return back()->with('error', 'Credentials PDF not found. Please regenerate it from the event edit page.');
    }
}
