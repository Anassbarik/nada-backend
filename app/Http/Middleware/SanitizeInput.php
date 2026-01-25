<?php

namespace App\Http\Middleware;

use App\Services\InputSanitizer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Handle an incoming request and sanitize all inputs
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sanitize all request inputs
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $input = $request->all();
            
            // Don't sanitize password fields (they're hashed anyway)
            $excludedFields = [
                'password',
                'password_confirmation',
                'current_password',
                'new_password',
            ];
            
            foreach ($input as $key => $value) {
                if (!in_array($key, $excludedFields) && is_string($value)) {
                    $request->merge([
                        $key => InputSanitizer::sanitize($value)
                    ]);
                }
            }
        }
        
        // Sanitize query parameters
        $query = $request->query();
        foreach ($query as $key => $value) {
            if (is_string($value)) {
                $request->query->set($key, InputSanitizer::sanitize($value));
            }
        }
        
        return $next($request);
    }
}

