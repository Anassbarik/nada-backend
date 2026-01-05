<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelNightPrice;
use Illuminate\Http\Request;

class HotelNightPriceController extends Controller
{
    /**
     * Display a listing of night prices for a hotel.
     */
    public function index(Hotel $hotel)
    {
        $nightPrices = $hotel->nightPrices()->latest()->get();
        return view('admin.hotels.night-prices.index', compact('hotel', 'nightPrices'));
    }

    /**
     * Show the form for creating a new night price.
     */
    public function create(Hotel $hotel)
    {
        return view('admin.hotels.night-prices.create', compact('hotel'));
    }

    /**
     * Store a newly created night price.
     */
    public function store(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'price_per_night' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after:valid_from',
            'status' => 'required|in:active,inactive',
        ]);

        $nightPrice = new HotelNightPrice();
        $nightPrice->hotel_id = $hotel->id;
        $nightPrice->price_per_night = $validated['price_per_night'];
        $nightPrice->valid_from = $validated['valid_from'];
        $nightPrice->valid_to = $validated['valid_to'] ?? null;
        $nightPrice->status = $validated['status'];
        $nightPrice->save();

        return redirect()->route('admin.hotels.night-prices.index', $hotel)->with('success', 'Night price created successfully.');
    }

    /**
     * Show the form for editing the specified night price.
     */
    public function edit(Hotel $hotel, HotelNightPrice $nightPrice)
    {
        // Ensure night price belongs to this hotel
        if ($nightPrice->hotel_id !== $hotel->id) {
            abort(404, 'Night price not found for this hotel.');
        }

        return view('admin.hotels.night-prices.edit', compact('hotel', 'nightPrice'));
    }

    /**
     * Update the specified night price.
     */
    public function update(Request $request, Hotel $hotel, HotelNightPrice $nightPrice)
    {
        // Ensure night price belongs to this hotel
        if ($nightPrice->hotel_id !== $hotel->id) {
            abort(404, 'Night price not found for this hotel.');
        }

        $validated = $request->validate([
            'price_per_night' => 'required|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_to' => 'nullable|date|after:valid_from',
            'status' => 'required|in:active,inactive',
        ]);

        $nightPrice->price_per_night = $validated['price_per_night'];
        $nightPrice->valid_from = $validated['valid_from'];
        $nightPrice->valid_to = $validated['valid_to'] ?? null;
        $nightPrice->status = $validated['status'];
        $nightPrice->save();

        return redirect()->route('admin.hotels.night-prices.index', $hotel)->with('success', 'Night price updated successfully.');
    }

    /**
     * Remove the specified night price.
     */
    public function destroy(Hotel $hotel, HotelNightPrice $nightPrice)
    {
        // Ensure night price belongs to this hotel
        if ($nightPrice->hotel_id !== $hotel->id) {
            abort(404, 'Night price not found for this hotel.');
        }

        $nightPrice->delete();
        return redirect()->route('admin.hotels.night-prices.index', $hotel)->with('success', 'Night price deleted successfully.');
    }
}
