@props([])

<header class="bg-white border-b border-gray-200 shadow-sm">
    <div class="px-6 py-4 flex items-center justify-end">
        {{-- Right Side: Language Switcher + Profile --}}
        <div class="flex items-center gap-4">
            {{-- Language Switcher --}}
            <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1">
                @php
                    $currentQuery = request()->query();
                    unset($currentQuery['lang']);
                    $enQuery = array_merge($currentQuery, ['lang' => 'en']);
                    $frQuery = array_merge($currentQuery, ['lang' => 'fr']);
                    $baseUrl = request()->url();
                    $enUrl = $baseUrl . '?' . http_build_query($enQuery);
                    $frUrl = $baseUrl . '?' . http_build_query($frQuery);
                @endphp
                
                <a href="{{ $enUrl }}" 
                   class="flex items-center gap-2 px-3 py-2 rounded-md transition-all duration-200
                          {{ app()->getLocale() == 'en' 
                             ? 'bg-white shadow-sm border border-gray-200' 
                             : 'hover:bg-gray-100' }}">
                    <img src="{{ asset('assets/english.png') }}" 
                         alt="English" 
                         class="w-5 h-5 object-contain">
                    <span class="text-sm font-medium {{ app()->getLocale() == 'en' ? 'text-gray-900' : 'text-gray-600' }}">EN</span>
                </a>
                
                <a href="{{ $frUrl }}" 
                   class="flex items-center gap-2 px-3 py-2 rounded-md transition-all duration-200
                          {{ app()->getLocale() == 'fr' 
                             ? 'bg-white shadow-sm border border-gray-200' 
                             : 'hover:bg-gray-100' }}">
                    <img src="{{ asset('assets/french.png') }}" 
                         alt="Français" 
                         class="w-5 h-5 object-contain">
                    <span class="text-sm font-medium {{ app()->getLocale() == 'fr' ? 'text-gray-900' : 'text-gray-600' }}">FR</span>
                </a>
            </div>

            {{-- Profile Dropdown --}}
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="flex items-center gap-3 px-4 py-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2" style="--tw-ring-color: #00adf1;">
                        <div class="flex items-center justify-center w-8 h-8 rounded-full text-white font-semibold text-sm" style="background-color: #00adf1;">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                            <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                        </div>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500 ml-3"></i>
                    </button>
                </x-slot>

                <x-slot name="content">
                    <x-dropdown-link :href="route('profile.edit')" class="flex items-center gap-2">
                        <i data-lucide="user" class="w-4 h-4"></i>
                        {{ __('Profile') }}
                    </x-dropdown-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();"
                                class="flex items-center gap-2 text-red-600 hover:text-red-700">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            {{ __('Log Out') }}
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </div>
</header>

