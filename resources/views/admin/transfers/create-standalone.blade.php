@extends('layouts.admin')

@section('content')
<div class="space-y-6">
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
      <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Create Transfer</h1>
      <p class="text-gray-600 mt-1">Create a new transfer and assign it to an event</p>
    </div>
    <a href="{{ route('admin.transfers.global-index') }}" class="text-logo-link hover:underline inline-flex items-center">
      <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
      Back to Transfers
    </a>
  </div>

  <x-shadcn.card class="shadow-lg">
    <x-shadcn.card-content class="p-6">
      <form method="POST" action="{{ route('admin.transfers.store-standalone') }}">
        @csrf

        {{-- Event Selection --}}
        <div class="mb-6">
          <h3 class="text-lg font-semibold mb-4">Event Selection</h3>
          
          <div class="mb-4">
            <x-input-label for="accommodation_id" :value="__('Event (Accommodation)')" />
            <select id="accommodation_id" name="accommodation_id" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
              <option value="">Select an Event</option>
              @foreach($accommodations as $accommodation)
                <option value="{{ $accommodation->id }}" {{ old('accommodation_id') == $accommodation->id ? 'selected' : '' }}>
                  {{ $accommodation->name }}
                </option>
              @endforeach
            </select>
            <x-input-error :messages="$errors->get('accommodation_id')" class="mt-2" />
          </div>
        </div>

        {{-- Beneficiary Selection --}}
        <div class="mb-6 p-4 border border-gray-300 rounded-md bg-blue-50">
            <h3 class="text-lg font-semibold mb-4">Beneficier (Beneficiary)</h3>

            <div class="mb-4">
                <x-input-label for="beneficiary_type" :value="__('Select Beneficiary')" />
                <select id="beneficiary_type" name="beneficiary_type"
                    class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                    required onchange="toggleClientEmail()">
                    <option value="organizer" {{ old('beneficiary_type', 'organizer') === 'organizer' ? 'selected' : '' }}>
                        Event Organizer</option>
                    <option value="client" {{ old('beneficiary_type') === 'client' ? 'selected' : '' }}>Client
                    </option>
                </select>
                <x-input-error :messages="$errors->get('beneficiary_type')" class="mt-2" />
            </div>
        </div>

        {{-- Client Information --}}
        <div id="client-info-section" class="mb-6" style="display: {{ old('beneficiary_type', 'organizer') === 'client' ? 'block' : 'none' }};">
          <h3 class="text-lg font-semibold mb-4">Client Information</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                  <x-input-label for="client_name" :value="__('Client Name')" />
                  <x-text-input id="client_name" class="block mt-1 w-full" type="text" name="client_name" :value="old('client_name')" autofocus />
                  <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
              </div>
              <div>
                  <x-input-label for="client_phone" :value="__('Client Phone')" />
                  <x-text-input id="client_phone" class="block mt-1 w-full" type="text" name="client_phone" :value="old('client_phone')" />
                  <x-input-error :messages="$errors->get('client_phone')" class="mt-2" />
              </div>
          </div>
          
          <div class="mb-4">
              <x-input-label for="client_email" :value="__('Client Email (Optional)')" />
              <x-text-input id="client_email" class="block mt-1 w-full" type="email" name="client_email" :value="old('client_email')" />
              <x-input-error :messages="$errors->get('client_email')" class="mt-2" />
          </div>
        </div>

        {{-- Transfer Details --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Transfer Details</h3>

            <div class="mb-4">
                <x-input-label for="transfer_type" :value="__('Transfer Type')" />
                <select id="transfer_type" name="transfer_type" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                    <option value="airport_hotel" {{ old('transfer_type') === 'airport_hotel' ? 'selected' : '' }}>Airport to Hotel</option>
                    <option value="hotel_airport" {{ old('transfer_type') === 'hotel_airport' ? 'selected' : '' }}>Hotel to Airport</option>
                    <option value="hotel_event" {{ old('transfer_type') === 'hotel_event' ? 'selected' : '' }}>Hotel to Event</option>
                    <option value="event_hotel" {{ old('transfer_type') === 'event_hotel' ? 'selected' : '' }}>Event to Hotel</option>
                    <option value="city_transfer" {{ old('transfer_type') === 'city_transfer' ? 'selected' : '' }}>City Transfer</option>
                </select>
                <x-input-error :messages="$errors->get('transfer_type')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <x-input-label for="pickup_location" :value="__('Pickup Location')" />
                    <x-text-input id="pickup_location" class="block mt-1 w-full" type="text" name="pickup_location" :value="old('pickup_location')" required />
                    <x-input-error :messages="$errors->get('pickup_location')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="dropoff_location" :value="__('Dropoff Location')" />
                    <x-text-input id="dropoff_location" class="block mt-1 w-full" type="text" name="dropoff_location" :value="old('dropoff_location')" required />
                    <x-input-error :messages="$errors->get('dropoff_location')" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <x-input-label for="pickup_date" :value="__('Pickup Date')" />
                    <x-text-input id="pickup_date" class="block mt-1 w-full" type="date" name="pickup_date" :value="old('pickup_date')" required />
                    <x-input-error :messages="$errors->get('pickup_date')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="pickup_time" :value="__('Pickup Time')" />
                    <x-text-input id="pickup_time" class="block mt-1 w-full" type="time" name="pickup_time" :value="old('pickup_time')" required />
                    <x-input-error :messages="$errors->get('pickup_time')" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <x-input-label for="vehicle_type_id" :value="__('Vehicle Type')" />
                        <a href="{{ route('admin.vehicle-types.index') }}"
                            class="text-xs text-blue-600 hover:underline flex items-center" target="_blank">
                            <i data-lucide="settings" class="w-3 h-3 mr-1"></i>
                            Gérer les véhicules
                        </a>
                    </div>
                    <select id="vehicle_type_id" name="vehicle_type_id"
                        class="block w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                        required>
                        <option value="">Select a Vehicle</option>
                        @foreach($vehicleTypes as $type)
                            <option value="{{ $type->id }}"
                                data-max-passengers="{{ $type->max_passengers }}"
                                data-max-luggages="{{ $type->max_luggages }}"
                                {{ old('vehicle_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} (Max: {{ $type->max_passengers }} pax, {{ $type->max_luggages }} bags)
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('vehicle_type_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="passengers" :value="__('Number of Passengers')" />
                    <x-text-input id="passengers" class="block mt-1 w-full" type="number" min="1" name="passengers" :value="old('passengers', 1)" required oninput="updateAdditionalPassengers()" />
                    <p id="passenger-limit-msg" class="mt-1 text-xs text-gray-500 hidden"></p>
                    <x-input-error :messages="$errors->get('passengers')" class="mt-2" />
                </div>
            </div>

            {{-- Additional Passengers Names --}}
            <div id="additional-passengers-container" class="mb-6 p-4 border border-dashed border-gray-300 rounded-md bg-gray-50 hidden">
                <h4 class="text-sm font-semibold mb-3 text-gray-700">Additional Passengers Information (Optional)</h4>
                <div id="additional-passengers-list" class="space-y-3">
                    {{-- Dynamic inputs will be injected here --}}
                </div>
            </div>
            
            <div class="mb-4">
                <x-input-label for="flight_number" :value="__('Flight Number (Optional)')" />
                <x-text-input id="flight_number" class="block mt-1 w-full" type="text" name="flight_number" :value="old('flight_number')" placeholder="If airport transfer" />
                <x-input-error :messages="$errors->get('flight_number')" class="mt-2" />
            </div>

            <div class="mb-4">
                <x-input-label for="notes" :value="__('Notes (Optional)')" />
                <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
            </div>
        </div>

        {{-- Driver Information --}}
        <div class="p-4 border border-gray-300 rounded-md bg-gray-50 mb-6">
            <h3 class="text-lg font-semibold mb-4">Driver Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="driver_name" :value="__('Driver Name')" />
                    <x-text-input id="driver_name" class="block mt-1 w-full" type="text" name="driver_name"
                        :value="old('driver_name')" placeholder="e.g. Ahmed" />
                    <x-input-error :messages="$errors->get('driver_name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="driver_phone" :value="__('Driver Phone')" />
                    <x-text-input id="driver_phone" class="block mt-1 w-full" type="text" name="driver_phone"
                        :value="old('driver_phone')" placeholder="e.g. +212 600..." />
                    <x-input-error :messages="$errors->get('driver_phone')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Payment Information --}}
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <x-input-label for="price" :value="__('Price (MAD)')" />
                    <x-text-input id="price" class="block mt-1 w-full" type="number" step="0.01" min="0" name="price" :value="old('price', 0)" required />
                    <x-input-error :messages="$errors->get('price')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="status" :value="__('Status')" />
                    <select id="status" name="status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="completed" {{ old('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <x-input-label for="payment_status" :value="__('Payment Status')" />
                    <select id="payment_status" name="payment_status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm" required>
                        <option value="unpaid" {{ old('payment_status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="paid" {{ old('payment_status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                    <x-input-error :messages="$errors->get('payment_status')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="payment_method" :value="__('Payment Method')" />
                    <select id="payment_method" name="payment_method" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm">
                        <option value="">Select Payment Method</option>
                        <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                        <option value="bank_transfer" {{ old('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                        <option value="wallet" {{ old('payment_method') === 'wallet' ? 'selected' : '' }}>Wallet</option>
                    </select>
                    <x-input-error :messages="$errors->get('payment_method')" class="mt-2" />
                </div>
            </div>
        </div>


        {{-- Transfers Sub-Permissions --}}
        @if(auth()->user()->isSuperAdmin() && isset($admins) && $admins->count() > 0)
          <div class="mb-6 p-4 border border-gray-300 rounded-md bg-gray-50">
            <x-input-label value="Transfers Sub-Permissions (Grant Access to Transfers Management)" />
            <p class="mt-1 mb-3 text-sm text-gray-600">
              Select admins who should be able to manage transfers for this accommodation.
            </p>
            
            <div class="space-y-2 max-h-60 overflow-y-auto">
              @foreach($admins as $admin)
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    name="transfers_sub_permissions[]" 
                    value="{{ $admin->id }}"
                    {{ in_array($admin->id, old('transfers_sub_permissions', [])) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <span class="ml-2 text-sm text-gray-700">{{ $admin->name }} ({{ $admin->email }})</span>
                </label>
              @endforeach
            </div>
            
            @if($admins->isEmpty())
              <p class="mt-2 text-sm text-gray-500">No regular admins available.</p>
            @endif
            
            <x-input-error :messages="$errors->get('transfers_sub_permissions')" class="mt-2" />
          </div>
        @endif

        {{-- Transfer Price Visibility --}}
        <div class="mb-6">
          <div class="p-4 border border-gray-300 rounded-md bg-gray-50">
            <h3 class="text-lg font-semibold mb-4">Transfer Price Visibility Settings</h3>
            
            <div class="space-y-4">
              <div>
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    name="show_transfer_prices_public" 
                    value="1"
                    {{ old('show_transfer_prices_public', true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <span class="ml-2 text-sm font-medium text-gray-700">Show prices on events landing page and transfer details page</span>
                </label>
                <p class="mt-1 ml-6 text-sm text-gray-500">Controls whether transfer prices are visible to clients browsing the public events landing page and individual details pages</p>
                <x-input-error :messages="$errors->get('show_transfer_prices_public')" class="mt-2" />
              </div>
              
              <div>
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    name="show_transfer_prices_client_dashboard" 
                    value="1"
                    {{ old('show_transfer_prices_client_dashboard', true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <span class="ml-2 text-sm font-medium text-gray-700">Show prices in client dashboard</span>
                </label>
                <p class="mt-1 ml-6 text-sm text-gray-500">Controls whether clients can see transfer prices for their own bookings when logged into their dashboard</p>
                <x-input-error :messages="$errors->get('show_transfer_prices_client_dashboard')" class="mt-2" />
              </div>
              
              <div>
                <label class="flex items-center">
                  <input 
                    type="checkbox" 
                    name="show_transfer_prices_organizer_dashboard" 
                    value="1"
                    {{ old('show_transfer_prices_organizer_dashboard', true) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                  <span class="ml-2 text-sm font-medium text-gray-700">Show prices in organizer dashboard</span>
                </label>
                <p class="mt-1 ml-6 text-sm text-gray-500">Controls whether organizers can see transfer prices for transfers in their events when viewing their dashboard</p>
                <x-input-error :messages="$errors->get('show_transfer_prices_organizer_dashboard')" class="mt-2" />
              </div>
            </div>
          </div>
        </div>

        <div class="flex items-center justify-end mt-4">
          <a href="{{ route('admin.transfers.global-index') }}" 
             class="text-gray-600 hover:text-gray-900 mr-4">
            Cancel
          </a>
          <x-primary-button class="btn-logo-primary">
            Create Transfer
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
      const clientInfoSection = document.getElementById('client-info-section');
      const clientInputs = clientInfoSection.querySelectorAll('input');

      if (beneficiaryType === 'client') {
          clientInfoSection.style.display = 'block';
          clientInputs.forEach(input => {
              if (input.id !== 'client_email') {
                  input.setAttribute('required', 'required');
              }
          });
      } else {
          clientInfoSection.style.display = 'none';
          clientInputs.forEach(input => {
              input.removeAttribute('required');
          });
      }
  }

  document.addEventListener('DOMContentLoaded', function() {
    toggleClientEmail();
    if (typeof lucide !== 'undefined') {
      lucide.createIcons();
    }

    const vehicleSelect = document.getElementById('vehicle_type_id');
    const passengersInput = document.getElementById('passengers');
    const limitMsg = document.getElementById('passenger-limit-msg');

    function updateLimit() {
        const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
        if (selected && selected.value) {
            const max = selected.getAttribute('data-max-passengers');
            const bags = selected.getAttribute('data-max-luggages');
            limitMsg.textContent = `Capacity max: ${max} passengers, ${bags} luggages.`;
            limitMsg.classList.remove('hidden');
            passengersInput.setAttribute('max', max);
        } else {
            limitMsg.classList.add('hidden');
            passengersInput.removeAttribute('max');
        }
        // Re-calculate additional passengers when limit changes
        if (typeof updateAdditionalPassengers === 'function') {
            updateAdditionalPassengers();
        }
    }

    vehicleSelect.addEventListener('change', updateLimit);
    updateLimit(); // Initial state
  });

  function updateAdditionalPassengers() {
      const passengersInput = document.getElementById('passengers');
      const vehicleSelect = document.getElementById('vehicle_type_id');
      const container = document.getElementById('additional-passengers-container');
      const list = document.getElementById('additional-passengers-list');
      
      const enteredCount = parseInt(passengersInput.value) || 1;
      
      // Get max passengers from selected vehicle
      const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
      const maxPassengers = selectedOption && selectedOption.value 
          ? parseInt(selectedOption.getAttribute('data-max-passengers')) 
          : enteredCount; // Default to entered count if no vehicle selected

      // Only render inputs for passengers that fit in the vehicle
      const effectiveCount = Math.min(enteredCount, maxPassengers);
      const neededCount = Math.max(0, effectiveCount - 1);
      
      const existingInputs = list.querySelectorAll('div[data-passenger-index]');
      const existingCount = existingInputs.length;

      if (neededCount > 0) {
          container.classList.remove('hidden');
          
          // Add missing inputs
          if (neededCount > existingCount) {
              for (let i = existingCount; i < neededCount; i++) {
                  const index = i;
                  const div = document.createElement('div');
                  div.setAttribute('data-passenger-index', index);
                  div.className = 'flex flex-col space-y-1';
                  
                  const label = document.createElement('label');
                  label.className = 'text-xs font-medium text-gray-600';
                  label.textContent = `Passenger ${index + 2} Name`;
                  
                  const input = document.createElement('input');
                  input.type = 'text';
                  input.name = `additional_passengers[${index}]`;
                  input.className = 'block w-full border-gray-300 rounded-md shadow-sm text-sm p-2';
                  input.placeholder = 'Full Name';
                  
                  div.appendChild(label);
                  div.appendChild(input);
                  list.appendChild(div);
              }
          } 
          // Remove extra inputs
          else if (neededCount < existingCount) {
              for (let i = existingCount - 1; i >= neededCount; i--) {
                  const lastInput = list.querySelector(`div[data-passenger-index="${i}"]`);
                  if (lastInput) lastInput.remove();
              }
          }
      } else {
          container.classList.add('hidden');
          list.innerHTML = '';
      }
  }

  // Initial call on load to handle old input
  document.addEventListener('DOMContentLoaded', function() {
      updateAdditionalPassengers();
      
      @if(old('additional_passengers'))
          const list = document.getElementById('additional-passengers-list');
          const oldData = @json(old('additional_passengers'));
          const inputs = list.querySelectorAll('input[name^="additional_passengers"]');
          inputs.forEach((input, index) => {
              if (oldData[index]) input.value = oldData[index];
          });
      @endif
  });
</script>
@endpush
@endsection
