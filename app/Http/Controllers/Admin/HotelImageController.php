<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use App\Models\HotelImage;
use App\Services\DualStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HotelImageController extends Controller
{
    /**
     * Display images for a hotel.
     */
    public function index(Hotel $hotel)
    {
        // All admins can view hotel images (read-only)
        if (!$hotel->accommodation->canBeViewedBy(auth()->user())) {
            abort(403, 'You do not have permission to view this hotel.');
        }
        
        $hotel->load('images');
        return view('admin.hotels.images.index', compact('hotel'));
    }

    /**
     * Store multiple images for a hotel.
     */
    public function store(Request $request, Hotel $hotel)
    {
        // Only allow uploading images if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Check if files were uploaded
        if (!$request->hasFile('images')) {
            return back()->withErrors(['images' => 'Please select at least one image to upload.'])->withInput();
        }

        $files = $request->file('images');
        
        // Ensure files is an array
        if (!is_array($files)) {
            $files = [$files];
        }

        // Validate each file
        $validated = $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ], [
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Images must be JPEG, PNG, or JPG format.',
            'images.*.max' => 'Each image must be less than 5MB.',
        ]);

        $currentImageCount = $hotel->images()->count();
        $uploadCount = count($files);

        if ($currentImageCount + $uploadCount > 10) {
            return back()->withErrors(['images' => "Maximum 10 images allowed per hotel. You currently have {$currentImageCount} images."])->withInput();
        }

        $maxSortOrder = $hotel->images()->max('sort_order') ?? -1;
        $uploaded = 0;
        $errors = [];

        foreach ($files as $index => $image) {
            try {
                // Store the file in both storage/app/public and public/storage
                $path = DualStorageService::store($image, "hotels/{$hotel->id}", 'public');
                
                if (!$path) {
                    $errors[] = "Failed to upload image " . ($index + 1);
                    continue;
                }

                // Create database record
                HotelImage::create([
                    'hotel_id' => $hotel->id,
                    'path' => $path,
                    'alt_text' => null,
                    'sort_order' => ++$maxSortOrder,
                    'status' => 'active',
                    'created_by' => auth()->id(),
                ]);
                
                $uploaded++;
            } catch (\Exception $e) {
                $errors[] = "Error uploading image " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        if ($uploaded === 0) {
            return back()->withErrors(['images' => 'Failed to upload images. ' . implode(' ', $errors)])->withInput();
        }

        $message = "{$uploaded} image(s) uploaded successfully.";
        if (count($errors) > 0) {
            $message .= " Some errors occurred: " . implode(' ', $errors);
        }

        return redirect()->route('admin.hotels.images.index', $hotel)->with('success', $message);
    }

    /**
     * Update image alt text.
     */
    public function update(Request $request, Hotel $hotel, $image)
    {
        // Only allow updating if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Resolve via the hotel's relationship to avoid any prod-only binding issues
        $image = $hotel->images()->findOrFail($image);

        // Also check image ownership
        if (!$image->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to edit this image.');
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
    public function destroy(Hotel $hotel, $image)
    {
        // Only allow deleting if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
        // Resolve via the hotel's relationship to avoid any prod-only binding issues
        $image = $hotel->images()->findOrFail($image);

        // Also check image ownership
        if (!$image->canBeDeletedBy(auth()->user())) {
            abort(403, 'You do not have permission to delete this image.');
        }

        // Prevent deleting the last image
        $imageCount = $hotel->images()->count();
        if ($imageCount <= 1) {
            return back()->withErrors(['delete' => 'L\'hôtel doit avoir au moins 1 image.']);
        }

        DualStorageService::delete($image->path, 'public');
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Reorder images.
     */
    public function reorder(Request $request, Hotel $hotel)
    {
        // Only allow reordering if user can edit the event
        if (!$hotel->accommodation->canBeEditedBy(auth()->user())) {
            abort(403, 'You do not have permission to modify this hotel. Events created by super administrators can only be modified by super administrators.');
        }
        
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
