<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use App\Models\Airport;
use App\Models\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
       Schema::defaultStringLength(191);

       // Add scoped route model binding for airports within events
       Route::bind('airport', function ($value, $route) {
           // If we have an event in the route, scope the airport to that event
           $event = $route->parameter('event');
           
           if ($event) {
               // Handle both Event model instance and slug/ID string
               if (!($event instanceof Event)) {
                   // Try to find by slug first (since Event uses slug as route key)
                   $event = Event::where('slug', $event)->orWhere('id', $event)->first();
               }
               
               if ($event && $event instanceof Event) {
                   return Airport::where('id', $value)
                       ->where('event_id', $event->id)
                       ->firstOrFail();
               }
           }
           
           // Fallback to standard binding if no event context
           return Airport::findOrFail($value);
       });

       // If the app is served from a subdirectory (ex: /admin/public), ensure generated
       // URLs (routes/assets + Livewire endpoints) include that base path in production.
       $forcedBasePath = trim((string) env('APP_BASE_PATH', ''));

       if ($this->app->runningInConsole()) {
           // In console context (ex: route:cache), we can only rely on explicit env config.
           $basePath = $forcedBasePath !== '' ? '/' . ltrim($forcedBasePath, '/') : '';
       } else {
           $forwardedPrefix = (string) request()->headers->get('X-Forwarded-Prefix', '');
           $basePath = $forcedBasePath !== ''
               ? '/' . ltrim($forcedBasePath, '/')
               : ($forwardedPrefix !== '' ? '/' . ltrim($forwardedPrefix, '/') : request()->getBasePath());

           $basePath = rtrim($basePath, '/');

           URL::forceRootUrl(request()->getSchemeAndHttpHost() . $basePath);
       }

       // Livewire v3 registers its endpoints at root by default (/livewire/update, /livewire/livewire.js).
       // When the app is hosted in a subdirectory, explicitly register the same endpoints under that base path.
       if ($basePath !== '' && class_exists(\Livewire\Livewire::class)) {
           \Livewire\Livewire::setUpdateRoute(function ($handle) use ($basePath) {
               return Route::post($basePath . '/livewire/update', $handle)->middleware('web');
           });

           \Livewire\Livewire::setScriptRoute(function ($handle) use ($basePath) {
               return config('app.debug')
                   ? Route::get($basePath . '/livewire/livewire.js', $handle)
                   : Route::get($basePath . '/livewire/livewire.min.js', $handle);
           });

           // Optional: avoid sourcemap 404s in devtools when hosted in a subdirectory.
           Route::get($basePath . '/livewire/livewire.min.js.map', [\Livewire\Mechanisms\FrontendAssets\FrontendAssets::class, 'maps']);

           // File uploads & previews also need the base path for Livewire v3.
           Route::post($basePath . '/livewire/upload-file', [\Livewire\Features\SupportFileUploads\FileUploadController::class, 'handle'])
               ->name('livewire.upload-file');
           Route::get($basePath . '/livewire/preview-file/{filename}', [\Livewire\Features\SupportFileUploads\FilePreviewController::class, 'handle'])
               ->name('livewire.preview-file');
       }
    }
}
