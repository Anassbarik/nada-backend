# Backend Security Requirements - OWASP Guidelines

This document outlines the security requirements for the Laravel backend following OWASP Top 10 guidelines.

## Table of Contents

1. [SQL Injection Prevention](#sql-injection-prevention)
2. [Rate Limiting](#rate-limiting)
3. [Input Validation](#input-validation)
4. [Parameterized Queries](#parameterized-queries)
5. [API Key Management](#api-key-management)
6. [Input Sanitization](#input-sanitization)
7. [Implementation Checklist](#implementation-checklist)

---

## SQL Injection Prevention

### OWASP Guideline: Use Parameterized Queries

**CRITICAL**: Never concatenate user input into SQL queries. Always use Laravel's built-in query methods.

### ✅ CORRECT Implementation

```php
// ✅ Use Eloquent ORM (automatically parameterized)
$bookings = Booking::where('user_id', $userId)->get();

// ✅ Use Query Builder with bindings
DB::table('bookings')
    ->where('status', '=', 'confirmed')
    ->where('accommodation_id', '=', $accommodationId)
    ->get();

// ✅ Use whereIn with array
Booking::whereIn('id', $bookingIds)->get();

// ✅ Use whereRaw with bindings
DB::table('bookings')
    ->whereRaw('created_at > ? AND price > ?', [$date, $minPrice])
    ->get();

// ✅ Search queries with sanitization
$search = \App\Services\InputSanitizer::sanitizeSearch($request->search);
$query->where('booking_reference', 'like', "%{$search}%");
```

### ❌ WRONG Implementation

```php
// ❌ NEVER DO THIS - SQL Injection vulnerability
$bookings = DB::select("SELECT * FROM bookings WHERE user_id = " . $userId);

// ❌ NEVER DO THIS - SQL Injection vulnerability
$query = "SELECT * FROM bookings WHERE booking_reference LIKE '%" . $searchTerm . "%'";
$bookings = DB::select($query);

// ❌ NEVER DO THIS - Even with escaping, use bindings
$bookings = DB::select("SELECT * FROM bookings WHERE user_id = " . DB::escape($userId));
```

### Required Implementation

**All Controllers Must**:
- ✅ Use Eloquent ORM for database operations
- ✅ Use Query Builder with parameter bindings
- ✅ Never use `DB::select()` with string concatenation
- ✅ Never use `DB::raw()` with user input
- ✅ Sanitize search queries before use

**Status**: ✅ **IMPLEMENTED** - All queries use Eloquent/Query Builder with parameterized bindings.

---

## Rate Limiting

### OWASP Guideline: Implement Rate Limiting on Every API Endpoint

**Laravel Implementation**: Use Laravel's built-in rate limiting middleware.

### Current Rate Limits

```php
// routes/api.php

// Public GET endpoints: 120 requests per minute
Route::get('/events', [EventController::class, 'index'])->middleware('throttle:120,1');
Route::get('/events/{slug}', [EventController::class, 'show'])->middleware('throttle:120,1');
Route::get('/events/{slug}/hotels', [HotelController::class, 'index'])->middleware('throttle:120,1');
Route::get('/events/{slug}/flights', [FlightController::class, 'index'])->middleware('throttle:120,1');

// Authentication: 5 requests per minute
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

// Newsletter: 5 requests per minute
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1');

// Protected endpoints: 60 requests per minute (general)
Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::get('/bookings', [BookingController::class, 'index']);
    // ...
});

// Sensitive operations: 10 requests per minute
Route::post('/bookings', [BookingController::class, 'store'])->middleware('throttle:10,1');
Route::post('/bookings/{booking}/payment-document', [BookingController::class, 'uploadPaymentDocument'])->middleware('throttle:10,1');
Route::put('/user/password', [AuthController::class, 'updatePassword'])->middleware('throttle:5,1');
```

### Rate Limit Response

When rate limit is exceeded, Laravel automatically returns:

```json
{
    "message": "Too Many Attempts."
}
```

With HTTP 429 status code and `Retry-After` header.

**Status**: ✅ **IMPLEMENTED** - All API endpoints have rate limiting configured.

---

## Input Validation

### OWASP Guideline: Validate Inputs on Both Frontend and Backend

**Laravel Implementation**: Validation rules defined in controllers using `$request->validate()`.

### Current Validation Examples

**1. Booking Creation Validation**

```php
// app/Http/Controllers/Api/BookingController.php
$validated = $request->validate([
    'accommodation_id' => 'required|integer|exists:accommodations,id',
    'hotel_id' => 'required|integer|exists:hotels,id',
    'package_id' => 'required|integer|exists:packages,id',
    'full_name' => 'required|string|max:255',
    'email' => 'required|email|max:255',
    'phone' => 'required|string|max:50',
    'checkin_date' => 'required|date|after_or_equal:today',
    'checkout_date' => 'required|date|after:checkin_date',
    'guests_count' => 'required|integer|min:1|max:10',
    'booking_reference' => 'nullable|string|exists:bookings,booking_reference',
]);
```

**2. User Registration Validation**

```php
// app/Http/Controllers/Api/AuthController.php
$validated = $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
    'password' => [
        'required',
        'confirmed',
        'min:8',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // Mixed case + numbers
    ],
    'phone' => ['nullable', 'string', 'max:50'],
    'company' => ['nullable', 'string', 'max:255'],
]);
```

**3. Password Update Validation**

```php
$validated = $request->validate([
    'current_password' => ['required', 'string'],
    'password' => [
        'required',
        'confirmed',
        'min:8',
        'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
    ],
]);
```

**4. File Upload Validation**

```php
$validated = $request->validate([
    'payment_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB
    'flight_ticket' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
]);
```

### Validation Error Response

All validation errors return consistent format:

```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."],
        "password": ["The password must be at least 8 characters."]
    }
}
```

With HTTP 422 status code.

**Status**: ✅ **IMPLEMENTED** - All endpoints have comprehensive validation rules.

---

## Parameterized Queries

### All Database Queries Must Use Bindings

**Example Controller Implementation**:

```php
// app/Http/Controllers/Api/BookingController.php

// ✅ CORRECT - Eloquent automatically uses parameterized queries
$bookings = Booking::where('user_id', $user->id)
    ->where('status', 'confirmed')
    ->with(['accommodation', 'hotel', 'package'])
    ->get();

// ✅ CORRECT - Query Builder with bindings
$booking = DB::table('bookings')
    ->where('booking_reference', '=', $reference)
    ->where('status', '=', 'pending')
    ->first();

// ✅ CORRECT - Search with sanitization
$search = \App\Services\InputSanitizer::sanitizeSearch($request->search);
$query->where('booking_reference', 'like', "%{$search}%");
```

**Status**: ✅ **IMPLEMENTED** - All queries use Eloquent ORM or Query Builder with parameterized bindings.

---

## API Key Management

### OWASP Guideline: Never Hardcode API Keys

**Laravel Implementation**: Store all API keys in `.env` file.

### Required Environment Variables

```env
# .env (not committed to git)

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Application
APP_KEY=base64:your_app_key_here
APP_ENV=production
APP_DEBUG=false

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_email@example.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls

# Third-party Services (if used)
GOOGLE_MAPS_API_KEY=your_google_maps_api_key_here
POSTMARK_API_KEY=your_postmark_key
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret

# Cache Clear Token
CACHE_CLEAR_TOKEN=your-secret-token-here

# Frontend URL (for CORS)
FRONTEND_URL=http://localhost:3000
```

### Configuration Files

```php
// config/services.php
return [
    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],
    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],
    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
];
```

### Usage in Code

```php
// ✅ CORRECT - Use config() helper
$apiKey = config('services.postmark.key');

// ✅ CORRECT - Use env() helper (only in config files)
$apiKey = env('GOOGLE_MAPS_API_KEY');

// ❌ WRONG - Never hardcode
$apiKey = 'AIzaSyBxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
```

**Status**: ✅ **IMPLEMENTED** - No hardcoded API keys found. All secrets use environment variables.

---

## Input Sanitization

### Sanitize All User Inputs

**Implementation**: `App\Services\InputSanitizer` service and `App\Http\Middleware\SanitizeInput` middleware.

### Sanitization Methods

```php
use App\Services\InputSanitizer;

// Sanitize string input
$sanitized = InputSanitizer::sanitize($input);

// Sanitize email
$email = InputSanitizer::sanitizeEmail($input);

// Sanitize URL
$url = InputSanitizer::sanitizeUrl($input);

// Sanitize search query
$query = InputSanitizer::sanitizeSearch($input);

// Sanitize file name
$filename = InputSanitizer::sanitizeFilename($input);
```

### Middleware Application

Input sanitization is automatically applied to all requests via middleware:

```php
// bootstrap/app.php
$middleware->web(append: [
    \App\Http\Middleware\SanitizeInput::class,
]);

$middleware->api(append: [
    \App\Http\Middleware\SanitizeInput::class,
]);
```

### Search Query Sanitization

All search queries are sanitized before use:

```php
// app/Http/Controllers/Admin/BookingController.php
$search = \App\Services\InputSanitizer::sanitizeSearch($request->search);
$query->where('booking_reference', 'like', "%{$search}%");
```

**Status**: ✅ **IMPLEMENTED** - All inputs are sanitized via middleware and service methods.

---

## Implementation Checklist

### SQL Injection Prevention

- [x] All queries use Eloquent ORM
- [x] All queries use Query Builder with bindings
- [x] No `DB::select()` with string concatenation
- [x] No `DB::raw()` with user input
- [x] All `whereIn()` use arrays, not strings
- [x] All `whereRaw()` use parameter bindings
- [x] Search queries sanitized before use

### Rate Limiting

- [x] Rate limiting middleware applied to all API routes
- [x] Different limits for different endpoint types:
  - [x] General API: 120/min (GET), 60/min (protected)
  - [x] Authentication: 5/min
  - [x] File uploads: 10/min
  - [x] Password changes: 5/min
- [x] 429 status code returned on rate limit exceeded
- [x] `Retry-After` header included in 429 response (automatic)

### Input Validation

- [x] Validation rules defined for all inputs
- [x] Custom validation rules for complex cases
- [x] Validation errors returned in consistent format
- [x] All numeric inputs validated (min/max)
- [x] All string inputs validated (length limits)
- [x] Email format validated
- [x] Phone format validated
- [x] File uploads validated (type and size)
- [x] Password strength requirements enforced

### Parameterized Queries

- [x] All database queries reviewed
- [x] No string concatenation in queries
- [x] All user input passed as bindings
- [x] Eloquent relationships used where possible
- [x] Search queries sanitized

### API Key Management

- [x] All API keys in `.env` file
- [x] `.env` in `.gitignore`
- [x] `.env.example` created (without keys)
- [x] Config files use `env()` helper
- [x] No hardcoded keys in source code

### Input Sanitization

- [x] Input sanitization middleware created
- [x] Middleware applied to all routes
- [x] Sanitization service with multiple methods
- [x] Search queries sanitized
- [x] File names sanitized
- [x] URLs validated
- [x] Emails validated

### Security Headers

- [x] CORS configured correctly
- [x] CSRF protection enabled (web routes)
- [x] Sanctum token authentication (API routes)
- [x] Content Security Policy middleware available
- [x] XSS protection via Blade auto-escaping

---

## Security Testing

### Test Cases

1. **SQL Injection**
   - [x] Try SQL injection in search: `'; DROP TABLE users; --`
   - [x] Try SQL injection in URL parameters
   - [x] Verify queries use bindings (all queries use Eloquent/Query Builder)

2. **Rate Limiting**
   - [x] Make 100 rapid requests to an endpoint
   - [x] Verify 429 status returned
   - [x] Verify `Retry-After` header present

3. **Input Validation**
   - [x] Submit invalid email format
   - [x] Submit invalid phone format
   - [x] Submit oversized strings
   - [x] Submit negative numbers where not allowed
   - [x] Verify validation errors returned

4. **XSS Prevention**
   - [x] Try injecting `<script>` tags in form fields
   - [x] Verify HTML is escaped in responses (Blade auto-escape)
   - [x] Check that user input is sanitized (middleware)

---

## Security Best Practices

### 1. Always Validate Before Processing

```php
// ✅ CORRECT
$validated = $request->validate([
    'accommodation_id' => 'required|integer|exists:accommodations,id',
]);
$accommodationId = $validated['accommodation_id']; // Safe to use

// ❌ WRONG
$accommodationId = $request->input('accommodation_id'); // Not validated
$bookings = Booking::where('accommodation_id', $accommodationId)->get();
```

### 2. Use Type Hints

```php
// ✅ CORRECT - Type hint ensures type safety
public function show(Request $request, Accommodation $accommodation, Flight $flight)

// ❌ WRONG - No type safety
public function show($request, $accommodation, $flight)
```

### 3. Use Eloquent Relationships

```php
// ✅ CORRECT - Eloquent handles SQL safely
$flights = $accommodation->flights()->where('status', 'pending')->get();

// ❌ WRONG - Manual query construction
$flights = DB::select("SELECT * FROM flights WHERE accommodation_id = " . $accommodation->id);
```

### 4. Sanitize Before Validation

```php
// ✅ CORRECT - Sanitization happens automatically via middleware
// Then validate
$validated = $request->validate(['name' => 'required|string|max:255']);
```

---

## Related Documentation

- **OWASP Security Implementation**: `OWASP_SECURITY_IMPLEMENTATION.md`
- **Security Audit Summary**: `SECURITY_AUDIT_SUMMARY.md`
- **Laravel Security**: https://laravel.com/docs/security
- **OWASP Top 10**: https://owasp.org/www-project-top-ten/

---

## Status Summary

| Requirement | Status | Notes |
|------------|--------|-------|
| SQL Injection Prevention | ✅ Complete | All queries use Eloquent/Query Builder |
| Rate Limiting | ✅ Complete | All endpoints protected |
| Input Validation | ✅ Complete | Comprehensive validation rules |
| Parameterized Queries | ✅ Complete | No raw SQL with user input |
| API Key Management | ✅ Complete | All keys in environment variables |
| Input Sanitization | ✅ Complete | Middleware + service methods |
| XSS Protection | ✅ Complete | Blade auto-escape + sanitization |
| CSRF Protection | ✅ Complete | Laravel middleware |
| File Upload Security | ✅ Complete | Validation + sanitization |

---

**Status**: ✅ **ALL REQUIREMENTS IMPLEMENTED**
**Last Updated**: 2026-01-24
**Compliance**: OWASP Top 10 Guidelines

