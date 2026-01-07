# API Contract Summary - Quick Reference

**For Frontend & Backend Teams**

---

## 🔑 Authentication Endpoints

### Register
```
POST /api/register
Content-Type: application/json

Request:
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}

Response (201):
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

### Login
```
POST /api/login
Content-Type: application/json

Request:
{
  "email": "john@example.com",
  "password": "Password123"
}

Response (200):
{
  "token": "2|xyz789...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

---

## 📋 Booking Endpoints

### Create Booking (Protected)
```
POST /api/bookings
Authorization: Bearer {token}
Content-Type: application/json

Request:
{
  "event_id": 1,
  "hotel_id": 1,
  "package_id": 1,
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+1234567890",
  "guests_count": 2
  // ❌ DO NOT SEND: user_id, password, password_confirmation
}

Response (201):
{
  "success": true,
  "message": "Booking created successfully.",
  "data": {
    "booking": {
      "id": 1,
      "reference": "BOOK-20260107-ABC",
      "booking_reference": "BOOK-20260107-ABC",
      "status": "pending",
      "full_name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

**Important:** `user_id` is automatically set by backend from authenticated user.

---

## ⚠️ Error Responses

### Email Already Exists (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Cet email est déjà utilisé."]
  }
}
```

### Invalid Credentials (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Les identifiants fournis sont incorrects."]
  }
}
```

### Unauthenticated (401)
```json
{
  "message": "Unauthenticated."
}
```

---

## 🔒 Security Headers

**Required for all API requests:**
```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}  // For protected routes
```

---

## ✅ Field Validation Rules

| Field | Rules | Error Message (FR) |
|-------|-------|-------------------|
| `name` | required, max:255 | "Le nom est obligatoire." |
| `email` | required, email, unique | "Cet email est déjà utilisé." |
| `password` | required, min:8, regex | "Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre." |
| `password_confirmation` | required, matches password | "La confirmation du mot de passe ne correspond pas." |

**Password Regex:** `/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/`

---

## 🚫 Fields NOT to Send

- ❌ `user_id` in booking request (auto-set by backend)
- ❌ `password` in booking request
- ❌ `password_confirmation` in booking request

---

## 📚 Full Documentation

- **Complete Guide:** `AUTH_SANCTUM_GUIDE.md`
- **Frontend Contract:** `FRONTEND_BACKEND_CONTRACT.md`
- **Integration Checklist:** `INTEGRATION_CHECKLIST.md`

---

**Last Updated:** January 2026

