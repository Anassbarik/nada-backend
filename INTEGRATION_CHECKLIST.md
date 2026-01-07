# Frontend ↔ Backend Integration Checklist

**Purpose:** Ensure perfect alignment between Frontend and Backend teams before production deployment.

---

## ✅ Backend Implementation Status

### 1. Database Schema
- [x] `users.role` column exists (enum: 'user', 'admin')
- [x] `bookings.user_id` column exists (nullable, foreign key)
- [x] Migrations created and ready to run

### 2. User Model
- [x] `HasApiTokens` trait included
- [x] `role` in fillable array
- [x] `isAdmin()` method exists
- [x] `bookings()` relationship exists

### 3. Booking Model
- [x] `user_id` in fillable array
- [x] `user()` relationship exists

### 4. AuthController
- [x] `register()` method returns exact format: `{token, user: {id, name, email}}`
- [x] `login()` method returns exact format: `{token, user: {id, name, email}}`
- [x] Password validation: min:8, regex for uppercase/lowercase/number
- [x] Error messages in French
- [x] Token name: `'booking-app'`
- [x] Status code: 201 for register, 200 for login

### 5. BookingController
- [x] `store()` method auto-sets `user_id` from `auth()->id()`
- [x] Does NOT require `user_id` in request
- [x] Returns success response with booking data
- [x] Protected with `auth:sanctum` middleware

### 6. Routes
- [x] `POST /api/register` (public)
- [x] `POST /api/login` (public)
- [x] `POST /api/bookings` (protected, requires auth)
- [x] `GET /api/user` (protected)
- [x] `GET /api/bookings` (protected)

### 7. Security
- [x] Sanctum configured
- [x] CORS configured
- [x] CSP compatible
- [x] Admin routes protected with `role:admin`

---

## ✅ Frontend Implementation Checklist

### 1. Registration Form
- [ ] Form includes: `name`, `email`, `password`, `password_confirmation`
- [ ] Password validation matches backend (regex: `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/`)
- [ ] Client-side validation before API call
- [ ] Error messages displayed in French

### 2. Token Management
- [ ] Token stored in `localStorage` after registration/login
- [ ] Token retrieved on page load/refresh
- [ ] Token included in all API requests: `Authorization: Bearer {token}`
- [ ] Token cleared on logout

### 3. Booking Creation Flow
- [ ] Registration → Store token → Create booking (single flow)
- [ ] Booking request does NOT include `user_id`
- [ ] Booking request does NOT include `password` or `password_confirmation`
- [ ] Booking request includes all required fields: `event_id`, `hotel_id`, `package_id`, `full_name`, `email`, etc.

### 4. Error Handling
- [ ] 422 error (email exists) → Show message → Redirect to login
- [ ] 401 error → Clear token → Redirect to login
- [ ] 422 validation errors → Display field-specific errors
- [ ] Network errors → Show user-friendly message

### 5. API Client Setup
- [ ] Base URL configured: `http://localhost:8000/api` (dev) or production URL
- [ ] Default headers: `Content-Type: application/json`, `Accept: application/json`
- [ ] Authorization header added automatically when token exists
- [ ] Request/response interceptors for error handling

---

## 🔍 Field Name Verification

### Registration Request
| Frontend Field | Backend Expects | ✅ Match |
|----------------|-----------------|----------|
| `name` | `name` | ✅ |
| `email` | `email` | ✅ |
| `password` | `password` | ✅ |
| `password_confirmation` | `password_confirmation` | ✅ |

### Registration Response
| Backend Returns | Frontend Expects | ✅ Match |
|-----------------|------------------|----------|
| `token` | `token` | ✅ |
| `user.id` | `user.id` | ✅ |
| `user.name` | `user.name` | ✅ |
| `user.email` | `user.email` | ✅ |

### Booking Request
| Frontend Sends | Backend Expects | Backend Auto-Sets | ✅ Match |
|----------------|-----------------|-------------------|----------|
| `event_id` | `event_id` | - | ✅ |
| `hotel_id` | `hotel_id` | - | ✅ |
| `package_id` | `package_id` | - | ✅ |
| `full_name` | `full_name` | - | ✅ |
| `email` | `email` | - | ✅ |
| `user_id` | ❌ **NOT SENT** | `user_id` (from auth) | ✅ |
| `password` | ❌ **NOT SENT** | - | ✅ |

---

## 🧪 Integration Testing Steps

### Step 1: Test Registration
```bash
# Test registration endpoint
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'

# Expected: 201 Created with {token, user: {id, name, email}}
```

### Step 2: Test Login
```bash
# Test login endpoint
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "Password123"
  }'

# Expected: 200 OK with {token, user: {id, name, email}}
```

### Step 3: Test Booking Creation
```bash
# Replace TOKEN with actual token from Step 1 or 2
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "event_id": 1,
    "hotel_id": 1,
    "package_id": 1,
    "full_name": "Test User",
    "email": "test@example.com",
    "guests_count": 2
  }'

# Expected: 201 Created with booking data
# Verify: booking.user_id matches authenticated user's id
```

### Step 4: Test Error Handling
```bash
# Test duplicate email
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User 2",
    "email": "test@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'

# Expected: 422 with error message "Cet email est déjà utilisé."

# Test invalid token
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer invalid_token"

# Expected: 401 Unauthorized
```

---

## 📋 Pre-Deployment Checklist

### Backend
- [ ] Run migrations: `php artisan migrate`
- [ ] Seed admin user: `php artisan db:seed --class=AdminSeeder`
- [ ] Test all endpoints with Postman/cURL
- [ ] Verify error messages are in French
- [ ] Check CORS configuration matches frontend domain
- [ ] Verify Sanctum stateful domains include frontend URL
- [ ] Test admin routes require admin role

### Frontend
- [ ] Registration form matches contract exactly
- [ ] Token stored and retrieved correctly
- [ ] Booking form does NOT send `user_id` or `password` fields
- [ ] Error handling implemented for all scenarios
- [ ] Loading states shown during API calls
- [ ] Success redirects work correctly
- [ ] Test with real backend (not mock data)

### Integration
- [ ] End-to-end test: Register → Login → Create Booking
- [ ] Test error scenarios (duplicate email, invalid token, etc.)
- [ ] Verify booking is linked to correct user
- [ ] Test on multiple browsers
- [ ] Test on mobile devices
- [ ] Performance test (API response times)

---

## 🚨 Common Integration Issues

### Issue: "Unauthenticated" on Booking Creation
**Cause:** Token not included in request  
**Fix:** Ensure `Authorization: Bearer {token}` header is set

### Issue: "Email Already Exists" but User Can't Login
**Cause:** User exists but password might be different  
**Fix:** Show login prompt, don't block registration flow

### Issue: Booking Not Linked to User
**Cause:** `user_id` not set or token invalid  
**Fix:** Verify token is valid and `auth()->id()` returns user ID

### Issue: CORS Errors
**Cause:** Frontend domain not in CORS_ALLOWED_ORIGINS  
**Fix:** Add frontend domain to `.env` CORS_ALLOWED_ORIGINS

---

## 📞 Support Contacts

- **Backend Issues:** Check `AUTH_SANCTUM_GUIDE.md`
- **Frontend Issues:** Check `FRONTEND_BACKEND_CONTRACT.md`
- **Integration Issues:** Verify field names match exactly

---

**Last Updated:** January 2026  
**Status:** ✅ Ready for Integration Testing

