<?php

namespace App\Services;

use Illuminate\Support\Str;

class InputSanitizer
{
    /**
     * Sanitize string input to prevent XSS attacks
     * 
     * @param mixed $input
     * @return mixed
     */
    public static function sanitize($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }

        if (is_string($input)) {
            // Remove null bytes
            $input = str_replace("\0", '', $input);
            
            // Trim whitespace
            $input = trim($input);
            
            // Remove control characters except newlines and tabs
            $input = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $input);
            
            return $input;
        }

        return $input;
    }

    /**
     * Sanitize HTML content (allows safe HTML)
     * 
     * @param string $input
     * @return string
     */
    public static function sanitizeHtml(string $input): string
    {
        // Use Laravel's built-in HTML purifier or strip_tags for basic sanitization
        // For production, consider using HTMLPurifier package
        return strip_tags($input, '<p><br><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6>');
    }

    /**
     * Sanitize URL input
     * 
     * @param string $url
     * @return string|null
     */
    public static function sanitizeUrl(string $url): ?string
    {
        $url = trim($url);
        
        if (empty($url)) {
            return null;
        }

        // Validate URL format
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Only allow http and https protocols
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'])) {
            return null;
        }

        return $url;
    }

    /**
     * Sanitize email input
     * 
     * @param string $email
     * @return string|null
     */
    public static function sanitizeEmail(string $email): ?string
    {
        $email = trim(strtolower($email));
        
        if (empty($email)) {
            return null;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    /**
     * Sanitize search query
     * 
     * @param string $query
     * @return string
     */
    public static function sanitizeSearch(string $query): string
    {
        $query = trim($query);
        
        // Remove SQL injection attempts
        $query = preg_replace('/[\'";\\\]/', '', $query);
        
        // Limit length
        $query = mb_substr($query, 0, 255);
        
        return $query;
    }

    /**
     * Sanitize slug input
     * 
     * @param string $slug
     * @return string
     */
    public static function sanitizeSlug(string $slug): string
    {
        // Use Laravel's Str::slug which is safe
        return Str::slug($slug);
    }

    /**
     * Sanitize numeric input
     * 
     * @param mixed $value
     * @param bool $allowFloat
     * @return int|float|null
     */
    public static function sanitizeNumeric($value, bool $allowFloat = false)
    {
        if (is_numeric($value)) {
            return $allowFloat ? (float) $value : (int) $value;
        }

        return null;
    }

    /**
     * Sanitize file name
     * 
     * @param string $filename
     * @return string
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Remove path components
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Limit length
        $filename = mb_substr($filename, 0, 255);
        
        return $filename;
    }
}

