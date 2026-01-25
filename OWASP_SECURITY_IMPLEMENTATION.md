# OWASP Security Implementation Guide

This document outlines the security measures implemented following OWASP Top 10 security guidelines.

## 1. Input Sanitization ✅

### Implementation
- **Service**: `App\Services\InputSanitizer`
- **Middleware**: `App\Http\Middleware\SanitizeInput`
- **Applied to**: All web and API routes

### Features
- Removes null bytes and control characters
- Trims whitespace
- Sanitizes HTML content
- Validates URLs and emails
- Sanitizes search queries
- Sanitizes file names

### Usage
```php
use App\Services\InputSanitizer;

// Sanitize string
$clean = InputSanitizer::sanitize($userInput);

// Sanitize email
$email = InputSanitizer::sanitizeEmail($userInput);

// Sanitize URL
$url = InputSanitizer::sanitizeUrl($userInput);

// Sanitize search query
$query = InputSanitizer::sanitizeSearch($userInput);
```

### Blade Templates
Blade automatically escapes output using `{{ }}` syntax:
```blade
{{ $user->name }} {{-- Automatically escaped --}}
{!! $htmlContent !!} {{-- Only use if you trust the content --}}
```

## 2. Rate Limiting ✅

### Implementation
All API endpoints now have rate limiting applied:

#### Public Endpoints
- **General GET requests**: `throttle:120,1` (120 requests per minute)
- **Authentication**: `throttle:5,1` (5 attempts per minute)
- **Newsletter**: `throttle:5,1` (5 requests per minute)
- **CSRF Cookie**: `throttle:60,1` (60 requests per minute)

#### Protected Endpoints (Authenticated)
- **General requests**: `throttle:60,1` (60 requests per minute)
- **Password changes**: `throttle:5,1` (5 attempts per minute)
- **Booking creation**: `throttle:10,1` (10 requests per minute)
- **File uploads**: `throttle:10,1` (10 uploads per minute)

### Configuration
Rate limits can be adjusted in `routes/api.php`:
```php
Route::get('/endpoint', [Controller::class, 'method'])
    ->middleware('throttle:requests,minutes');
```

## 3. Environment Variables for Secrets ✅

### Current Implementation
All sensitive data uses environment variables:

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PASSWORD=your_password

# Application
APP_KEY=base64:...
APP_ENV=production
APP_DEBUG=false

# Mail
MAIL_MAILER=smtp
MAIL_PASSWORD=your_password

# Cache Clear Token
CACHE_CLEAR_TOKEN=your-secret-token

# Third-party Services
POSTMARK_API_KEY=your_key
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
```

### Verification
✅ No hardcoded API keys found in codebase
✅ All secrets use `env()` helper
✅ `.env` file is in `.gitignore`

### Best Practices
1. **Never commit `.env` file**
2. **Use different keys for development and production**
3. **Rotate keys regularly**
4. **Use Laravel's config caching in production**:
   ```bash
   php artisan config:cache
   ```

## 4. Parameterized Queries ✅

### Implementation
Laravel Eloquent ORM uses parameterized queries by default, preventing SQL injection.

### Examples

#### ✅ Safe (Using Eloquent)
```php
// Safe - Eloquent uses parameterized queries
User::where('email', $email)->first();

// Safe - Query builder uses parameterized queries
DB::table('users')->where('email', $email)->first();
```

#### ✅ Safe (Using Query Builder with Bindings)
```php
// Safe - Parameters are bound
DB::select('SELECT * FROM users WHERE email = ?', [$email]);
```

#### ❌ Unsafe (Never Do This)
```php
// UNSAFE - Never concatenate user input into SQL
DB::select("SELECT * FROM users WHERE email = '{$email}'");
```

### Verification
✅ No raw SQL queries with user input found
✅ All database queries use Eloquent or Query Builder
✅ Route model binding used for ID lookups

## 5. Input Validation ✅

### Backend Validation
All inputs are validated using Laravel's validation rules:

```php
$validated = $request->validate([
    'email' => 'required|email|max:255|unique:users',
    'name' => 'required|string|max:255',
    'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
    'url' => 'nullable|url|max:500',
    'phone' => 'nullable|string|max:20',
]);
```

### Common Validation Rules

#### Email
```php
'email' => 'required|email|max:255|unique:users,email'
```

#### Password
```php
'password' => [
    'required',
    'string',
    'min:8',
    'confirmed',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', // Mixed case + numbers
]
```

#### URL
```php
'url' => 'nullable|url|max:500'
```

#### File Uploads
```php
'image' => 'required|image|mimes:jpeg,png,jpg|max:2048'
```

#### Numeric
```php
'price' => 'required|numeric|min:0|max:999999.99'
```

### Frontend Validation
Frontend should also validate inputs before submission:

```javascript
// Example: Email validation
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
if (!emailRegex.test(email)) {
    setError('Invalid email format');
    return;
}

// Example: Password validation
if (password.length < 8) {
    setError('Password must be at least 8 characters');
    return;
}

if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(password)) {
    setError('Password must contain uppercase, lowercase, and numbers');
    return;
}
```

## 6. XSS Protection ✅

### Backend
- **Input Sanitization**: All inputs sanitized via middleware
- **Output Escaping**: Blade templates auto-escape with `{{ }}`
- **Content Security Policy**: CSP middleware enabled (when `CSP_ENABLED=true`)

### Frontend
- **React/Vue**: Automatically escape output
- **Never use `dangerouslySetInnerHTML`** unless absolutely necessary
- **Sanitize user-generated content** before rendering

## 7. CSRF Protection ✅

### Implementation
- **Web Routes**: CSRF protection enabled via Laravel middleware
- **API Routes**: Sanctum token-based authentication
- **Forms**: All forms include `@csrf` token

### Usage
```blade
<form method="POST" action="/route">
    @csrf
    <!-- Form fields -->
</form>
```

## 8. File Upload Security ✅

### Validation
```php
'file' => [
    'required',
    'file',
    'mimes:jpeg,png,jpg,pdf',
    'max:2048', // 2MB
]
```

### Storage
- Files stored in `storage/app/public`
- Filenames sanitized before storage
- Original filenames not used (use UUID or hash)

### Example
```php
$filename = InputSanitizer::sanitizeFilename($request->file('image')->getClientOriginalName());
$path = $request->file('image')->storeAs('images', $filename, 'public');
```

## 9. Authentication Security ✅

### Password Requirements
- Minimum 8 characters
- Must contain uppercase, lowercase, and numbers
- Passwords hashed using bcrypt
- Password confirmation required

### Token Management
- Sanctum tokens for API authentication
- Maximum 5 tokens per user
- Oldest tokens deleted when limit reached
- Tokens can be revoked

### Rate Limiting
- Login: 5 attempts per minute
- Registration: 5 attempts per minute
- Password reset: 6 attempts per minute

## 10. Security Headers ✅

### Content Security Policy
CSP middleware configured (when enabled):
```php
// config/csp.php or middleware
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline';
```

### Other Headers
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block

## Security Checklist

### Before Deployment
- [ ] All environment variables set
- [ ] `.env` file not committed
- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production` in production
- [ ] Strong `APP_KEY` generated
- [ ] Database credentials secure
- [ ] Rate limiting configured
- [ ] Input sanitization enabled
- [ ] File upload limits set
- [ ] CSRF protection enabled
- [ ] HTTPS enabled
- [ ] Security headers configured

### Regular Maintenance
- [ ] Review access logs monthly
- [ ] Check for failed login attempts
- [ ] Rotate API keys quarterly
- [ ] Update dependencies regularly
- [ ] Review and update rate limits
- [ ] Audit user permissions
- [ ] Check for SQL injection attempts
- [ ] Monitor file uploads

## Reporting Security Issues

If you discover a security vulnerability, please email security@example.com instead of using the issue tracker.

## References

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Documentation](https://laravel.com/docs/security)
- [OWASP Cheat Sheet Series](https://cheatsheetseries.owasp.org/)

