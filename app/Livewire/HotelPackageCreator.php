<?php

namespace App\Livewire;

use App\Models\Hotel;
use App\Models\Package;
use Livewire\Component;

class HotelPackageCreator extends Component
{
    public Hotel $hotel;
    
    public $nom_package = '';
    public $type_chambre = '';
    public $check_in = '';
    public $check_out = '';
    public $occupants = 1;
    public $prix_ht = 0;
    public $prix_ttc = 0;
    public $quantite_chambres = 1;
    public $chambres_restantes = 0;
    public $disponibilite = true;

    public function mount(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    public function updatedPrixHt()
    {
        $this->prix_ttc = (float) $this->prix_ht * 1.20;
        $this->updatedChambresRestantes();
    }

    public function updatedChambresRestantes()
    {
        $this->disponibilite = $this->chambres_restantes > 0;
    }

    public function save()
    {
        $validated = $this->validate([
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
        $validated['prix_ttc'] = (float) $validated['prix_ht'] * 1.20;
        
        // Auto-calculate disponibilite
        $validated['disponibilite'] = $validated['chambres_restantes'] > 0;

        // Create package
        $package = $this->hotel->packages()->create($validated);

        // SUCCESS FLASH MESSAGE
        session()->flash('success', __('package_created'));
        
        // RESET FORM
        $this->resetForm();
        
        // DISPATCH EVENT TO REFRESH PARENT
        $this->dispatch('package-created');
        
        // SCROLL TO TOP TO SEE SUCCESS MESSAGE
        $this->js('window.scrollTo({ top: 0, behavior: "smooth" })');
    }
    
    public function resetForm()
    {
        $this->reset([
            'nom_package', 'type_chambre', 'check_in', 'check_out',
            'occupants', 'prix_ht', 'quantite_chambres', 'chambres_restantes'
        ]);
        $this->prix_ttc = 0;
        $this->disponibilite = true;
        $this->occupants = 1;
        $this->quantite_chambres = 1;
        $this->chambres_restantes = 0;
    }

    public function render()
    {
        return view('livewire.hotel-package-creator');
    }
}
