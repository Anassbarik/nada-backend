<?php

namespace App\Livewire;

use App\Models\Event;
use App\Models\EventContent;
use App\Services\DualStorageService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EventContentEditor extends Component
{
    use WithFileUploads;

    public Event $event;
    public $page_type;
    public $sections = [];
    public $hero_image;
    public $uploadedHeroImage;

    public function mount(Event $event, $pageType = 'conditions')
    {
        $this->event = $event;
        $this->page_type = $pageType;
        
        $content = EventContent::where('event_id', $event->id)
            ->where('page_type', $pageType)
            ->first();
        
        if ($content) {
            $this->sections = $content->sections ?? [];
            $this->hero_image = $content->hero_image;
        } else {
            $this->sections = [];
        }
    }

    public function addSection()
    {
        $this->sections[] = ['title' => '', 'content' => ''];
    }

    public function removeSection($index)
    {
        unset($this->sections[$index]);
        $this->sections = array_values($this->sections);
    }

    public function updatedUploadedHeroImage()
    {
        $this->validate([
            'uploadedHeroImage' => 'image|max:5120',
        ]);
    }

    public function save()
    {
        $this->validate([
            'sections' => 'required|array|min:1',
            'sections.*.title' => 'required|string|max:255',
            'sections.*.content' => 'required|string',
            'uploadedHeroImage' => 'nullable|image|max:5120',
        ]);

        // Upload hero image if provided
        $heroImagePath = $this->hero_image;
        if ($this->uploadedHeroImage) {
            // Delete old hero image if exists
            if ($heroImagePath) {
                DualStorageService::delete($heroImagePath, 'public');
            }
            // Store in both storage/app/public and public/storage
            $heroImagePath = DualStorageService::store($this->uploadedHeroImage, "events/{$this->event->id}/content", 'public');
        }

        EventContent::updateOrCreate(
            [
                'event_id' => $this->event->id,
                'page_type' => $this->page_type,
            ],
            [
                'hero_image' => $heroImagePath,
                'sections' => $this->sections,
            ]
        );

        $this->hero_image = $heroImagePath;
        $this->uploadedHeroImage = null;
        
        session()->flash('success', 'Contenu sauvegardé avec succès!');
    }

    public function render()
    {
        return view('livewire.event-content-editor');
    }
}
