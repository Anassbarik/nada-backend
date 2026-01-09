<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of packages for a hotel.
     */
    public function index(Hotel $hotel)
    {
        // All admins can view packages (read-only)
        if (!$hotel->event->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this hotel.');
        }
        
        $packages = $hotel->packages()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('admin.packages.index', compact('hotel', 'packages'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create(Hotel $hotel)
    {
        // Only allow creating packages if user can edit the event
        if (!$hotel->event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
        return view('admin.packages.create', compact('hotel'));
    }

    /**
     * Store a newly created package.
     */
    public function store(Request $request, Hotel $hotel)
    {
        // Only allow creating packages if user can edit the event
        if (!$hotel->event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
        $validated = $request->validate([
            'nom_package' => 'required|string|max:255',
            'type_chambre' => 'required|string|max:255',
            'check_in' => 'required|date|before:check_out',
            'check_out' => 'required|date|after:check_in',
            'occupants' => 'required|integer|min:1',
            'prix_ht' => 'required|numeric|min:0',
            'quantite_chambres' => 'required|integer|min:1',
            'chambres_restantes' => 'required|integer|min:0',
        ]);

        // Auto-calculate TTC (+20% VAT)
        $validated['prix_ttc'] = $validated['prix_ht'] * 1.20;
        
        // Auto-calculate disponibilite
        $validated['disponibilite'] = $validated['chambres_restantes'] > 0;
        $validated['created_by'] = auth()->id();

        $package = $hotel->packages()->create($validated);

        return redirect()->route('admin.hotels.packages.index', $hotel)->with('success', 'Package créé avec succès!');
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Hotel $hotel, $package)
    {
        // Only allow editing if user can edit the event
        if (!$hotel->event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this package. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Resolve via the hotel's relationship to avoid any prod-only binding issues
        $package = $hotel->packages()->findOrFail($package);

        // Also check package ownership (in case package was created by a different admin)
        if (!$package->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this package.');
        }

        return view('admin.packages.edit', compact('hotel', 'package'));
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, Hotel $hotel, $package)
    {
        // Only allow updating if user can edit the event
        if (!$hotel->event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this package. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Resolve via the hotel's relationship to avoid any prod-only binding issues
        $package = $hotel->packages()->findOrFail($package);

        // Also check package ownership
        if (!$package->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this package.');
        }

        $validated = $request->validate([
            'nom_package' => 'required|string|max:255',
            'type_chambre' => 'required|string|max:255',
            'check_in' => 'required|date|before:check_out',
            'check_out' => 'required|date|after:check_in',
            'occupants' => 'required|integer|min:1',
            'prix_ht' => 'required|numeric|min:0',
            'quantite_chambres' => 'required|integer|min:1',
            'chambres_restantes' => 'required|integer|min:0',
        ]);

        // Auto-calculate TTC (+20% VAT)
        $validated['prix_ttc'] = $validated['prix_ht'] * 1.20;
        
        // Auto-calculate disponibilite
        $validated['disponibilite'] = $validated['chambres_restantes'] > 0;

        $package->update($validated);

        return redirect()->route('admin.hotels.packages.index', $hotel)->with('success', 'Package mis à jour avec succès!');
    }

    /**
     * Remove the specified package.
     */
    public function destroy(Hotel $hotel, $package)
    {
        // Only allow deleting if user can edit the event
        if (!$hotel->event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this package. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Resolve via the hotel's relationship to avoid any prod-only binding issues
        $package = $hotel->packages()->findOrFail($package);

        // Also check package ownership
        if (!$package->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this package.');
        }

        $package->delete();
        return redirect()->route('admin.hotels.packages.index', $hotel)->with('success', 'Package deleted successfully.');
    }

    /**
     * Duplicate a package.
     */
    public function duplicate(Hotel $hotel, $package)
    {
        // Only allow duplicating if user can edit the event
        if (!$hotel->event->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this package. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Resolve via the hotel's relationship to avoid any prod-only binding issues
        $package = $hotel->packages()->findOrFail($package);

        $duplicate = $package->replicate();
        $duplicate->nom_package = $package->nom_package . ' (Copy)';
        $duplicate->hotel_id = $hotel->id;
        $duplicate->created_by = auth()->id();
        $duplicate->save();

        return redirect()->route('admin.hotels.packages.index', $hotel)->with('success', 'Package duplicated successfully.');
    }
}
