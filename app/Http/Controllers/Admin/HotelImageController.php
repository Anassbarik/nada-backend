<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelImageController extends Controller
{
    /**
     * Display images for a hotel.
     */
    public function index(Hotel $hotel)
    {
        $hotel->load('images');
        return view('admin.hotels.images.index', compact('hotel'));
    }

    /**
     * Store multiple images for a hotel.
     */
    public function store(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'alt_texts' => 'nullable|array',
            'alt_texts.*' => 'nullable|string|max:255',
        ]);

        $currentImageCount = $hotel->images()->count();
        $uploadCount = count($request->file('images'));

        if ($currentImageCount + $uploadCount > 10) {
            return back()->withErrors(['images' => 'Maximum 10 images allowed per hotel.'])->withInput();
        }

        $maxSortOrder = $hotel->images()->max('sort_order') ?? -1;

        foreach ($request->file('images') as $index => $image) {
            $path = $image->store("hotels/{$hotel->id}", 'public');
            $altText = $request->alt_texts[$index] ?? null;

            HotelImage::create([
                'hotel_id' => $hotel->id,
                'path' => $path,
                'alt_text' => $altText,
                'sort_order' => ++$maxSortOrder,
                'status' => 'active',
            ]);
        }

        return redirect()->route('admin.hotels.images.index', $hotel)->with('success', "{$uploadCount} image(s) uploaded successfully.");
    }

    /**
     * Update image alt text.
     */
    public function update(Request $request, Hotel $hotel, HotelImage $image)
    {
        // Ensure image belongs to this hotel
        if ($image->hotel_id !== $hotel->id) {
            abort(404, 'Image not found for this hotel.');
        }

        $validated = $request->validate([
            'alt_text' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        $image->alt_text = $validated['alt_text'] ?? null;
        $image->status = $validated['status'];
        $image->save();

        return back()->with('success', 'Image updated successfully.');
    }

    /**
     * Delete an image.
     */
    public function destroy(Hotel $hotel, HotelImage $image)
    {
        // Ensure image belongs to this hotel
        if ($image->hotel_id !== $hotel->id) {
            abort(404, 'Image not found for this hotel.');
        }

        // Prevent deleting the last image
        $imageCount = $hotel->images()->count();
        if ($imageCount <= 1) {
            return back()->withErrors(['delete' => 'L\'hôtel doit avoir au moins 1 image.']);
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Reorder images.
     */
    public function reorder(Request $request, Hotel $hotel)
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|exists:hotel_images,id',
        ]);

        foreach ($validated['order'] as $index => $imageId) {
            HotelImage::where('id', $imageId)
                ->where('hotel_id', $hotel->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Images reordered successfully.']);
    }
}
