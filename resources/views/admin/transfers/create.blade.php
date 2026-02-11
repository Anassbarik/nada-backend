@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">Create Transfer</h1>
                <p class="text-gray-600 mt-1">{{ $accommodation->name }}</p>
            </div>
            <a href="{{ route('admin.transfers.index', $accommodation) }}"
                class="text-logo-link hover:underline inline-flex items-center">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Transfers
            </a>
        </div>

        <x-shadcn.card class="shadow-lg">
            <x-shadcn.card-content class="p-6">
                <form method="POST" action="{{ route('admin.transfers.store', $accommodation) }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Client Information --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Client Information</h3>

                        <div class="mb-4">
                            <x-input-label for="client_name" :value="__('Nom complet de client')" />
                            <x-text-input id="client_name" class="block mt-1 w-full" type="text" name="client_name"
                                :value="old('client_name')" required autofocus />
                            <x-input-error :messages="$errors->get('client_name')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="client_phone" :value="__('Téléphone')" />
                                <x-text-input id="client_phone" class="block mt-1 w-full" type="text" name="client_phone"
                                    :value="old('client_phone')" required />
                                <x-input-error :messages="$errors->get('client_phone')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="client_email" :value="__('Email (optional)')" />
                                <x-text-input id="client_email" class="block mt-1 w-full" type="email" name="client_email"
                                    :value="old('client_email')" />
                                <x-input-error :messages="$errors->get('client_email')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    {{-- Transfer Details --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Transfer Details</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="transfer_type" :value="__('Type de transfert')" />
                                <select id="transfer_type" name="transfer_type"
                                    class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="airport_hotel" {{ old('transfer_type') === 'airport_hotel' ? 'selected' : '' }}>Airport to Hotel</option>
                                    <option value="hotel_airport" {{ old('transfer_type') === 'hotel_airport' ? 'selected' : '' }}>Hotel to Airport</option>
                                    <option value="hotel_event" {{ old('transfer_type') === 'hotel_event' ? 'selected' : '' }}>Hotel to Event</option>
                                    <option value="event_hotel" {{ old('transfer_type') === 'event_hotel' ? 'selected' : '' }}>Event to Hotel</option>
                                    <option value="city_transfer" {{ old('transfer_type') === 'city_transfer' ? 'selected' : '' }}>City Transfer</option>
                                </select>
                                <x-input-error :messages="$errors->get('transfer_type')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="trip_type" :value="__('Type de trajet')" />
                                <select id="trip_type" name="trip_type"
                                    class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="one_way" {{ old('trip_type', 'one_way') === 'one_way' ? 'selected' : '' }}>
                                        Aller Simple (One Way)</option>
                                    <option value="round_trip" {{ old('trip_type') === 'round_trip' ? 'selected' : '' }}>
                                        Aller-Retour (Round Trip)</option>
                                </select>
                                <x-input-error :messages="$errors->get('trip_type')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="transfer_date" :value="__('Date départ')" />
                                <x-text-input id="transfer_date" class="block mt-1 w-full" type="date" name="transfer_date"
                                    :value="old('transfer_date')" required />
                                <x-input-error :messages="$errors->get('transfer_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="pickup_time" :value="__('Heure départ')" />
                                <x-text-input id="pickup_time" class="block mt-1 w-full" type="time" name="pickup_time"
                                    :value="old('pickup_time')" required />
                                <x-input-error :messages="$errors->get('pickup_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="pickup_location" :value="__('Lieu de départ (Pickup)')" />
                                <x-text-input id="pickup_location" class="block mt-1 w-full" type="text"
                                    name="pickup_location" :value="old('pickup_location')" required
                                    placeholder="e.g., CMN Airport" />
                                <x-input-error :messages="$errors->get('pickup_location')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="dropoff_location" :value="__('Lieu d\'arrivée (Dropoff)')" />
                                <x-text-input id="dropoff_location" class="block mt-1 w-full" type="text"
                                    name="dropoff_location" :value="old('dropoff_location')" required
                                    placeholder="e.g., Hyatt Regency" />
                                <x-input-error :messages="$errors->get('dropoff_location')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="flight_number" :value="__('Numéro de vol (Optional)')" />
                                <x-text-input id="flight_number" class="block mt-1 w-full" type="text" name="flight_number"
                                    :value="old('flight_number')" placeholder="e.g., AT222" />
                                <x-input-error :messages="$errors->get('flight_number')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="flight_time" :value="__('Heure de vol (Optional)')" />
                                <x-text-input id="flight_time" class="block mt-1 w-full" type="time" name="flight_time"
                                    :value="old('flight_time')" />
                                <x-input-error :messages="$errors->get('flight_time')" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <x-input-label for="vehicle_type_id" :value="__('Type de véhicule')" />
                                    <a href="{{ route('admin.vehicle-types.index') }}"
                                        class="text-xs text-blue-600 hover:underline flex items-center" target="_blank">
                                        <i data-lucide="settings" class="w-3 h-3 mr-1"></i>
                                        Gérer les véhicules
                                    </a>
                                </div>
                                <select id="vehicle_type_id" name="vehicle_type_id"
                                    class="block w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="">Sélectionner un véhicule</option>
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
                                <x-input-label for="passengers" :value="__('Nombre de passagers')" />
                                <x-text-input id="passengers" class="block mt-1 w-full" type="number" name="passengers"
                                    min="1" :value="old('passengers', 1)" required />
                                <p id="passenger-limit-msg" class="mt-1 text-xs text-gray-500 hidden"></p>
                                <x-input-error :messages="$errors->get('passengers')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="price" :value="__('Prix TTC (MAD)')" />
                            <x-text-input id="price" class="block mt-1 w-full" type="number" name="price" step="0.01"
                                min="0" :value="old('price', 0)" required />
                            <x-input-error :messages="$errors->get('price')" class="mt-2" />
                        </div>
                    </div>

                    {{-- Return Transfer (Conditional) --}}
                    <div class="mb-6 return-transfer-section" id="return-transfer-section" style="display: none;">
                        <h3 class="text-lg font-semibold mb-4">Return Transfer (Aller-Retour)</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="return_date" :value="__('Date retour')" />
                                <x-text-input id="return_date" class="block mt-1 w-full" type="date" name="return_date"
                                    :value="old('return_date')" />
                                <x-input-error :messages="$errors->get('return_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="return_time" :value="__('Heure retour')" />
                                <x-text-input id="return_time" class="block mt-1 w-full" type="time" name="return_time"
                                    :value="old('return_time')" />
                                <x-input-error :messages="$errors->get('return_time')" class="mt-2" />
                            </div>
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const tripTypeSelect = document.getElementById('trip_type');
                            const returnSection = document.getElementById('return-transfer-section');
                            const returnFields = returnSection.querySelectorAll('input, select');

                            function toggleReturnFields() {
                                if (tripTypeSelect.value === 'round_trip') {
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

                            tripTypeSelect.addEventListener('change', toggleReturnFields);
                            toggleReturnFields(); // Initial state
                        });
                    </script>

                    {{-- eTicket Upload --}}
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">eTicket / Voucher</h3>

                        <div class="mb-4">
                            <x-input-label for="eticket" :value="__('eTicket/Voucher File')" />
                            <x-text-input id="eticket" class="block mt-1 w-full" type="file" name="eticket"
                                accept=".pdf,.jpg,.jpeg,.png,.webp" />
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
                                <select id="status" name="status"
                                    class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                                    required>
                                    <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Pending (Not
                                        Paid)</option>
                                    <option value="paid" {{ old('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>Confirmed
                                    </option>
                                </select>
                                <x-input-error :messages="$errors->get('status')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="payment_method" :value="__('Payment Method')" />
                                <select id="payment_method" name="payment_method"
                                    class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm">
                                    <option value="">Select Payment Method</option>
                                    <option value="wallet" {{ old('payment_method') === 'wallet' ? 'selected' : '' }}>Wallet
                                    </option>
                                    <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank
                                        Transfer</option>
                                    <option value="both" {{ old('payment_method') === 'both' ? 'selected' : '' }}>Both (Wallet
                                        + Bank)</option>
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
                            <select id="beneficiary_type" name="beneficiary_type"
                                class="block mt-1 w-full bg-white text-gray-900 border-gray-300 rounded-md shadow-sm"
                                required onchange="toggleClientEmail()">
                                <option value="organizer" {{ old('beneficiary_type') === 'organizer' ? 'selected' : '' }}>
                                    Event Organizer</option>
                                <option value="client" {{ old('beneficiary_type') === 'client' ? 'selected' : '' }}>Client
                                </option>
                            </select>
                            <x-input-error :messages="$errors->get('beneficiary_type')" class="mt-2" />
                        </div>

                        {{-- Logic handled via script below --}}
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
                    {{ in_array($admin->id, old('transfers_sub_permissions', $transfersSubPermissions ?? [])) ? 'checked' : '' }}
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
                    {{ old('show_transfer_prices_public', $accommodation->show_transfer_prices_public ?? true) ? 'checked' : '' }}
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
                    {{ old('show_transfer_prices_client_dashboard', $accommodation->show_transfer_prices_client_dashboard ?? true) ? 'checked' : '' }}
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
                    {{ old('show_transfer_prices_organizer_dashboard', $accommodation->show_transfer_prices_organizer_dashboard ?? true) ? 'checked' : '' }}
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
                        <a href="{{ route('admin.transfers.index', $accommodation) }}"
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
                // Basic beneficiary logic similar to flights
            }

            document.addEventListener('DOMContentLoaded', function () {
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
                        limitMsg.textContent = `Capacité max: ${max} passagers, ${bags} bagages.`;
                        limitMsg.classList.remove('hidden');
                        passengersInput.setAttribute('max', max);
                    } else {
                        limitMsg.classList.add('hidden');
                        passengersInput.removeAttribute('max');
                    }
                }

                vehicleSelect.addEventListener('change', updateLimit);
                updateLimit(); // Initial state
            });
        </script>
    @endpush
@endsection