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
     * Uses Laravel's asset helper to properly handle subdirectories and local development
     */
    public static function url(string $path): string
    {
        // Use asset() helper which automatically handles the base URL and subdirectories
        // This works correctly both locally and in production with subdirectories
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Copy a file from one location to another in both storage locations
     * Returns the new relative path
     */
    public static function copy(string $sourcePath, string $destinationPath, string $disk = 'public'): string
    {
        if (empty($sourcePath) || !Storage::disk($disk)->exists($sourcePath)) {
            return $sourcePath; // Return original if source doesn't exist
        }

        // Generate new filename with timestamp to avoid conflicts
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $filename = pathinfo($sourcePath, PATHINFO_FILENAME);
        $newFilename = $filename . '_' . time() . '.' . $extension;
        $newPath = rtrim($destinationPath, '/') . '/' . $newFilename;

        // Ensure directory exists
        self::makeDirectory($destinationPath, $disk);

        // Copy in storage/app/public
        $sourceStoragePath = Storage::disk($disk)->path($sourcePath);
        $destStoragePath = Storage::disk($disk)->path($newPath);
        File::copy($sourceStoragePath, $destStoragePath);

        // Copy in public/storage
        $sourcePublicPath = public_path('storage/' . $sourcePath);
        $destPublicPath = public_path('storage/' . $newPath);
        if (File::exists($sourcePublicPath)) {
            File::copy($sourcePublicPath, $destPublicPath);
        }

        return $newPath;
    }
}

