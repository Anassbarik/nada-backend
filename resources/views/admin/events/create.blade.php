@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Create Event') }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('Back to Events') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
                    <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                        @csrf

                        @if(auth()->user()->isSuperAdmin() && $admins->count() > 0)
                            <div class="mt-6 mb-6 p-4 border border-gray-300 rounded-md bg-gray-50">
                                <x-input-label value="Sub-Permissions (Grant Access to Other Admins)" />
                                <p class="mt-1 mb-3 text-sm text-gray-600">
                                    Select admins who should be able to edit this event even if they didn't create it.
                                </p>
                                
                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    @foreach($admins as $admin)
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                name="sub_permissions[]" 
                                                value="{{ $admin->id }}"
                                                {{ in_array($admin->id, old('sub_permissions', [])) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">{{ $admin->name }} ({{ $admin->email }})</span>
                                        </label>
                                    @endforeach
                                </div>
                                
                                @if($admins->isEmpty())
                                    <p class="mt-2 text-sm text-gray-500">No regular admins available.</p>
                                @endif
                                
                                <x-input-error :messages="$errors->get('sub_permissions')" class="mt-2" />
                            </div>
                        @endif

                        @if(auth()->user()->isSuperAdmin() && $admins->count() > 0)
                            <div class="mt-6 mb-6 p-4 border border-gray-300 rounded-md bg-gray-50">
                                <x-input-label value="Flights Sub-Permissions (Grant Access to Flights Management)" />
                                <p class="mt-1 mb-3 text-sm text-gray-600">
                                    Select admins who should be able to manage flights for this accommodation.
                                </p>
                                
                                <div class="space-y-2 max-h-60 overflow-y-auto">
                                    @foreach($admins as $admin)
                                        <label class="flex items-center">
                                            <input 
                                                type="checkbox" 
                                                name="flights_sub_permissions[]" 
                                                value="{{ $admin->id }}"
                                                {{ in_array($admin->id, old('flights_sub_permissions', $flightsSubPermissions ?? [])) ? 'checked' : '' }}
                                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                            <span class="ml-2 text-sm text-gray-700">{{ $admin->name }} ({{ $admin->email }})</span>
                                        </label>
                                    @endforeach
                                </div>
                                
                                @if($admins->isEmpty())
                                    <p class="mt-2 text-sm text-gray-500">No regular admins available.</p>
                                @endif
                                
                                <x-input-error :messages="$errors->get('flights_sub_permissions')" class="mt-2" />
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="venue" :value="__('venue')" />
                            <x-text-input id="venue" class="block mt-1 w-full" type="text" name="venue" :value="old('venue')" placeholder="e.g., Dakhla" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('venue_hint') }}</p>
                            <x-input-error :messages="$errors->get('venue')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location')" placeholder="e.g., Convention Center, Street Address" />
                            <p class="mt-1 text-sm text-gray-500">Full address or location name</p>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="google_maps_url" :value="__('Google Maps URL')" />
                            <x-text-input id="google_maps_url" class="block mt-1 w-full" type="url" name="google_maps_url" :value="old('google_maps_url')" placeholder="https://maps.google.com/..." />
                            <p class="mt-1 text-sm text-gray-500">Link to Google Maps location</p>
                            <x-input-error :messages="$errors->get('google_maps_url')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="start_date" :value="__('start_date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date')" />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('end_date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date')" />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="website_url" :value="__('website_url')" />
                            <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="old('website_url')" placeholder="https://example.com" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('website_url_optional') }}</p>
                            <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="organizer_logo" :value="__('organizer_logo')" />
                            <x-text-input id="organizer_logo" class="block mt-1 w-full" type="file" name="organizer_logo" accept="image/jpeg,image/png,image/jpg" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('organizer_logo_hint') }}</p>
                            <x-input-error :messages="$errors->get('organizer_logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('description')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Legacy description field (optional)</p>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description_en" :value="__('Description (English)')" />
                            <textarea id="description_en" name="description_en" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description_en') }}</textarea>
                            <x-input-error :messages="$errors->get('description_en')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description_fr" :value="__('Description (Français)')" />
                            <textarea id="description_fr" name="description_fr" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description_fr') }}</textarea>
                            <x-input-error :messages="$errors->get('description_fr')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="logo" :value="__('logo')" />
                            <x-text-input id="logo" class="block mt-1 w-full" type="file" name="logo" accept="image/*" />
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="banner" :value="__('banner')" />
                            <x-text-input id="banner" class="block mt-1 w-full" type="file" name="banner" accept="image/*" />
                            <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                        </div>

                        <div class="mb-6 p-4 border border-gray-300 rounded-md bg-blue-50">
                            <h3 class="text-lg font-semibold mb-4">Organizer Information</h3>
                            
                            <div class="mb-4">
                                <x-input-label for="organizer_name" :value="__('Organizer Name')" />
                                <x-text-input id="organizer_name" class="block mt-1 w-full" type="text" name="organizer_name" :value="old('organizer_name')" required />
                                <p class="mt-1 text-sm text-gray-500">Full name of the event organizer</p>
                                <x-input-error :messages="$errors->get('organizer_name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="organizer_email" :value="__('Organizer Email')" />
                                <x-text-input id="organizer_email" class="block mt-1 w-full" type="email" name="organizer_email" :value="old('organizer_email')" required />
                                <p class="mt-1 text-sm text-gray-500">Email address for the organizer login. A password will be automatically generated and a PDF with credentials will be available for download.</p>
                                <x-input-error :messages="$errors->get('organizer_email')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="commission_percentage" :value="__('Commission Percentage')" />
                                <x-text-input id="commission_percentage" class="block mt-1 w-full" type="number" name="commission_percentage" :value="old('commission_percentage')" step="0.01" min="0" max="100" placeholder="e.g., 10.5" />
                                <p class="mt-1 text-sm text-gray-500">Commission percentage for the organizer (e.g., 10.5 for 10.5%). This will be calculated on every booking made for this event.</p>
                                <x-input-error :messages="$errors->get('commission_percentage')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('status')" />
                            <select id="status" name="status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>{{ __('draft') }}</option>
                                <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>{{ __('published') }}</option>
                                <option value="archived" {{ old('status') === 'archived' ? 'selected' : '' }}>{{ __('archived') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>

                        <div class="mb-6 p-4 border border-gray-300 rounded-md bg-gray-50">
                            <h3 class="text-lg font-semibold mb-4">Flight Price Visibility Settings</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="show_flight_prices_public" 
                                            value="1"
                                            {{ old('show_flight_prices_public', true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm font-medium text-gray-700">Show prices on events landing page and flight details page</span>
                                    </label>
                                    <p class="mt-1 ml-6 text-sm text-gray-500">Controls whether flight prices are visible to clients browsing the public events landing page and individual flight details pages</p>
                                    <x-input-error :messages="$errors->get('show_flight_prices_public')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="show_flight_prices_client_dashboard" 
                                            value="1"
                                            {{ old('show_flight_prices_client_dashboard', true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm font-medium text-gray-700">Show prices in client dashboard</span>
                                    </label>
                                    <p class="mt-1 ml-6 text-sm text-gray-500">Controls whether clients can see flight prices for their own bookings when logged into their dashboard</p>
                                    <x-input-error :messages="$errors->get('show_flight_prices_client_dashboard')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <label class="flex items-center">
                                        <input 
                                            type="checkbox" 
                                            name="show_flight_prices_organizer_dashboard" 
                                            value="1"
                                            {{ old('show_flight_prices_organizer_dashboard', true) ? 'checked' : '' }}
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm font-medium text-gray-700">Show prices in organizer dashboard</span>
                                    </label>
                                    <p class="mt-1 ml-6 text-sm text-gray-500">Controls whether organizers can see flight prices for flights in their events when viewing their dashboard</p>
                                    <x-input-error :messages="$errors->get('show_flight_prices_organizer_dashboard')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.events.index') }}" 
                               class="text-gray-600 hover:text-gray-900 mr-4"
                               data-livewire-ignore="true">{{ __('cancel') }}</a>
                            <x-primary-button class="btn-logo-primary">
                                {{ __('create') }}
                            </x-primary-button>
                        </div>
                    </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

