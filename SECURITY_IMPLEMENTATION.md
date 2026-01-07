# Security Implementation - Token Management & Data Protection

**Status:** ✅ Production Ready  
**Last Updated:** January 2026

---

## 🔒 Token Security

### Maximum Tokens Per User

**Limit:** **5 tokens per user**

**Implementation:**
- When a user creates a new token (register/login), the system checks their current token count
- If they have 5 or more tokens, the **oldest tokens are automatically deleted**
- Only the **most recent 5 tokens** are kept active
- This prevents token accumulation and database bloat

**Code Location:** `app/Http/Controllers/Api/AuthController.php`
```php
private const MAX_TOKENS_PER_USER = 5;

private function createTokenWithLimit(User $user, string $tokenName): string
{
    $tokenCount = $user->tokens()->count();
    
    if ($tokenCount >= self::MAX_TOKENS_PER_USER) {
        $tokensToDelete = $tokenCount - self::MAX_TOKENS_PER_USER + 1;
        $user->tokens()
            ->orderBy('created_at', 'asc')
            ->limit($tokensToDelete)
            ->delete();
    }
    
    return $user->createToken($tokenName)->plainTextToken;
}
```

**Benefits:**
- ✅ Prevents unlimited token creation
- ✅ Automatic cleanup of old tokens
- ✅ Reduces database storage
- ✅ Limits attack surface if tokens are leaked
- ✅ Users can still use multiple devices (up to 5)

---

## 🛡️ Rate Limiting

### Authentication Endpoints

**Rate Limits:**
- `POST /api/register`: **5 attempts per minute**
- `POST /api/login`: **5 attempts per minute**

**Implementation:**
```php
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:5,1'); // 5 attempts per minute

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per minute
```

**Benefits:**
- ✅ Prevents brute force attacks
- ✅ Protects against credential stuffing
- ✅ Reduces server load
- ✅ Protects user accounts

**Error Response (429 Too Many Requests):**
```json
{
  "message": "Too Many Attempts."
}
```

---

## 🔐 Data Security

### Password Security

**Hashing:**
- ✅ Passwords are hashed using `bcrypt` (via Laravel's `Hash::make()`)
- ✅ Passwords are **never** stored in plain text
- ✅ Passwords are **never** returned in API responses

**Validation:**
- ✅ Minimum 8 characters
- ✅ Must contain uppercase letter
- ✅ Must contain lowercase letter
- ✅ Must contain number
- ✅ Regex: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/`

**User Model:**
```php
protected $hidden = [
    'password',
    'remember_token',
];

protected function casts(): array
{
    return [
        'password' => 'hashed', // Auto-hash on assignment
    ];
}
```

---

### Sensitive Data Protection

**User Model - Hidden Fields:**
```php
protected $hidden = [
    'password',        // Never exposed in JSON
    'remember_token',  // Never exposed in JSON
];
```

**API Responses:**
- ✅ Only safe fields are returned: `id`, `name`, `email`
- ✅ Password is **never** included
- ✅ Token is only returned once (on register/login)
- ✅ Role is not exposed in user endpoint (security through obscurity)

**Example Safe Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
    // ❌ NO password, remember_token, or role
  }
}
```

---

### Token Security

**Token Storage:**
- ✅ Tokens stored in `personal_access_tokens` table
- ✅ Token hash is stored (not plain text)
- ✅ Only `plainTextToken` is returned once (on creation)
- ✅ Token cannot be retrieved after creation

**Token Structure:**
```
{id}|{random_hash}
Example: 1|abcdef1234567890abcdef1234567890abcdef1234567890
```

**Token Lifecycle:**
1. Created on register/login
2. Stored in database (hashed)
3. Returned to frontend (plain text, one time only)
4. Frontend stores in localStorage
5. Included in API requests: `Authorization: Bearer {token}`
6. Validated on each request
7. Deleted on logout or when limit exceeded

**Token Expiration:**
- Currently: **No expiration** (tokens don't expire)
- Can be configured in `config/sanctum.php`:
  ```php
  'expiration' => 60 * 24 * 7, // 7 days in minutes
  ```

---

## 🔒 API Security

### Authentication Middleware

**Protected Routes:**
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    // ... other protected routes
});
```

**How It Works:**
1. Checks for `Authorization: Bearer {token}` header
2. Validates token against database
3. Loads user model
4. Returns 401 if token invalid/missing

**Error Response (401):**
```json
{
  "message": "Unauthenticated."
}
```

---

### CORS Configuration

**Allowed Origins:**
- Configured in `config/cors.php`
- Set via `.env`: `CORS_ALLOWED_ORIGINS`
- Default: `localhost:3000,localhost:3001`

**Security:**
- ✅ Only specified origins can access API
- ✅ Credentials supported for cookie-based auth
- ✅ Prevents unauthorized cross-origin requests

---

### Content Security Policy (CSP)

**Status:** Configured but disabled by default

**Configuration:**
- Enabled via `.env`: `CSP_ENABLED=true`
- Middleware: `app/Http/Middleware/ContentSecurityPolicy.php`
- Compatible with Sanctum, Tailwind, Alpine.js

**When Enabled:**
- ✅ Prevents XSS attacks
- ✅ Blocks unauthorized script execution
- ✅ Protects against code injection
- ✅ Allows necessary sources (Vite, fonts, API)

---

## 📊 Security Checklist

### ✅ Implemented

- [x] Maximum tokens per user (5 tokens)
- [x] Automatic token cleanup
- [x] Rate limiting on auth endpoints
- [x] Password hashing (bcrypt)
- [x] Password validation (strong requirements)
- [x] Sensitive data hidden (password, remember_token)
- [x] Token authentication middleware
- [x] CORS configuration
- [x] CSP middleware (optional)
- [x] Role-based access control
- [x] Admin route protection

### ⚠️ Optional Enhancements

- [ ] Token expiration (currently disabled)
- [ ] Two-factor authentication (2FA)
- [ ] Email verification
- [ ] Password reset functionality
- [ ] Account lockout after failed attempts
- [ ] IP-based rate limiting
- [ ] Token refresh mechanism
- [ ] Audit logging

---

## 🚨 Security Best Practices

### For Developers

1. **Never log tokens or passwords**
   ```php
   // ❌ BAD
   Log::info('User token: ' . $token);
   
   // ✅ GOOD
   Log::info('User authenticated', ['user_id' => $user->id]);
   ```

2. **Always validate input**
   ```php
   $validated = $request->validate([
       'email' => 'required|email',
       'password' => 'required|min:8',
   ]);
   ```

3. **Use parameterized queries** (Laravel Eloquent does this automatically)
   ```php
   // ✅ GOOD - Eloquent prevents SQL injection
   User::where('email', $email)->first();
   ```

4. **Never expose sensitive data**
   ```php
   // ❌ BAD
   return $user; // Exposes password, remember_token
   
   // ✅ GOOD
   return $user->only('id', 'name', 'email');
   ```

### For Frontend Developers

1. **Store tokens securely**
   ```javascript
   // ✅ GOOD - localStorage (persists)
   localStorage.setItem('token', token);
   
   // ⚠️ ALTERNATIVE - sessionStorage (clears on tab close)
   sessionStorage.setItem('token', token);
   
   // ❌ NEVER - URL parameters, cookies (unless HttpOnly)
   ```

2. **Include token in requests**
   ```javascript
   headers: {
     'Authorization': `Bearer ${token}`,
     'Content-Type': 'application/json',
   }
   ```

3. **Handle token expiration**
   ```javascript
   if (error.response?.status === 401) {
     localStorage.removeItem('token');
     router.push('/login');
   }
   ```

4. **Never log tokens**
   ```javascript
   // ❌ BAD
   console.log('Token:', token);
   
   // ✅ GOOD
   console.log('User authenticated');
   ```

---

## 📈 Monitoring & Maintenance

### Token Cleanup

**Automatic:**
- Oldest tokens deleted when limit reached
- Happens on each register/login

**Manual Cleanup (if needed):**
```bash
# Delete expired tokens (if expiration is enabled)
php artisan tinker
>>> DB::table('personal_access_tokens')->where('expires_at', '<', now())->delete();

# Delete tokens older than 30 days
>>> DB::table('personal_access_tokens')
    ->where('created_at', '<', now()->subDays(30))
    ->delete();
```

### Database Maintenance

**Check token count per user:**
```sql
SELECT 
    u.email,
    COUNT(t.id) as token_count
FROM users u
LEFT JOIN personal_access_tokens t ON t.tokenable_id = u.id
GROUP BY u.id, u.email
HAVING COUNT(t.id) > 5;
```

**Find users with many tokens:**
```sql
SELECT 
    u.email,
    COUNT(t.id) as token_count,
    MAX(t.created_at) as latest_token
FROM users u
JOIN personal_access_tokens t ON t.tokenable_id = u.id
GROUP BY u.id, u.email
ORDER BY token_count DESC;
```

---

## 🔍 Security Audit

### Regular Checks

1. **Review token counts** (monthly)
   - Check for users with excessive tokens
   - Investigate suspicious activity

2. **Monitor failed login attempts**
   - Check logs for brute force patterns
   - Review rate limit effectiveness

3. **Verify password strength**
   - Ensure validation rules are enforced
   - Check for weak passwords in database

4. **Review API access logs**
   - Check for unauthorized access attempts
   - Monitor token usage patterns

5. **Update dependencies**
   - Keep Laravel and Sanctum updated
   - Apply security patches promptly

---

## 📚 References

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [CSP Documentation](./CSP_SETUP.md)

---

**Last Updated:** January 2026  
**Security Status:** ✅ Production Ready

