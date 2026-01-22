@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Create Flight</h1>
      <p class="text-gray-600 mt-1">{{ $accommodation->name }}</p>
    </div>
    <a href="{{ route('admin.flights.index', $accommodation) }}" class="text-logo-link hover:underline inline-flex items-center">
      <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
      Back to Flights
    </a>
  </div>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <form method="POST" action="{{ route('admin.flights.store', $accommodation) }}" enctype="multipart/form-data">
        @csrf

        {{-- Client Information --}}
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-4">Client Information</h3>
          
          <div class="mb-4">
            <x-input-label for="full_name" :value="__('Nom complet de client')" />
            <x-text-input id="full_name" class="block mt-1 w-full" type="text" name="full_name" :value="old('full_name')" required autofocus />
            <x-input-error :messages="$errors->get('full_name')" class="mt-2" />
          </div>
        </div>

        {{-- Flight Details --}}
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-4">Flight Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="flight_class" :value="__('Classe')" />
              <select id="flight_class" name="flight_class" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                <option value="economy" {{ old('flight_class') === 'economy' ? 'selected' : '' }}>Economy</option>
                <option value="business" {{ old('flight_class') === 'business' ? 'selected' : '' }}>Business</option>
                <option value="first" {{ old('flight_class') === 'first' ? 'selected' : '' }}>First Class</option>
              </select>
              <x-input-error :messages="$errors->get('flight_class')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="flight_category" :value="__('Type de vol')" />
              <select id="flight_category" name="flight_category" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                <option value="one_way" {{ old('flight_category', 'one_way') === 'one_way' ? 'selected' : '' }}>Aller Simple (One Way)</option>
                <option value="round_trip" {{ old('flight_category') === 'round_trip' ? 'selected' : '' }}>Aller-Retour (Round Trip)</option>
              </select>
              <x-input-error :messages="$errors->get('flight_category')" class="mt-2" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="departure_date" :value="__('Date départ')" />
              <x-text-input id="departure_date" class="block mt-1 w-full" type="date" name="departure_date" :value="old('departure_date')" required />
              <x-input-error :messages="$errors->get('departure_date')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="departure_time" :value="__('Heure départ')" />
              <x-text-input id="departure_time" class="block mt-1 w-full" type="time" name="departure_time" :value="old('departure_time')" required />
              <x-input-error :messages="$errors->get('departure_time')" class="mt-2" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="arrival_date" :value="__('Date arrivée')" />
              <x-text-input id="arrival_date" class="block mt-1 w-full" type="date" name="arrival_date" :value="old('arrival_date')" required />
              <x-input-error :messages="$errors->get('arrival_date')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="arrival_time" :value="__('Heure arrivée')" />
              <x-text-input id="arrival_time" class="block mt-1 w-full" type="time" name="arrival_time" :value="old('arrival_time')" required />
              <x-input-error :messages="$errors->get('arrival_time')" class="mt-2" />
            </div>
          </div>

          <div class="mb-4">
            <x-input-label for="departure_flight_number" :value="__('Vol Départ (e.g., AT2222)')" />
            <x-text-input id="departure_flight_number" class="block mt-1 w-full" type="text" name="departure_flight_number" :value="old('departure_flight_number')" required />
            <x-input-error :messages="$errors->get('departure_flight_number')" class="mt-2" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="departure_airport" :value="__('Aéroport Départ (e.g., CMN, CDG)')" />
              <x-text-input id="departure_airport" class="block mt-1 w-full" type="text" name="departure_airport" :value="old('departure_airport')" required placeholder="e.g., CMN, CDG" />
              <x-input-error :messages="$errors->get('departure_airport')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="arrival_airport" :value="__('Aéroport Arrivée (e.g., RAK, ORY)')" />
              <x-text-input id="arrival_airport" class="block mt-1 w-full" type="text" name="arrival_airport" :value="old('arrival_airport')" required placeholder="e.g., RAK, ORY" />
              <x-input-error :messages="$errors->get('arrival_airport')" class="mt-2" />
            </div>
          </div>

          <div class="mb-4">
            <x-input-label for="departure_price_ttc" :value="__('Prix Aller TTC (MAD)')" />
            <x-text-input id="departure_price_ttc" class="block mt-1 w-full" type="number" name="departure_price_ttc" step="0.01" min="0" :value="old('departure_price_ttc', 0)" required />
            <x-input-error :messages="$errors->get('departure_price_ttc')" class="mt-2" />
          </div>
        </div>

        {{-- Return Flight (Conditional) --}}
        <div class="mb-6 return-flight-section" id="return-flight-section" style="display: none;">
          <h3 class="text-lg font-semibold mb-4">Return Flight (Aller-Retour)</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="return_date" :value="__('Date retour')" />
              <x-text-input id="return_date" class="block mt-1 w-full" type="date" name="return_date" :value="old('return_date')" />
              <x-input-error :messages="$errors->get('return_date')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="return_departure_time" :value="__('Heure départ retour')" />
              <x-text-input id="return_departure_time" class="block mt-1 w-full" type="time" name="return_departure_time" :value="old('return_departure_time')" />
              <x-input-error :messages="$errors->get('return_departure_time')" class="mt-2" />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="return_arrival_date" :value="__('Date arrivée retour')" />
              <x-text-input id="return_arrival_date" class="block mt-1 w-full" type="date" name="return_arrival_date" :value="old('return_arrival_date')" />
              <x-input-error :messages="$errors->get('return_arrival_date')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="return_arrival_time" :value="__('Heure arrivée retour')" />
              <x-text-input id="return_arrival_time" class="block mt-1 w-full" type="time" name="return_arrival_time" :value="old('return_arrival_time')" />
              <x-input-error :messages="$errors->get('return_arrival_time')" class="mt-2" />
            </div>
          </div>

          <div class="mb-4">
            <x-input-label for="return_flight_number" :value="__('Vol Retour (e.g., AT1111)')" />
            <x-text-input id="return_flight_number" class="block mt-1 w-full" type="text" name="return_flight_number" :value="old('return_flight_number')" />
            <x-input-error :messages="$errors->get('return_flight_number')" class="mt-2" />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="return_departure_airport" :value="__('Aéroport Départ Retour (e.g., RAK, ORY)')" />
              <x-text-input id="return_departure_airport" class="block mt-1 w-full" type="text" name="return_departure_airport" :value="old('return_departure_airport')" placeholder="e.g., RAK, ORY" />
              <x-input-error :messages="$errors->get('return_departure_airport')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="return_arrival_airport" :value="__('Aéroport Arrivée Retour (e.g., CMN, CDG)')" />
              <x-text-input id="return_arrival_airport" class="block mt-1 w-full" type="text" name="return_arrival_airport" :value="old('return_arrival_airport')" placeholder="e.g., CMN, CDG" />
              <x-input-error :messages="$errors->get('return_arrival_airport')" class="mt-2" />
            </div>
          </div>

          <div class="mb-4">
            <x-input-label for="return_price_ttc" :value="__('Prix Retour TTC (MAD)')" />
            <x-text-input id="return_price_ttc" class="block mt-1 w-full" type="number" name="return_price_ttc" step="0.01" min="0" :value="old('return_price_ttc', 0)" />
            <x-input-error :messages="$errors->get('return_price_ttc')" class="mt-2" />
          </div>
        </div>

        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('flight_category');
            const returnSection = document.getElementById('return-flight-section');
            const returnFields = returnSection.querySelectorAll('input, select');
            
            function toggleReturnFields() {
              if (categorySelect.value === 'round_trip') {
                returnSection.style.display = 'block';
                returnFields.forEach(field => {
                  field.setAttribute('required', 'required');
                });
              } else {
                returnSection.style.display = 'none';
                returnFields.forEach(field => {
                  field.removeAttribute('required');
                  field.value = '';
                });
              }
            }
            
            categorySelect.addEventListener('change', toggleReturnFields);
            toggleReturnFields(); // Initial state
          });
        </script>

        {{-- eTicket Upload --}}
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-4">eTicket</h3>
          
          <div class="mb-4">
            <x-input-label for="eticket" :value="__('eTicket File')" />
            <x-text-input id="eticket" class="block mt-1 w-full" type="file" name="eticket" accept=".pdf,.jpg,.jpeg,.png,.webp" />
            <p class="mt-1 text-sm text-gray-500">PDF or images (JPG, PNG, WebP) - max 10MB</p>
            <x-input-error :messages="$errors->get('eticket')" class="mt-2" />
          </div>
        </div>

        {{-- Status and Payment --}}
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
              <x-input-label for="status" :value="__('Status')" />
              <select id="status" name="status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending (Not Paid)</option>
                <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Paid</option>
              </select>
              <x-input-error :messages="$errors->get('status')" class="mt-2" />
            </div>
            <div>
              <x-input-label for="payment_method" :value="__('Payment Method')" />
              <select id="payment_method" name="payment_method" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm">
                <option value="">Select Payment Method</option>
                <option value="wallet" {{ old('payment_method') === 'wallet' ? 'selected' : '' }}>Wallet</option>
                <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                <option value="both" {{ old('payment_method') === 'both' ? 'selected' : '' }}>Both (Wallet + Bank)</option>
              </select>
              <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
            </div>
          </div>
        </div>

        {{-- Beneficiary Selection --}}
        <div class="mb-6 p-4 border border-gray-300 rounded-md bg-blue-50">
          <h3 class="text-lg font-semibold mb-4">Beneficier (Beneficiary)</h3>
          
          <div class="mb-4">
            <x-input-label for="beneficiary_type" :value="__('Select Beneficiary')" />
            <select id="beneficiary_type" name="beneficiary_type" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required onchange="toggleClientEmail()">
              <option value="organizer" {{ old('beneficiary_type') === 'organizer' ? 'selected' : '' }}>Event Organizer</option>
              <option value="client" {{ old('beneficiary_type') === 'client' ? 'selected' : '' }}>Client</option>
            </select>
            <x-input-error :messages="$errors->get('beneficiary_type')" class="mt-2" />
          </div>

          <div id="client_email_field" class="mb-4" style="display: {{ old('beneficiary_type') === 'client' ? 'block' : 'none' }};">
            <x-input-label for="client_email" :value="__('Client Email')" />
            <x-text-input id="client_email" class="block mt-1 w-full" type="email" name="client_email" :value="old('client_email')" />
            <p class="mt-1 text-sm text-gray-500">A user account will be created with this email and credentials will be sent.</p>
            <x-input-error :messages="$errors->get('client_email')" class="mt-2" />
          </div>
        </div>

        <div class="flex items-center justify-end mt-4">
          <a href="{{ route('admin.flights.index', $accommodation) }}" 
             class="text-gray-600 hover:text-gray-900 mr-4">
            Cancel
          </a>
          <x-primary-button class="btn-logo-primary">
            Create Flight
          </x-primary-button>
        </div>
      </form>
    </x-shadcn.card-content>
  </x-shadcn.card>
</div>

@push('scripts')
<script>
  function toggleClientEmail() {
    const beneficiaryType = document.getElementById('beneficiary_type').value;
    const clientEmailField = document.getElementById('client_email_field');
    const clientEmailInput = document.getElementById('client_email');
    
    if (beneficiaryType === 'client') {
      clientEmailField.style.display = 'block';
      clientEmailInput.setAttribute('required', 'required');
    } else {
      clientEmailField.style.display = 'none';
      clientEmailInput.removeAttribute('required');
    }
  }

  // Initialize on page load
  document.addEventListener('DOMContentLoaded', function() {
    toggleClientEmail();
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }
  });
</script>
@endpush
@endsection

