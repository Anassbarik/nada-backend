<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\HotelImage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class HotelImagesManager extends Component
{
    use WithFileUploads;

    public Hotel $hotel;
    public $images = [];
    public $altTexts = [];
    public $uploadedImages = [];

    public function mount(Hotel $hotel)
    {
        $this->hotel = $hotel->load('images');
    }

    public function updatedImages()
    {
        $this->validate([
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
            'images' => 'max:10',
        ], [
            'images.max' => 'Maximum 10 images allowed.',
            'images.*.max' => 'Each image must be less than 5MB.',
        ]);
    }

    public function uploadImages()
    {
        $this->validate([
            'images' => 'required|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $currentCount = $this->hotel->images()->count();
        if ($currentCount + count($this->images) > 10) {
            $this->addError('images', 'Maximum 10 images allowed per hotel.');
            return;
        }

        $maxSortOrder = $this->hotel->images()->max('sort_order') ?? -1;

        foreach ($this->images as $index => $image) {
            $path = $image->store("hotels/{$this->hotel->id}", 'public');
            $altText = $this->altTexts[$index] ?? null;

            HotelImage::create([
                'hotel_id' => $this->hotel->id,
                'path' => $path,
                'alt_text' => $altText,
                'sort_order' => ++$maxSortOrder,
                'status' => 'active',
            ]);
        }

        $this->images = [];
        $this->altTexts = [];
        $this->hotel->refresh();
        $this->dispatch('images-uploaded');
    }

    public function deleteImage($imageId)
    {
        // Prevent deleting the last image
        $imageCount = $this->hotel->images()->count();
        if ($imageCount <= 1) {
            $this->addError('delete', 'L\'hôtel doit avoir au moins 1 image.');
            return;
        }

        $image = HotelImage::findOrFail($imageId);
        
        if ($image->hotel_id !== $this->hotel->id) {
            return;
        }

        Storage::disk('public')->delete($image->path);
        $image->delete();
        
        $this->hotel->refresh();
        $this->dispatch('image-deleted');
    }

    public function updateAltText($imageId, $altText)
    {
        $image = HotelImage::findOrFail($imageId);
        
        if ($image->hotel_id !== $this->hotel->id) {
            return;
        }

        $image->alt_text = $altText;
        $image->save();
    }

    public function reorderImages($order)
    {
        foreach ($order as $index => $imageId) {
            HotelImage::where('id', $imageId)
                ->where('hotel_id', $this->hotel->id)
                ->update(['sort_order' => $index]);
        }

        $this->hotel->refresh();
    }

    public function render()
    {
        return view('livewire.hotel-images-manager');
    }
}
