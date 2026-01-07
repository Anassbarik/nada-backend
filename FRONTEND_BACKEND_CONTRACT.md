# Frontend ↔ Backend Contract - Authentication & Booking Flow

**Version:** 1.0.0  
**Last Updated:** January 2026  
**Status:** ✅ Production Ready

This document defines the exact contract between Frontend (React/Next.js/Vue) and Backend (Laravel + Sanctum) for authentication and booking creation.

---

## Table of Contents

1. [Authentication Flow](#authentication-flow)
2. [API Endpoints](#api-endpoints)
3. [Field Mapping](#field-mapping)
4. [Error Handling](#error-handling)
5. [Security Requirements](#security-requirements)
6. [Testing Examples](#testing-examples)

---

## Authentication Flow

### Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (React/Next.js)                  │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 1. User fills registration form
                            ▼
                    ┌───────────────┐
                    │  Registration │
                    │     Form      │
                    └───────────────┘
                            │
                            │ POST /api/register
                            │ {
                            │   name, email, password,
                            │   password_confirmation
                            │ }
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel)                        │
└─────────────────────────────────────────────────────────────┘
                            │
                            │ 2. Validate & Create User
                            │    - Validate fields
                            │    - Hash password
                            │    - Set role='user'
                            │    - Create Sanctum token
                            ▼
                    ┌───────────────┐
                    │   Response    │
                    │ {token, user} │
                    └───────────────┘
                            │
                            │ 3. Store token
                            │    localStorage.setItem('token', ...)
                            ▼
                    ┌───────────────┐
                    │  Booking Form │
                    │  (pre-filled) │
                    └───────────────┘
                            │
                            │ POST /api/bookings
                            │ Authorization: Bearer {token}
                            │ {
                            │   full_name, email, flight_num,
                            │   ... (other booking fields)
                            │ }
                            ▼
                            │ 4. Create Booking
                            │    - Link to user_id (auto)
                            │    - Generate invoice
                            │    - Send notifications
                            ▼
                    ┌───────────────┐
                    │   Success!    │
                    └───────────────┘
```

---

## API Endpoints

### 1. Register User

**Endpoint:** `POST /api/register`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

**Field Requirements:**
- `name`: required, string, max:255
- `email`: required, valid email format, unique in users table
- `password`: required, min:8 chars, must contain:
  - At least one lowercase letter (a-z)
  - At least one uppercase letter (A-Z)
  - At least one number (0-9)
- `password_confirmation`: required, must match `password`

**Success Response (201 Created):**
```json
{
  "token": "1|abcdef1234567890abcdef1234567890abcdef1234567890",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

**Error Response (422 Validation Error):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Cet email est déjà utilisé."
    ],
    "password": [
      "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre."
    ]
  }
}
```

**Error Response (422 - Email Already Exists):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Cet email est déjà utilisé."
    ]
  }
}
```

**Frontend Handling:**
```javascript
if (error.response?.status === 422 && 
    error.response.data.errors.email?.[0] === 'Cet email est déjà utilisé.') {
  // Redirect to login or show login prompt
  alert('Email existe déjà. Connectez-vous: /login');
  window.location.href = '/login';
}
```

---

### 2. Login User

**Endpoint:** `POST /api/login`

**Request Headers:**
```
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "Password123"
}
```

**Success Response (200 OK):**
```json
{
  "token": "2|xyz9876543210xyz9876543210xyz9876543210",
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
    "email": [
      "Les identifiants fournis sont incorrects."
    ]
  }
}
```

---

### 3. Create Booking (Protected)

**Endpoint:** `POST /api/bookings`

**Request Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "event_id": 1,
  "hotel_id": 1,
  "package_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "company": "Acme Corp",
  "flight_number": "AA123",
  "flight_date": "2026-02-01",
  "flight_time": "14:30",
  "airport": "CDG",
  "guests_count": 2,
  "resident_name_1": "John Doe",
  "resident_name_2": "Jane Doe",
  "special_instructions": "Late check-in requested"
}
```

**Important Notes:**
- `user_id` is **automatically set** by backend from `auth()->id()`
- Frontend should **NOT** send `user_id` in request
- Frontend should **NOT** send `password` or `password_confirmation` in booking request
- `email` field in booking can differ from user's email (guest booking scenario)

**Success Response (201 Created):**
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

**Error Response (401 Unauthorized):**
```json
{
  "message": "Unauthenticated."
}
```

**Error Response (422 Validation Error):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "package_id": [
      "The selected package id is invalid."
    ],
    "guests_count": [
      "The guests count must be at least 1."
    ]
  }
}
```

---

### 4. Get Authenticated User

**Endpoint:** `GET /api/user`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

### 5. Get User's Bookings

**Endpoint:** `GET /api/bookings`

**Request Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Success Response (200 OK):**
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
      "event": { "id": 1, "name": "SEAFOOD4AFRICA", "slug": "seafood4africa" },
      "hotel": { "id": 1, "name": "WEST POINT DAKHLA", "slug": "west-point-dakhla" },
      "package": { "id": 1, "nom_package": "Package Vue Mer", "prix_ttc": 1500.00 }
    }
  ]
}
```

---

## Field Mapping

### Database Field Names (Backend)

**Users Table:**
```
id              → integer (auto-increment)
name            → varchar(255)
email           → varchar(255), unique
password        → varchar(255), hashed
role            → enum('user', 'admin'), default='user'
email_verified_at → timestamp, nullable
remember_token  → varchar(100), nullable
created_at      → timestamp
updated_at      → timestamp
```

**Bookings Table:**
```
id                  → integer (auto-increment)
user_id             → bigint, nullable, foreign key → users.id
event_id            → bigint, foreign key → events.id
hotel_id            → bigint, foreign key → hotels.id
package_id          → bigint, foreign key → hotel_packages.id
full_name           → varchar(255), nullable
email               → varchar(255), nullable
phone               → varchar(255), nullable
flight_number       → varchar(20), nullable
flight_date         → date, nullable
flight_time         → time, nullable
airport             → varchar(255), nullable
guests_count        → integer
price               → decimal(10,2), nullable
status              → enum('pending','confirmed','cancelled','refunded')
... (other fields)
```

### API Field Names (Frontend ↔ Backend)

| Frontend Sends | Backend Receives | Backend Stores | Notes |
|----------------|------------------|----------------|-------|
| `name` | `name` | `users.name` | Registration only |
| `email` | `email` | `users.email` | Registration only |
| `password` | `password` | `users.password` (hashed) | Registration only |
| `password_confirmation` | `password_confirmation` | - | Validation only |
| `full_name` | `full_name` | `bookings.full_name` | Booking creation |
| `email` | `email` | `bookings.email` | Booking creation (can differ from user email) |
| `phone` | `phone` | `bookings.phone` | Booking creation |
| `flight_number` | `flight_number` | `bookings.flight_number` | Booking creation |
| `flight_date` | `flight_date` | `bookings.flight_date` | Format: YYYY-MM-DD |
| `flight_time` | `flight_time` | `bookings.flight_time` | Format: HH:mm |
| `user_id` | ❌ **NOT SENT** | `bookings.user_id` | **Auto-set by backend** from `auth()->id()` |

---

## Error Handling

### Standard Error Response Format

```json
{
  "message": "Error message description",
  "errors": {
    "field_name": [
      "Error message 1",
      "Error message 2"
    ]
  }
}
```

### HTTP Status Codes

| Code | Meaning | When It Occurs |
|------|---------|----------------|
| 200 | OK | Successful GET/PATCH request |
| 201 | Created | Successful POST request (register, booking) |
| 401 | Unauthorized | Missing or invalid token |
| 403 | Forbidden | Valid token but insufficient permissions |
| 422 | Validation Error | Invalid input data |
| 500 | Server Error | Internal server error |

### Common Error Scenarios

#### 1. Email Already Exists (422)

**Response:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": [
      "Cet email est déjà utilisé."
    ]
  }
}
```

**Frontend Handling:**
```javascript
if (error.response?.status === 422) {
  const emailError = error.response.data.errors?.email?.[0];
  if (emailError === 'Cet email est déjà utilisé.') {
    // Show login prompt
    setError('Cet email existe déjà. Veuillez vous connecter.');
    setTimeout(() => {
      router.push('/login');
    }, 2000);
  }
}
```

#### 2. Invalid Token (401)

**Response:**
```json
{
  "message": "Unauthenticated."
}
```

**Frontend Handling:**
```javascript
if (error.response?.status === 401) {
  // Clear token and redirect to login
  localStorage.removeItem('token');
  router.push('/login');
}
```

#### 3. Password Validation Failed (422)

**Response:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "password": [
      "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre."
    ],
    "password_confirmation": [
      "La confirmation du mot de passe ne correspond pas."
    ]
  }
}
```

---

## Security Requirements

### Token Management

**Token Format:**
```
Bearer {token}
```

**Token Storage:**
- ✅ **Recommended:** `localStorage` (persists across sessions)
- ⚠️ **Alternative:** `sessionStorage` (clears on tab close)
- ❌ **Never:** URL parameters, cookies (unless HttpOnly)

**Token Usage:**
```javascript
// Set token after registration/login
localStorage.setItem('token', response.data.token);

// Include in all API requests
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

// Or per-request
axios.post('/api/bookings', data, {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
```

### CORS Configuration

**Backend (.env):**
```env
CORS_ALLOWED_ORIGINS=http://localhost:3000,https://yourfrontend.com
```

**Frontend:** Ensure requests include:
```javascript
headers: {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${token}`
}
```

### Password Requirements

- **Minimum Length:** 8 characters
- **Must Contain:**
  - At least one lowercase letter (a-z)
  - At least one uppercase letter (A-Z)
  - At least one number (0-9)
- **Example Valid Passwords:**
  - ✅ `Password123`
  - ✅ `MySecure1`
  - ✅ `Test2024`
- **Example Invalid Passwords:**
  - ❌ `password` (no uppercase, no number)
  - ❌ `PASSWORD123` (no lowercase)
  - ❌ `Password` (no number)
  - ❌ `Pass123` (too short)

---

## Testing Examples

### cURL Examples

#### 1. Register User

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'
```

**Expected Response:**
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

#### 2. Login User

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "Password123"
  }'
```

#### 3. Create Booking (Replace TOKEN)

```bash
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer TOKEN_HERE" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "event_id": 1,
    "hotel_id": 1,
    "package_id": 1,
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "guests_count": 2
  }'
```

#### 4. Get User Info

```bash
curl http://localhost:8000/api/user \
  -H "Authorization: Bearer TOKEN_HERE" \
  -H "Accept: application/json"
```

---

### Frontend Integration Example (React/Next.js)

#### Complete Registration + Booking Flow

```typescript
// pages/booking.tsx or components/BookingForm.tsx
import { useState } from 'react';
import axios from 'axios';
import { useRouter } from 'next/router';

export default function BookingForm() {
  const router = useRouter();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    const formData = new FormData(e.currentTarget);
    const data = {
      // Registration fields
      name: formData.get('name') as string,
      email: formData.get('email') as string,
      password: formData.get('password') as string,
      password_confirmation: formData.get('password_confirmation') as string,
      // Booking fields
      event_id: parseInt(formData.get('event_id') as string),
      hotel_id: parseInt(formData.get('hotel_id') as string),
      package_id: parseInt(formData.get('package_id') as string),
      full_name: formData.get('full_name') as string,
      phone: formData.get('phone') as string,
      flight_number: formData.get('flight_number') as string,
      guests_count: parseInt(formData.get('guests_count') as string),
    };

    try {
      // Step 1: Register user
      const registerResponse = await axios.post(
        'http://localhost:8000/api/register',
        {
          name: data.name,
          email: data.email,
          password: data.password,
          password_confirmation: data.password_confirmation,
        },
        {
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        }
      );

      const { token, user } = registerResponse.data;

      // Step 2: Store token
      localStorage.setItem('token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

      // Step 3: Create booking (remove password fields)
      const bookingData = {
        event_id: data.event_id,
        hotel_id: data.hotel_id,
        package_id: data.package_id,
        full_name: data.full_name,
        email: data.email, // Can be same as user email or different
        phone: data.phone,
        flight_number: data.flight_number,
        guests_count: data.guests_count,
        // user_id is automatically set by backend - DO NOT send it
      };

      const bookingResponse = await axios.post(
        'http://localhost:8000/api/bookings',
        bookingData,
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
        }
      );

      // Step 4: Success - redirect
      router.push(`/booking-success?id=${bookingResponse.data.data.booking.id}`);

    } catch (err: any) {
      setLoading(false);

      // Handle email already exists
      if (
        err.response?.status === 422 &&
        err.response.data.errors?.email?.[0] === 'Cet email est déjà utilisé.'
      ) {
        setError('Cet email existe déjà. Veuillez vous connecter.');
        setTimeout(() => {
          router.push('/login');
        }, 2000);
        return;
      }

      // Handle validation errors
      if (err.response?.status === 422) {
        const errors = err.response.data.errors;
        const firstError = Object.values(errors)[0]?.[0];
        setError(firstError || 'Erreur de validation');
        return;
      }

      // Handle other errors
      setError(err.response?.data?.message || 'Une erreur est survenue');
    }
  };

  return (
    <form onSubmit={handleSubmit}>
      <fieldset className="mandatory-account">
        <legend>Création de compte obligatoire</legend>

        <input
          name="name"
          placeholder="Nom complet *"
          required
          maxLength={255}
        />

        <input
          name="email"
          type="email"
          placeholder="Email *"
          required
        />

        <input
          name="password"
          type="password"
          placeholder="Mot de passe (8+ caractères) *"
          required
          minLength={8}
          pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$"
          title="Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre"
        />

        <input
          name="password_confirmation"
          type="password"
          placeholder="Confirmer le mot de passe *"
          required
        />
      </fieldset>

      <fieldset className="booking-details">
        <legend>Détails de la réservation</legend>

        <input name="event_id" type="hidden" value="1" />
        <input name="hotel_id" type="hidden" value="1" />
        <input name="package_id" type="hidden" value="1" />

        <input
          name="full_name"
          placeholder="Nom complet *"
          required
        />

        <input
          name="phone"
          type="tel"
          placeholder="Téléphone *"
          required
        />

        <input
          name="flight_number"
          placeholder="Numéro de vol"
        />

        <input
          name="guests_count"
          type="number"
          placeholder="Nombre de personnes *"
          min="1"
          required
        />
      </fieldset>

      {error && <div className="error">{error}</div>}

      <button type="submit" disabled={loading}>
        {loading ? 'Traitement...' : 'Confirmer la réservation'}
      </button>
    </form>
  );
}
```

---

### Frontend Integration Example (Vue.js)

```vue
<template>
  <form @submit.prevent="handleSubmit">
    <fieldset class="mandatory-account">
      <legend>Création de compte obligatoire</legend>
      
      <input v-model="form.name" placeholder="Nom complet *" required />
      <input v-model="form.email" type="email" placeholder="Email *" required />
      <input v-model="form.password" type="password" placeholder="Mot de passe *" required minlength="8" />
      <input v-model="form.password_confirmation" type="password" placeholder="Confirmer *" required />
    </fieldset>

    <fieldset class="booking-details">
      <legend>Détails de la réservation</legend>
      <!-- Booking fields -->
    </fieldset>

    <button type="submit" :disabled="loading">
      {{ loading ? 'Traitement...' : 'Confirmer' }}
    </button>
  </form>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  // ... booking fields
});

const loading = ref(false);
const error = ref('');

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';

  try {
    // Register
    const { data } = await axios.post('/api/register', {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      password_confirmation: form.value.password_confirmation,
    });

    // Store token
    localStorage.setItem('token', data.token);
    axios.defaults.headers.common['Authorization'] = `Bearer ${data.token}`;

    // Create booking
    const bookingData = { ...form.value };
    delete bookingData.password;
    delete bookingData.password_confirmation;

    await axios.post('/api/bookings', bookingData);

    // Success
    router.push('/booking-success');
  } catch (err) {
    if (err.response?.status === 422 && 
        err.response.data.errors?.email?.[0] === 'Cet email est déjà utilisé.') {
      error.value = 'Email existe déjà. Connectez-vous.';
      setTimeout(() => router.push('/login'), 2000);
    } else {
      error.value = err.response?.data?.message || 'Erreur';
    }
  } finally {
    loading.value = false;
  }
};
</script>
```

---

## Validation Rules Summary

### Registration Validation

| Field | Rules | Error Message (French) |
|-------|-------|----------------------|
| `name` | required, string, max:255 | "Le nom est obligatoire." |
| `email` | required, email, unique:users | "Cet email est déjà utilisé." |
| `password` | required, confirmed, min:8, regex | "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre." |
| `password_confirmation` | required, must match password | "La confirmation du mot de passe ne correspond pas." |

### Booking Validation

| Field | Rules | Notes |
|-------|-------|-------|
| `event_id` | required, exists:events | Must be valid event |
| `hotel_id` | required, exists:hotels | Must be valid hotel |
| `package_id` | required, exists:hotel_packages | Must be valid package |
| `full_name` | required, string | Guest name |
| `email` | required, email | Guest email (can differ from user email) |
| `phone` | nullable, string | Guest phone |
| `guests_count` | required, integer, min:1 | Number of guests |
| `user_id` | ❌ **NOT VALIDATED** | Auto-set by backend |

---

## Quick Reference Checklist

### Backend Checklist ✅

- [x] User model has `role` field (enum: 'user', 'admin')
- [x] Booking model has `user_id` foreign key
- [x] AuthController returns exact format: `{token, user: {id, name, email}}`
- [x] Password validation: min:8, regex for uppercase/lowercase/number
- [x] BookingController auto-sets `user_id` from `auth()->id()`
- [x] Error messages in French
- [x] Token name: `'booking-app'`
- [x] Routes protected with `auth:sanctum`

### Frontend Checklist ✅

- [ ] Registration form includes: name, email, password, password_confirmation
- [ ] Password validation matches backend (regex pattern)
- [ ] Token stored in localStorage after registration/login
- [ ] Authorization header included: `Bearer {token}`
- [ ] Booking request does NOT include `user_id`
- [ ] Booking request does NOT include `password` fields
- [ ] Error handling for 422 (email exists) redirects to login
- [ ] Error handling for 401 clears token and redirects to login
- [ ] French error messages displayed to user

---

## Support & Contact

For questions or issues:
- **Backend Team:** Check `AUTH_SANCTUM_GUIDE.md`
- **Frontend Team:** Reference this contract document
- **Integration Issues:** Verify field names match exactly

---

**Document Status:** ✅ Approved for Production  
**Last Reviewed:** January 2026

