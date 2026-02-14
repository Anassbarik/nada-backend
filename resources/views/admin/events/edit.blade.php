@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold break-words">{{ __('Edit Event') }}</h1>
    </div>

    <div class="mb-4">
        <a href="{{ route('admin.events.index') }}" class="text-logo-link hover:underline inline-flex items-center">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
            {{ __('Back to Events') }}
        </a>
    </div>

    <x-shadcn.card class="shadow-lg">
        <x-shadcn.card-content class="p-6">
                    <form method="POST" action="{{ route('admin.events.update', $event) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

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
                                                {{ in_array($admin->id, old('sub_permissions', $subPermissions ?? [])) ? 'checked' : '' }}
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

                            <div class="mb-6 p-4 border border-gray-300 rounded-md bg-gray-50">
                                <x-input-label value="Flights Sub-Permissions (Grant Access to Flights Management)" />
                                <p class="mt-1 mb-3 text-sm text-gray-600">
                                    Select admins who should be able to manage flights for this event. These admins will be able to view, create, edit, and delete flights for this event even if they don't have the main flights permissions.
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



                        <div class="mb-6 p-4 border border-gray-300 rounded-md bg-gray-50">
                            <h3 class="text-lg font-semibold mb-4">{{ __('Event Components') }}</h3>
                            <p class="mb-4 text-sm text-gray-600">Select which components are available for this event.</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="has_hotel_package" value="1" {{ old('has_hotel_package', $event->has_hotel_package) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-gray-700 font-medium">{{ __('Hotel Package') }}</span>
                                </label>
                                
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="has_flights" value="1" {{ old('has_flights', $event->has_flights) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-gray-700 font-medium">{{ __('Flights') }}</span>
                                </label>
                                
                                <label class="flex items-center space-x-3">
                                    <input type="checkbox" name="has_transfers" value="1" {{ old('has_transfers', $event->has_transfers) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <span class="text-gray-700 font-medium">{{ __('Transfers') }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $event->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="venue" :value="__('venue')" />
                            <x-text-input id="venue" class="block mt-1 w-full" type="text" name="venue" :value="old('venue', $event->venue)" placeholder="e.g., Dakhla" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('venue_hint') }}</p>
                            <x-input-error :messages="$errors->get('venue')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="location" :value="__('Location')" />
                            <x-text-input id="location" class="block mt-1 w-full" type="text" name="location" :value="old('location', $event->location)" placeholder="e.g., Convention Center, Street Address" />
                            <p class="mt-1 text-sm text-gray-500">Full address or location name</p>
                            <x-input-error :messages="$errors->get('location')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="google_maps_url" :value="__('Google Maps URL')" />
                            <x-text-input id="google_maps_url" class="block mt-1 w-full" type="url" name="google_maps_url" :value="old('google_maps_url', $event->google_maps_url)" placeholder="https://maps.google.com/..." />
                            <p class="mt-1 text-sm text-gray-500">Link to Google Maps location</p>
                            <x-input-error :messages="$errors->get('google_maps_url')" class="mt-2" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="start_date" :value="__('start_date')" />
                                <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date" :value="old('start_date', $event->start_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="end_date" :value="__('end_date')" />
                                <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date" :value="old('end_date', $event->end_date?->format('Y-m-d'))" />
                                <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="website_url" :value="__('website_url')" />
                            <x-text-input id="website_url" class="block mt-1 w-full" type="url" name="website_url" :value="old('website_url', $event->website_url)" placeholder="https://example.com" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('website_url_optional') }}</p>
                            <x-input-error :messages="$errors->get('website_url')" class="mt-2" />
                        </div>

                        @if($event->organizer_logo)
                            <div class="mb-4">
                                <x-input-label :value="__('current_organizer_logo')" />
                                <img src="{{ $event->organizer_logo_url }}" alt="Organizer Logo" class="mt-2 h-16 w-auto object-contain rounded">
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="organizer_logo" :value="__('organizer_logo')" />
                            <x-text-input id="organizer_logo" class="block mt-1 w-full" type="file" name="organizer_logo" accept="image/jpeg,image/png,image/jpg" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('organizer_logo_hint_update') }}</p>
                            <x-input-error :messages="$errors->get('organizer_logo')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('description')" />
                            <textarea id="description" name="description" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description', $event->description) }}</textarea>
                            <p class="mt-1 text-sm text-gray-500">Legacy description field (optional)</p>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description_en" :value="__('Description (English)')" />
                            <textarea id="description_en" name="description_en" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description_en', $event->description_en) }}</textarea>
                            <x-input-error :messages="$errors->get('description_en')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description_fr" :value="__('Description (Français)')" />
                            <textarea id="description_fr" name="description_fr" rows="4" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description_fr', $event->description_fr) }}</textarea>
                            <x-input-error :messages="$errors->get('description_fr')" class="mt-2" />
                        </div>

                        @if($event->logo_path)
                            <div class="mb-4">
                                <x-input-label :value="__('Current Logo')" />
                                <img src="{{ $event->logo_url }}" alt="Logo" class="mt-2 h-20 w-20 object-cover rounded">
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="logo" :value="__('Logo')" />
                            <x-text-input id="logo" class="block mt-1 w-full" type="file" name="logo" accept="image/*" />
                            <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                        </div>

                        @if($event->banner_path)
                            <div class="mb-4">
                                <x-input-label :value="__('Current Banner')" />
                                <img src="{{ $event->banner_url }}" alt="Banner" class="mt-2 h-32 w-full object-cover rounded">
                            </div>
                        @endif

                        <div class="mb-4">
                            <x-input-label for="banner" :value="__('Banner')" />
                            <x-text-input id="banner" class="block mt-1 w-full" type="file" name="banner" accept="image/*" />
                            <x-input-error :messages="$errors->get('banner')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center justify-between">
                                <x-input-label :value="__('Content Pages')" />
                                <a href="{{ route('admin.events.content.index', $event) }}" class="text-logo-link hover:underline text-sm px-2">
                                    Manage Content Pages →
                                </a>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">Manage Conditions, Informations, and FAQ pages separately.</p>
                        </div>

                        <div class="mb-6 p-4 border border-gray-300 rounded-md bg-blue-50">
                            <h3 class="text-lg font-semibold mb-4">Organizer Information</h3>
                            
                            @if($event->organizer)
                                <div class="mb-4 p-3 bg-white rounded border border-gray-200">
                                    <p class="text-sm text-gray-600 mb-2">Current Organizer:</p>
                                    <div class="space-y-1">
                                        <div>
                                            <span class="font-semibold">Name:</span> {{ $event->organizer->name }}
                                        </div>
                                        <div>
                                            <span class="font-semibold">Email:</span> {{ $event->organizer->email }}
                                        </div>
                                    </div>
                                    @if(auth()->user()->isSuperAdmin())
                                        <div class="mt-3">
                                            <a href="{{ route('admin.organizers.download-credentials', $event->organizer) }}" 
                                               class="text-logo-link hover:underline inline-flex items-center text-sm">
                                                <i data-lucide="download" class="w-4 h-4 mr-2"></i>
                                                Download Organizer Credentials PDF
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-600 mb-4">Update organizer information below. A new password will be generated if the email is changed.</p>
                            @else
                                <p class="text-sm text-gray-600 mb-4">Create an organizer account for this event. A password will be automatically generated and a PDF with credentials will be available for download.</p>
                            @endif
                            
                            <div class="mb-4">
                                <x-input-label for="organizer_name" :value="__('Organizer Name')" />
                                @if(!$event->organizer)
                                    <x-text-input id="organizer_name" class="block mt-1 w-full" type="text" name="organizer_name" :value="old('organizer_name')" required />
                                @else
                                    <x-text-input id="organizer_name" class="block mt-1 w-full" type="text" name="organizer_name" :value="old('organizer_name', $event->organizer->name)" />
                                @endif
                                <p class="mt-1 text-sm text-gray-500">Full name of the event organizer</p>
                                <x-input-error :messages="$errors->get('organizer_name')" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <x-input-label for="organizer_email" :value="__('Organizer Email')" />
                                @if(!$event->organizer)
                                    <x-text-input id="organizer_email" class="block mt-1 w-full" type="email" name="organizer_email" :value="old('organizer_email')" required />
                                @else
                                    <x-text-input id="organizer_email" class="block mt-1 w-full" type="email" name="organizer_email" :value="old('organizer_email', $event->organizer->email)" />
                                @endif
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($event->organizer)
                                        Email address for the organizer login. If changed, a new password will be generated and a new credentials PDF will be created.
                                    @else
                                        Email address for the organizer login. A password will be automatically generated and a PDF with credentials will be available for download.
                                    @endif
                                </p>
                                <x-input-error :messages="$errors->get('organizer_email')" class="mt-2" />
                            </div>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="commission_percentage" :value="__('Commission Percentage')" />
                            <x-text-input id="commission_percentage" class="block mt-1 w-full" type="number" name="commission_percentage" :value="old('commission_percentage', $event->commission_percentage)" step="0.01" min="0" max="100" placeholder="e.g., 10.5" />
                            <p class="mt-1 text-sm text-gray-500">Commission percentage for the organizer (e.g., 10.5 for 10.5%). This will be calculated on every booking made for this event.</p>
                            <x-input-error :messages="$errors->get('commission_percentage')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="status" :value="__('Status')" />
                            <select id="status" name="status" class="block mt-1 w-full bg-white text-gray-900 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
                                <option value="draft" {{ old('status', $event->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="published" {{ old('status', $event->status) === 'published' ? 'selected' : '' }}>Published</option>
                                <option value="archived" {{ old('status', $event->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('admin.events.index') }}" 
                               class="text-gray-600 hover:text-gray-900 mr-4"
                               data-livewire-ignore="true">{{ __('cancel') }}</a>
                            <x-primary-button class="btn-logo-primary">
                                {{ __('Update Event') }}
                            </x-primary-button>
                        </div>
                    </form>
        </x-shadcn.card-content>
    </x-shadcn.card>
</div>
@endsection

