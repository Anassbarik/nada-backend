<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of packages for a hotel.
     */
    public function index(Hotel $hotel)
    {
        return view('admin.packages.index', compact('hotel'));
    }


    /**
     * Store a newly created package.
     */
    public function store(Request $request, Hotel $hotel)
    {
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

        $package = $hotel->packages()->create($validated);

        return redirect()->route('admin.hotels.packages.index', $hotel)->with('success', 'Package créé avec succès!');
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Hotel $hotel, Package $package)
    {
        // Ensure package belongs to this hotel
        if ($package->hotel_id !== $hotel->id) {
            abort(404, 'Package not found for this hotel.');
        }

        return view('admin.packages.edit', compact('hotel', 'package'));
    }

    /**
     * Update the specified package.
     */
    public function update(Request $request, Hotel $hotel, Package $package)
    {
        // Ensure package belongs to this hotel
        if ($package->hotel_id !== $hotel->id) {
            abort(404, 'Package not found for this hotel.');
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
    public function destroy(Hotel $hotel, Package $package)
    {
        // Ensure package belongs to this hotel
        if ($package->hotel_id !== $hotel->id) {
            abort(404, 'Package not found for this hotel.');
        }

        $package->delete();
        return redirect()->route('admin.hotels.packages.index', $hotel)->with('success', 'Package deleted successfully.');
    }
}
