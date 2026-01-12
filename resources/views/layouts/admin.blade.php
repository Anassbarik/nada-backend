<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- CSP header for future Sanctum token security (XSS/JS protection) --}}
    {{-- TODO: Configure CSP properly when implementing Sanctum - currently disabled to allow Vite dev server --}}
    {{-- <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com http://localhost:* ws://localhost:*; style-src 'self' 'unsafe-inline' https://fonts.bunny.net http://localhost:*; font-src 'self' https://fonts.bunny.net data:; img-src 'self' data: https:; connect-src 'self' http://localhost:* ws://localhost:*;"> --}}

    <title>Admin - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --logo-yellow: #f7cb00;
            --logo-orange: #ea5d25;
            --logo-dark-pink: #b71354;
            --logo-pink: #eb0b69;
            --logo-purple: #8c1790;
            --logo-cyan: #00adf1;
            --logo-teal: #37ac9c;
            --logo-green: #83ce2f;
        }
        .btn-logo-primary {
            background-color: #00adf1;
            color: white;
        }
        .btn-logo-primary:hover {
            background-color: #0099d8;
        }
        .text-logo-link {
            color: #00adf1;
        }
        .text-logo-link:hover {
            color: #0099d8;
        }
        /* Responsive text and overflow handling */
        @media (max-width: 640px) {
            body {
                font-size: 14px;
            }
            table {
                font-size: 12px;
            }
        }
        /* Ensure tables don't overflow on mobile */
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }
        /* Prevent text overflow in cells */
        td, th {
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        /* Ensure all form inputs have white backgrounds */
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        input[type="datetime-local"],
        input[type="search"],
        input[type="tel"],
        input[type="url"],
        select,
        textarea {
            background-color: white !important;
            color: #111827 !important;
        }
        input:disabled,
        select:disabled,
        textarea:disabled {
            background-color: #f3f4f6 !important;
            color: #6b7280 !important;
        }
        /* Text ellipsis for 2 lines */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            max-height: 3em; /* 2 lines * 1.5 line-height */
        }
    </style>
</head>
<body class="bg-gray-50" x-data="{ sidebarOpen: false }">
    <div class="flex h-screen">
        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 bg-gray-600 bg-opacity-75 z-40 lg:hidden"></div>
        
        {{-- Modern Sidebar --}}
        <aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
               class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-white border-r shadow-lg flex flex-col transform transition-transform duration-300 ease-in-out lg:translate-x-0">
            <div class="p-6 border-b bg-white flex items-center justify-between">
                <div class="bg-gray-100 rounded-lg p-3 flex items-center justify-center flex-1">
                    <img src="{{ asset('assets/logo-seminaireexpo.png') }}" 
                         alt="SeminairExpo Logo" 
                         class="h-10 w-auto object-contain">
                </div>
                {{-- Close button for mobile --}}
                <button @click="sidebarOpen = false" 
                        class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 ml-2">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('dashboard'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('dashboard'))
                          style="background: linear-gradient(135deg, rgba(0, 173, 241, 0.15) 0%, rgba(0, 173, 241, 0.05) 100%); color: #00adf1; border: 2px solid rgba(0, 173, 241, 0.3); box-shadow: 0 4px 12px rgba(0, 173, 241, 0.15);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="layout-dashboard" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('dashboard')) color: #00adf1; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('dashboard'))
                            <div class="absolute inset-0 bg-blue-100 rounded-full blur-sm opacity-50"></div>
                        @endif
                    </div>
                    <span>{{ __('Overview') }}</span>
                    @if(request()->routeIs('dashboard'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: #00adf1;"></div>
                            <div class="w-1 h-1 rounded-full opacity-60" style="background-color: #00adf1;"></div>
                        </div>
                    @endif
                </a>
                
                <a href="{{ route('admin.events.index') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('admin.events.*'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.events.*'))
                          style="background: linear-gradient(135deg, rgba(131, 206, 47, 0.15) 0%, rgba(131, 206, 47, 0.05) 100%); color: #83ce2f; border: 2px solid rgba(131, 206, 47, 0.3); box-shadow: 0 4px 12px rgba(131, 206, 47, 0.15);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="calendar" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('admin.events.*')) color: #83ce2f; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('admin.events.*'))
                            <div class="absolute inset-0 bg-green-100 rounded-full blur-sm opacity-50"></div>
                        @endif
                    </div>
                    <span>Events</span>
                    @if(request()->routeIs('admin.events.*'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: #83ce2f;"></div>
                            <div class="w-1 h-1 rounded-full opacity-60" style="background-color: #83ce2f;"></div>
                        </div>
                    @endif
                </a>
                
                <a href="{{ route('admin.bookings.index') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('admin.bookings.*'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.bookings.*'))
                          style="background: linear-gradient(135deg, rgba(140, 23, 144, 0.15) 0%, rgba(140, 23, 144, 0.05) 100%); color: #8c1790; border: 2px solid rgba(140, 23, 144, 0.3); box-shadow: 0 4px 12px rgba(140, 23, 144, 0.15);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="file-text" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('admin.bookings.*')) color: #8c1790; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('admin.bookings.*'))
                            <div class="absolute inset-0 bg-purple-100 rounded-full blur-sm opacity-50"></div>
                        @endif
                    </div>
                    <span>Bookings</span>
                    @if(request()->routeIs('admin.bookings.*'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: #8c1790;"></div>
                            <div class="w-1 h-1 rounded-full opacity-60" style="background-color: #8c1790;"></div>
                        </div>
                    @endif
                </a>

                <a href="{{ route('admin.invoices.index') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('admin.invoices.*'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.invoices.*'))
                          style="background: linear-gradient(135deg, rgba(234, 93, 37, 0.15) 0%, rgba(234, 93, 37, 0.05) 100%); color: #ea5d25; border: 2px solid rgba(234, 93, 37, 0.3); box-shadow: 0 4px 12px rgba(234, 93, 37, 0.15);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="receipt" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('admin.invoices.*')) color: #ea5d25; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('admin.invoices.*'))
                            <div class="absolute inset-0 bg-orange-100 rounded-full blur-sm opacity-50"></div>
                        @endif
                    </div>
                    <span>Invoices</span>
                    @if(request()->routeIs('admin.invoices.*'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: #ea5d25;"></div>
                            <div class="w-1 h-1 rounded-full opacity-60" style="background-color: #ea5d25;"></div>
                        </div>
                    @endif
                </a>

                @if(auth()->user()?->isSuperAdmin())
                <a href="{{ route('admin.newsletter.index') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('admin.newsletter.*'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.newsletter.*'))
                          style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0.05) 100%); color: #3b82f6; border: 2px solid rgba(59, 130, 246, 0.3); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="mail" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('admin.newsletter.*')) color: #3b82f6; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('admin.newsletter.*'))
                            <div class="absolute inset-0 bg-blue-100 rounded-full blur-sm opacity-50"></div>
                        @endif
                    </div>
                    <span>{{ __('Newsletter') }}</span>
                    @if(request()->routeIs('admin.newsletter.*'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: #3b82f6;"></div>
                            <div class="w-1 h-1 rounded-full opacity-60" style="background-color: #3b82f6;"></div>
                        </div>
                    @endif
                </a>
                @endif

                @can('viewAny', App\Models\User::class)
                <a href="{{ route('admin.admins.index') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('admin.admins.*'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.admins.*'))
                          style="background: linear-gradient(135deg, rgba(183, 19, 84, 0.15) 0%, rgba(183, 19, 84, 0.05) 100%); color: #b71354; border: 2px solid rgba(183, 19, 84, 0.3); box-shadow: 0 4px 12px rgba(183, 19, 84, 0.15);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="users" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('admin.admins.*')) color: #b71354; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('admin.admins.*'))
                            <div class="absolute inset-0 bg-pink-100 rounded-full blur-sm opacity-50"></div>
                        @endif
                    </div>
                    <span>Admins</span>
                    @if(request()->routeIs('admin.admins.*'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background-color: #b71354;"></div>
                            <div class="w-1 h-1 rounded-full opacity-60" style="background-color: #b71354;"></div>
                        </div>
                    @endif
                </a>
                @endcan

                @if(auth()->user()?->isSuperAdmin())
                <a href="{{ route('admin.logs.index') }}" 
                   @click="sidebarOpen = false"
                   class="group relative flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200
                          @if(request()->routeIs('admin.logs.*'))
                            font-semibold shadow-lg
                          @else
                            hover:bg-gray-50 text-gray-700 hover:text-gray-900
                          @endif"
                          @if(request()->routeIs('admin.logs.*'))
                          style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.10) 0%, rgba(0, 0, 0, 0.03) 100%); color: #111827; border: 2px solid rgba(17, 24, 39, 0.15); box-shadow: 0 4px 12px rgba(17, 24, 39, 0.08);"
                          @endif>
                    <div class="relative">
                        <i data-lucide="scroll-text" class="w-5 h-5 transition-colors relative z-10" style="@if(request()->routeIs('admin.logs.*')) color: #111827; @else color: #6b7280; @endif"></i>
                        @if(request()->routeIs('admin.logs.*'))
                            <div class="absolute inset-0 bg-gray-200 rounded-full blur-sm opacity-40"></div>
                        @endif
                    </div>
                    <span>{{ __('Logs') }}</span>
                    @if(request()->routeIs('admin.logs.*'))
                        <div class="ml-auto flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full bg-gray-900"></div>
                            <div class="w-1 h-1 rounded-full opacity-60 bg-gray-900"></div>
                        </div>
                    @endif
                </a>
                @endif
            </nav>
        </aside>
        
        {{-- Main Content Area with Header --}}
        <div class="flex-1 flex flex-col overflow-hidden w-full lg:w-auto">
            {{-- Top Header --}}
            <header class="bg-white border-b border-gray-200 shadow-sm">
                <div class="px-4 sm:px-6 py-4 flex items-center justify-between lg:justify-end">
                    {{-- Mobile Menu Button --}}
                    <button @click="sidebarOpen = !sidebarOpen" 
                            class="lg:hidden p-2 rounded-md text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                    
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
            
            <main class="flex-1 overflow-auto">
                <div class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto">
                    @if (session('success'))
                        <x-alert type="success">
                            {{ session('success') }}
                        </x-alert>
                    @endif
                    
                    @if (session('error'))
                        <x-alert type="error">
                            {{ session('error') }}
                        </x-alert>
                    @endif
                    
                    @if (session('warning'))
                        <x-alert type="warning">
                            {{ session('warning') }}
                        </x-alert>
                    @endif
                    
                    @if (session('info'))
                        <x-alert type="info">
                            {{ session('info') }}
                        </x-alert>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    @stack('scripts')
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Initialize Lucide icons and re-initialize on dynamic content
        document.addEventListener('DOMContentLoaded', function() {
            lucide.createIcons();
        });
        
        // Re-initialize icons when sidebar toggles (Alpine is loaded via Vite)
        if (window.Alpine) {
            document.addEventListener('alpine:init', () => {
                Alpine.effect(() => {
                    setTimeout(() => lucide.createIcons(), 100);
                });
            });
        }
    </script>
</body>
</html>

