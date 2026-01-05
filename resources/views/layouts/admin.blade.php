<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Admin - {{ config('app.name') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    
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
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        {{-- Modern Sidebar --}}
        <aside class="w-64 bg-white border-r shadow-lg flex flex-col">
            <div class="p-6 border-b bg-white">
                <div class="bg-gray-100 rounded-lg p-3 flex items-center justify-center">
                    <img src="{{ asset('assets/logo-seminaireexpo.png') }}" 
                         alt="SeminairExpo Logo" 
                         class="h-10 w-auto object-contain">
                </div>
            </div>
            
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" 
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
            </nav>
        </aside>
        
        {{-- Main Content Area with Header --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Top Header --}}
            <x-admin-header />
            
            <main class="flex-1 overflow-auto">
                <div class="p-8 max-w-7xl mx-auto">
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
    @livewireScripts
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>

