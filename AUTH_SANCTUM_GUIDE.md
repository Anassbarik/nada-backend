# Laravel Sanctum Authentication Guide

Complete guide for implementing and using Laravel Sanctum authentication in this application.

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Setup & Configuration](#setup--configuration)
4. [API Endpoints](#api-endpoints)
5. [Frontend Integration](#frontend-integration)
6. [Security Features](#security-features)
7. [Role-Based Access Control](#role-based-access-control)
8. [Troubleshooting](#troubleshooting)
9. [Best Practices](#best-practices)

---

## Overview

This application uses **Laravel Sanctum** for API authentication, providing:

- ✅ **Token-based authentication** for API requests
- ✅ **Role-based access control** (Admin/User)
- ✅ **Secure token storage** (HttpOnly cookies support)
- ✅ **CSP-compatible** (no conflicts with Tailwind/Alpine/Livewire)
- ✅ **Account creation on booking** (mandatory user registration)

### Authentication Flow

```
1. User registers → POST /api/register
   ↓
2. Receives token + user data
   ↓
3. Stores token (localStorage/sessionStorage)
   ↓
4. Includes token in API requests (Authorization header)
   ↓
5. Creates booking → POST /api/bookings (with token)
   ↓
6. Booking linked to user_id automatically
```

---

## Architecture

### Database Schema

**Users Table:**
```sql
- id (bigint)
- name (string)
- email (string, unique)
- password (hashed)
- role (enum: 'user', 'admin') ← NEW
- email_verified_at (timestamp, nullable)
- remember_token (string, nullable)
- created_at, updated_at
```

**Bookings Table:**
```sql
- id (bigint)
- user_id (bigint, nullable, foreign key) ← NEW
- event_id (bigint, foreign key)
- hotel_id (bigint, foreign key)
- package_id (bigint, foreign key)
- ... (other booking fields)
```

### Models

**User Model:**
- Uses `HasApiTokens` trait (Sanctum)
- Has `isAdmin()` method
- Has `bookings()` relationship

**Booking Model:**
- Has `user()` relationship
- Automatically links to authenticated user on creation

---

## Setup & Configuration

### 1. Run Migrations

```bash
php artisan migrate
```

This will:
- Add `role` column to `users` table
- Add `user_id` column to `bookings` table

### 2. Seed Admin User

```bash
php artisan db:seed --class=AdminSeeder
```

Creates default admin:
- **Email:** `admin@example.com`
- **Password:** `password`
- **Role:** `admin`

⚠️ **Change the password immediately after first login!**

### 3. Configure Environment Variables

Edit `.env`:

```env
# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:3001,127.0.0.1:3000,yourfrontend.com

# Session Configuration (for HttpOnly cookies)
SESSION_DRIVER=cookie
SESSION_SECURE_COOKIE=true  # Set to true in production (HTTPS required)
SESSION_HTTP_ONLY=true       # Prevents JavaScript access (security)
SESSION_SAME_SITE=lax       # CSRF protection

# CORS Configuration (if using separate frontend)
CORS_ALLOWED_ORIGINS=https://yourfrontend.com,http://localhost:3000
```

### 4. Verify Sanctum Configuration

Check `config/sanctum.php`:

```php
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '...')),
```

Ensure your frontend domain is included.

---

## API Endpoints

### Public Endpoints (No Authentication Required)

#### 1. Register User

**Endpoint:** `POST /api/register`

**Request:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePass123",
  "password_confirmation": "SecurePass123"
}
```

**Response (201 Created):**
```json
{
  "token": "1|abcdef1234567890...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Validation Rules:**
- `name`: required, string, max:255
- `email`: required, email, unique:users
- `password`: required, confirmed, min:8, mixed case, numbers

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password confirmation does not match."]
  }
}
```

---

#### 2. Login

**Endpoint:** `POST /api/login`

**Request:**
```json
{
  "email": "john@example.com",
  "password": "SecurePass123"
}
```

**Response (200 OK):**
```json
{
  "token": "2|xyz9876543210...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

### Protected Endpoints (Require Authentication)

All protected endpoints require the `Authorization` header:

```
Authorization: Bearer {token}
```

---

#### 3. Get Authenticated User

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Error Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

---

#### 4. Logout

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "message": "Logged out successfully."
}
```

**Note:** This revokes the current token. User must login again to get a new token.

---

#### 5. Create Booking (Protected)

**Endpoint:** `POST /api/bookings`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "event_id": 1,
  "hotel_id": 1,
  "package_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "guests_count": 2,
  "flight_number": "AA123",
  "flight_date": "2026-02-01",
  "special_instructions": "Late check-in requested"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Booking created successfully.",
  "data": {
    "booking": {
      "id": 1,
      "reference": "BOOK-20260107-ABC",
      "status": "pending",
      "full_name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Note:** The booking is automatically linked to the authenticated user's `user_id`.

---

#### 6. Get User's Bookings

**Endpoint:** `GET /api/bookings`

**Headers:**
```
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "booking_reference": "BOOK-20260107-ABC",
      "status": "pending",
      "full_name": "John Doe",
      "email": "john@example.com",
      "checkin_date": "2026-02-01",
      "checkout_date": "2026-02-05",
      "price": 1500.00,
      "event": {
        "id": 1,
        "name": "SEAFOOD4AFRICA",
        "slug": "seafood4africa"
      },
      "hotel": {
        "id": 1,
        "name": "WEST POINT DAKHLA",
        "slug": "west-point-dakhla"
      },
      "package": {
        "id": 1,
        "nom_package": "Package Vue Mer",
        "prix_ttc": 1500.00
      }
    }
  ]
}
```

**Note:** Returns bookings where `user_id` matches authenticated user, or where email matches (for legacy bookings).

---

#### 7. Get Single Booking

**Endpoint:** `GET /api/bookings/{id}`

**Headers:**
```
Authorization: Bearer {token}
```

**Response:** Similar to booking list item, with full details.

---

#### 8. Update Booking Status

**Endpoint:** `PATCH /api/bookings/{id}/status`

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request:**
```json
{
  "status": "confirmed"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Booking status updated successfully.",
  "data": {
    "id": 1,
    "status": "confirmed"
  }
}
```

---

## Frontend Integration

### React/Next.js Example

#### 1. Authentication Service

```typescript
// services/auth.ts
const API_URL = 'http://localhost:8000/api';

interface User {
  id: number;
  name: string;
  email: string;
}

interface AuthResponse {
  token: string;
  user: User;
}

export const authService = {
  async register(name: string, email: string, password: string): Promise<AuthResponse> {
    const response = await fetch(`${API_URL}/register`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        name,
        email,
        password,
        password_confirmation: password,
      }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Registration failed');
    }

    return response.json();
  },

  async login(email: string, password: string): Promise<AuthResponse> {
    const response = await fetch(`${API_URL}/login`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({ email, password }),
    });

    if (!response.ok) {
      const error = await response.json();
      throw new Error(error.message || 'Login failed');
    }

    return response.json();
  },

  async logout(token: string): Promise<void> {
    await fetch(`${API_URL}/logout`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });
  },

  async getUser(token: string): Promise<User> {
    const response = await fetch(`${API_URL}/user`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Failed to get user');
    }

    const data = await response.json();
    return data.user;
  },
};
```

#### 2. Token Storage

```typescript
// utils/tokenStorage.ts
export const tokenStorage = {
  getToken(): string | null {
    if (typeof window === 'undefined') return null;
    return localStorage.getItem('auth_token');
  },

  setToken(token: string): void {
    if (typeof window === 'undefined') return;
    localStorage.setItem('auth_token', token);
  },

  removeToken(): void {
    if (typeof window === 'undefined') return;
    localStorage.removeItem('auth_token');
  },
};
```

#### 3. API Client with Auth

```typescript
// services/api.ts
import { tokenStorage } from '@/utils/tokenStorage';

const API_URL = 'http://localhost:8000/api';

async function apiRequest<T>(
  endpoint: string,
  options: RequestInit = {}
): Promise<T> {
  const token = tokenStorage.getToken();

  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...options.headers,
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const response = await fetch(`${API_URL}${endpoint}`, {
    ...options,
    headers,
  });

  if (response.status === 401) {
    // Token expired or invalid
    tokenStorage.removeToken();
    window.location.href = '/login';
    throw new Error('Unauthenticated');
  }

  if (!response.ok) {
    const error = await response.json();
    throw new Error(error.message || 'Request failed');
  }

  return response.json();
}

export const api = {
  bookings: {
    create(data: any) {
      return apiRequest('/bookings', {
        method: 'POST',
        body: JSON.stringify(data),
      });
    },

    list() {
      return apiRequest<{ success: boolean; data: any[] }>('/bookings');
    },

    get(id: number) {
      return apiRequest(`/bookings/${id}`);
    },
  },
};
```

#### 4. React Hook Example

```typescript
// hooks/useAuth.ts
import { useState, useEffect } from 'react';
import { authService } from '@/services/auth';
import { tokenStorage } from '@/utils/tokenStorage';

interface User {
  id: number;
  name: string;
  email: string;
}

export function useAuth() {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = tokenStorage.getToken();
    if (token) {
      authService.getUser(token)
        .then(setUser)
        .catch(() => tokenStorage.removeToken())
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  const login = async (email: string, password: string) => {
    const { token, user } = await authService.login(email, password);
    tokenStorage.setToken(token);
    setUser(user);
  };

  const register = async (name: string, email: string, password: string) => {
    const { token, user } = await authService.register(name, email, password);
    tokenStorage.setToken(token);
    setUser(user);
  };

  const logout = async () => {
    const token = tokenStorage.getToken();
    if (token) {
      await authService.logout(token);
    }
    tokenStorage.removeToken();
    setUser(null);
  };

  return {
    user,
    loading,
    login,
    register,
    logout,
    isAuthenticated: !!user,
  };
}
```

#### 5. Login Component Example

```tsx
// components/LoginForm.tsx
import { useState } from 'react';
import { useAuth } from '@/hooks/useAuth';

export function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const { login } = useAuth();

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError('');

    try {
      await login(email, password);
      // Redirect to dashboard or booking page
      window.location.href = '/bookings';
    } catch (err: any) {
      setError(err.message || 'Login failed');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      {error && <div className="error">{error}</div>}
      
      <input
        type="email"
        placeholder="Email"
        value={email}
        onChange={(e) => setEmail(e.target.value)}
        required
      />
      
      <input
        type="password"
        placeholder="Password"
        value={password}
        onChange={(e) => setPassword(e.target.value)}
        required
      />
      
      <button type="submit">Login</button>
    </form>
  );
}
```

#### 6. Protected Route Example

```tsx
// components/ProtectedRoute.tsx
import { useAuth } from '@/hooks/useAuth';
import { Navigate } from 'react-router-dom';

export function ProtectedRoute({ children }: { children: React.ReactNode }) {
  const { isAuthenticated, loading } = useAuth();

  if (loading) {
    return <div>Loading...</div>;
  }

  if (!isAuthenticated) {
    return <Navigate to="/login" />;
  }

  return <>{children}</>;
}
```

---

### Vue.js Example

```javascript
// composables/useAuth.js
import { ref, computed } from 'vue';
import { authService } from '@/services/auth';
import { tokenStorage } from '@/utils/tokenStorage';

const user = ref(null);
const loading = ref(true);

export function useAuth() {
  const isAuthenticated = computed(() => !!user.value);

  const init = async () => {
    const token = tokenStorage.getToken();
    if (token) {
      try {
        user.value = await authService.getUser(token);
      } catch {
        tokenStorage.removeToken();
      }
    }
    loading.value = false;
  };

  const login = async (email, password) => {
    const { token, user: userData } = await authService.login(email, password);
    tokenStorage.setToken(token);
    user.value = userData;
  };

  const logout = async () => {
    const token = tokenStorage.getToken();
    if (token) {
      await authService.logout(token);
    }
    tokenStorage.removeToken();
    user.value = null;
  };

  return {
    user,
    loading,
    isAuthenticated,
    init,
    login,
    logout,
  };
}
```

---

## Security Features

### 1. Token Security

- **Token Name:** `'booking-app'` (identifies token purpose)
- **Token Storage:** Frontend stores in localStorage/sessionStorage
- **Token Expiration:** Tokens don't expire by default (can be configured)
- **Token Revocation:** Logout revokes current token

### 2. Password Security

- **Hashing:** Passwords hashed using `bcrypt`
- **Validation:** Minimum 8 characters, mixed case, numbers required
- **Confirmation:** Password confirmation required on registration

### 3. HttpOnly Cookies (Optional)

For enhanced security, you can use HttpOnly cookies instead of tokens:

**Backend Configuration:**
```php
// In AuthController, instead of returning token:
$request->user()->createToken('booking-app');
// Cookie is automatically set by Sanctum
```

**Frontend:**
- No need to store/manage tokens
- Cookies automatically sent with requests
- JavaScript cannot access cookies (XSS protection)

**Enable in `.env`:**
```env
SESSION_DRIVER=cookie
SESSION_HTTP_ONLY=true
SESSION_SECURE_COOKIE=true  # HTTPS only
```

### 4. CORS Configuration

Edit `config/cors.php`:

```php
'paths' => ['api/*'],
'allowed_origins' => explode(',', env('CORS_ALLOWED_ORIGINS', '')),
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => true, // Important for cookies
```

### 5. CSP Compatibility

The Content Security Policy middleware is configured to work with Sanctum:

- ✅ Allows API calls (`connect-src`)
- ✅ Allows Sanctum domains
- ✅ No conflicts with Tailwind/Alpine/Livewire

See `CSP_SETUP.md` for details.

---

## Role-Based Access Control

### Roles

- **`user`**: Default role for API registrations
- **`admin`**: Admin dashboard access

### Checking Roles

**In Controllers:**
```php
if (auth()->user()->isAdmin()) {
    // Admin-only logic
}
```

**In Middleware:**
```php
Route::middleware('role:admin')->group(function () {
    // Admin routes
});
```

### Admin Routes

All admin routes in `routes/web.php` are protected:

```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/bookings', ...);
    Route::get('/admin/events', ...);
    // etc.
});
```

**Access Denied (403):**
If a non-admin user tries to access admin routes, they'll receive:
```json
{
  "message": "Unauthorized. Required role: admin"
}
```

---

## Troubleshooting

### Issue: "Unauthenticated" Error

**Symptoms:**
- API returns 401 Unauthorized
- Token seems valid

**Solutions:**

1. **Check Token Format:**
   ```
   Authorization: Bearer {token}
   ```
   Ensure there's a space between "Bearer" and the token.

2. **Verify Token Storage:**
   ```javascript
   console.log(localStorage.getItem('auth_token'));
   ```

3. **Check Token Expiration:**
   Tokens don't expire by default, but check if token was revoked:
   ```bash
   php artisan tinker
   >>> $user = User::find(1);
   >>> $user->tokens; // Check if token exists
   ```

4. **Verify Sanctum Configuration:**
   ```bash
   php artisan config:show sanctum
   ```

### Issue: CORS Errors

**Symptoms:**
- Browser console shows CORS errors
- Requests fail with "No 'Access-Control-Allow-Origin' header"

**Solutions:**

1. **Check CORS Configuration:**
   ```php
   // config/cors.php
   'allowed_origins' => ['http://localhost:3000'],
   'supports_credentials' => true,
   ```

2. **Verify Frontend Domain:**
   ```env
   CORS_ALLOWED_ORIGINS=http://localhost:3000,https://yourfrontend.com
   ```

3. **Check Request Headers:**
   ```javascript
   headers: {
     'Content-Type': 'application/json',
     'Accept': 'application/json',
     'Authorization': `Bearer ${token}`,
   }
   ```

### Issue: Token Not Persisting

**Symptoms:**
- User logged in but token lost on page refresh

**Solutions:**

1. **Check Storage Method:**
   ```javascript
   // Use localStorage (persists) not sessionStorage (clears on tab close)
   localStorage.setItem('auth_token', token);
   ```

2. **Verify Token Retrieval:**
   ```javascript
   const token = localStorage.getItem('auth_token');
   if (!token) {
     // Redirect to login
   }
   ```

### Issue: Booking Not Linked to User

**Symptoms:**
- Booking created but `user_id` is null

**Solutions:**

1. **Verify Authentication:**
   ```php
   // In BookingController@store
   dd($request->user()); // Should return User model
   ```

2. **Check Route Middleware:**
   ```php
   Route::middleware('auth:sanctum')->post('/bookings', ...);
   ```

3. **Verify Token in Request:**
   ```javascript
   console.log('Token:', localStorage.getItem('auth_token'));
   ```

### Issue: Admin Routes Not Accessible

**Symptoms:**
- 403 Forbidden on admin routes
- User is authenticated but not admin

**Solutions:**

1. **Check User Role:**
   ```bash
   php artisan tinker
   >>> User::find(1)->role; // Should be 'admin'
   ```

2. **Update User Role:**
   ```bash
   php artisan tinker
   >>> User::find(1)->update(['role' => 'admin']);
   ```

3. **Verify Middleware:**
   ```php
   Route::middleware(['auth', 'role:admin'])->group(...);
   ```

---

## Best Practices

### 1. Token Management

✅ **DO:**
- Store tokens securely (localStorage for web apps)
- Include token in Authorization header
- Handle token expiration gracefully
- Revoke tokens on logout

❌ **DON'T:**
- Store tokens in URL parameters
- Log tokens in console (production)
- Share tokens between users
- Store tokens in plain text files

### 2. Password Security

✅ **DO:**
- Use strong passwords (min 8 chars, mixed case, numbers)
- Hash passwords server-side (already done)
- Validate password confirmation
- Never send passwords in API responses

❌ **DON'T:**
- Store plain text passwords
- Send passwords via email
- Use weak passwords
- Reuse passwords

### 3. API Security

✅ **DO:**
- Use HTTPS in production
- Validate all input
- Sanitize output
- Rate limit endpoints
- Use CSRF protection for web routes

❌ **DON'T:**
- Expose sensitive data in responses
- Trust client-side validation only
- Skip authentication checks
- Allow SQL injection

### 4. Error Handling

✅ **DO:**
```javascript
try {
  const response = await api.bookings.create(data);
} catch (error) {
  if (error.status === 401) {
    // Handle unauthorized
    redirectToLogin();
  } else if (error.status === 422) {
    // Handle validation errors
    showValidationErrors(error.errors);
  } else {
    // Handle other errors
    showGenericError();
  }
}
```

### 5. Testing

**Test Authentication Flow:**
```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"Test1234","password_confirmation":"Test1234"}'

# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"Test1234"}'

# Get User (replace TOKEN)
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer TOKEN"

# Create Booking (replace TOKEN)
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"event_id":1,"hotel_id":1,"package_id":1,"full_name":"Test User","email":"test@example.com"}'
```

---

## Additional Resources

- [Laravel Sanctum Documentation](https://laravel.com/docs/sanctum)
- [Sanctum SPA Authentication](https://laravel.com/docs/sanctum#spa-authentication)
- [API Token Authentication](https://laravel.com/docs/sanctum#api-token-authentication)
- [CSP Setup Guide](./CSP_SETUP.md)

---

## Quick Reference

### Environment Variables
```env
SANCTUM_STATEFUL_DOMAINS=localhost:3000,yourfrontend.com
SESSION_HTTP_ONLY=true
SESSION_SECURE_COOKIE=true
CORS_ALLOWED_ORIGINS=http://localhost:3000
```

### Token Format
```
Authorization: Bearer {token}
```

### Response Format
```json
{
  "token": "1|...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Error Codes
- `401`: Unauthenticated (no/invalid token)
- `403`: Unauthorized (wrong role)
- `422`: Validation error
- `500`: Server error

---

**Last Updated:** January 2026
**Version:** 1.0.0

