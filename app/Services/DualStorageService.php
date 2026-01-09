<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DualStorageService
{
    /**
     * Store a file in both storage/app/public and public/storage
     * Returns the relative path (same format as Laravel's store method)
     */
    public static function store(UploadedFile $file, string $path, string $disk = 'public'): string
    {
        // Store in storage/app/public (Laravel's default)
        $relativePath = $file->store($path, $disk);
        
        // Also copy to public/storage for direct web access
        $sourcePath = Storage::disk($disk)->path($relativePath);
        $publicPath = public_path('storage/' . $relativePath);
        
        // Ensure directory exists in public/storage
        $publicDir = dirname($publicPath);
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }
        
        // Copy file to public/storage
        File::copy($sourcePath, $publicPath);
        
        return $relativePath;
    }

    /**
     * Store raw content (like PDF output) in both locations
     */
    public static function put(string $path, string $content, string $disk = 'public'): bool
    {
        // Store in storage/app/public
        $stored = Storage::disk($disk)->put($path, $content);
        
        if (!$stored) {
            return false;
        }
        
        // Also copy to public/storage
        $publicPath = public_path('storage/' . $path);
        $publicDir = dirname($publicPath);
        
        if (!File::exists($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }
        
        File::put($publicPath, $content);
        
        return true;
    }

    /**
     * Delete file from both locations
     */
    public static function delete(string $path, string $disk = 'public'): bool
    {
        $deletedFromStorage = Storage::disk($disk)->delete($path);
        
        $publicPath = public_path('storage/' . $path);
        $deletedFromPublic = true;
        
        if (File::exists($publicPath)) {
            $deletedFromPublic = File::delete($publicPath);
        }
        
        return $deletedFromStorage && $deletedFromPublic;
    }

    /**
     * Ensure directory exists in both locations
     */
    public static function makeDirectory(string $path, string $disk = 'public'): bool
    {
        $storageDir = Storage::disk($disk)->path($path);
        $publicDir = public_path('storage/' . $path);
        
        $storageCreated = true;
        $publicCreated = true;
        
        if (!File::exists($storageDir)) {
            $storageCreated = File::makeDirectory($storageDir, 0755, true);
        }
        
        if (!File::exists($publicDir)) {
            $publicCreated = File::makeDirectory($publicDir, 0755, true);
        }
        
        return $storageCreated && $publicCreated;
    }

    /**
     * Get the public URL for a stored file
     * Always returns /storage/ path since public/storage is directly accessible
     */
    public static function url(string $path): string
    {
        $baseUrl = config('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/storage/' . ltrim($path, '/');
    }
}

