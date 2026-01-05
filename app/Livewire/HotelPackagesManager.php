<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Hotel;
use App\Models\Package;

class HotelPackagesManager extends Component
{
    use WithPagination;
    
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
    public $showForm = false;
    
    protected $listeners = ['refreshComponent' => '$refresh'];

    protected $rules = [
        'nom_package' => 'required|string|max:255',
        'type_chambre' => 'required|string|max:255',
        'check_in' => 'required|date|before:check_out',
        'check_out' => 'required|date|after:check_in',
        'occupants' => 'required|integer|min:1',
        'prix_ht' => 'required|numeric|min:0',
        'quantite_chambres' => 'required|integer|min:1',
        'chambres_restantes' => 'required|integer|min:0',
    ];

    public function mount(Hotel $hotel)
    {
        $this->hotel = $hotel;
    }

    public function updated($propertyName)
    {
        if ($propertyName === 'prix_ht') {
            $this->updatedPrixHt();
        }
        if ($propertyName === 'chambres_restantes') {
            $this->updatedChambresRestantes();
        }
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

    public function createPackage()
    {
        $this->validate();
        
        $package = $this->hotel->packages()->create([
            'nom_package' => $this->nom_package,
            'type_chambre' => $this->type_chambre,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'occupants' => $this->occupants,
            'prix_ht' => $this->prix_ht,
            'prix_ttc' => $this->prix_ttc,
            'quantite_chambres' => $this->quantite_chambres,
            'chambres_restantes' => $this->chambres_restantes,
            'disponibilite' => $this->disponibilite,
        ]);
        
        // MULTIPLE REFRESH METHODS
        $this->resetForm();
        $this->showForm = false;
        $this->dispatch('refreshComponent');
        $this->resetPage(); // Reset pagination
        session()->flash('message', __('package_created'));
        
        // FORCE DOM UPDATE
        $this->js('
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent("packages-updated"));
            }, 100);
        ');
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

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->resetForm();
        }
    }

    public function deletePackage($packageId)
    {
        $package = $this->hotel->packages()->find($packageId);
        if ($package) {
            $package->delete();
            $this->dispatch('refreshComponent');
            $this->resetPage();
            session()->flash('message', __('package_deleted'));
        }
    }

    public function render()
    {
        $packages = $this->hotel->packages()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('livewire.hotel-packages-manager', [
            'packages' => $packages
        ]);
    }
}
