# Wallet Dashboard - Full Stack Implementation

**Status:** ✅ Complete  
**Last Updated:** January 2026

---

## ✅ Expected Outcomes - All Implemented

### 1. Registration: User + Wallet(0€) Created Automatically ✅

**Backend:**
- Wallet created automatically on user registration
- Initial balance: 0.00 €

**API Response:**
```json
POST /api/register
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallet": {
      "id": 1,
      "balance": "0.00",
      "balance_formatted": "0,00 €"
    }
  }
}
```

---

### 2. Refund: Booking Status='refunded' → Wallet Credited Instantly ✅

**Backend Flow:**
1. Admin processes refund via `POST /admin/bookings/{booking}/refund`
2. Booking status → `'refunded'`
3. `refund_amount` is set
4. **Wallet automatically credited** (via Booking model event)
5. Room count incremented

**Example:**
```php
// Admin refunds 1500.00
$booking->update([
    'status' => 'refunded',
    'refund_amount' => 1500.00,
]);

// Wallet automatically credited:
// $user->wallet->balance += 1500.00
// New balance: 1500.00 €
```

---

### 3. Dashboard: User Sees Balance, Changes Password ✅

**API Endpoints:**

#### Get Wallet Balance
```
GET /api/wallet
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "wallet": {
      "id": 1,
      "balance": "1500.00",
      "balance_formatted": "1 500,00 €",
      "user_id": 1
    },
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

#### Change Password
```
PUT /api/wallet/password
Authorization: Bearer {token}
Content-Type: application/json

{
  "current_password": "OldPassword123",
  "password": "NewPassword123",
  "password_confirmation": "NewPassword123"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Mot de passe mis à jour avec succès."
}
```

**Error Response:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": ["Le mot de passe actuel est incorrect."]
  }
}
```

---

### 4. Secure: Sanctum Tokens, Role-Based Admin Access ✅

**Security Features:**
- ✅ All wallet endpoints protected with `auth:sanctum`
- ✅ Admin routes protected with `role:admin`
- ✅ Token-based authentication
- ✅ Maximum 5 tokens per user
- ✅ Rate limiting on auth endpoints

**Protected Routes:**
- `GET /api/wallet` - Requires authentication
- `PUT /api/wallet/password` - Requires authentication
- `GET /api/user` - Requires authentication (includes wallet)

---

### 5. French UX: "Mon Portefeuille", "Changer mot de passe", € Currency ✅

**French Labels:**
- "Mon Portefeuille" - Wallet display
- "Changer mot de passe" - Change password
- "Solde" - Balance
- "Mot de passe actuel" - Current password
- "Nouveau mot de passe" - New password
- "Confirmer le mot de passe" - Confirm password

**Currency Formatting:**
- Format: `1 500,00 €` (French format with space thousands separator, comma decimal)
- Raw value: `"1500.00"` (for calculations)
- Formatted value: `"1 500,00 €"` (for display)

**API Response Format:**
```json
{
  "wallet": {
    "balance": "1500.00",           // Raw value for calculations
    "balance_formatted": "1 500,00 €"  // Formatted for display
  }
}
```

---

### 6. Full Stack Sync: Backend Auto-Creates Wallet, Frontend Displays /api/user Data ✅

**Backend Auto-Creation:**
- ✅ Wallet created on registration
- ✅ Wallet created for existing users (migration)
- ✅ Wallet created if missing (on any wallet access)

**Frontend Data Source:**
- ✅ `GET /api/user` - Returns user + wallet data
- ✅ `GET /api/wallet` - Returns wallet + user data
- ✅ Registration/Login responses include wallet

**Frontend Display:**
```typescript
// Get user data (includes wallet)
const response = await axios.get('/api/user', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});

const { user } = response.data;
// user.wallet.balance_formatted = "1 500,00 €"
```

---

## Frontend Integration Examples

### React/Next.js Dashboard Component

```tsx
import { useState, useEffect } from 'react';
import axios from 'axios';

function UserDashboard() {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const [passwordError, setPasswordError] = useState('');

  const token = localStorage.getItem('token');

  useEffect(() => {
    // Get user data (includes wallet)
    axios.get('/api/user', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    })
    .then(res => {
      setUser(res.data.user);
      setLoading(false);
    })
    .catch(err => {
      console.error(err);
      setLoading(false);
    });
  }, [token]);

  const handlePasswordChange = async (e) => {
    e.preventDefault();
    setPasswordError('');

    try {
      await axios.put('/api/wallet/password', passwordForm, {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      });

      alert('Mot de passe mis à jour avec succès.');
      setPasswordForm({
        current_password: '',
        password: '',
        password_confirmation: '',
      });
    } catch (err) {
      if (err.response?.data?.errors) {
        const errors = err.response.data.errors;
        setPasswordError(
          errors.current_password?.[0] || 
          errors.password?.[0] || 
          'Erreur lors de la mise à jour du mot de passe'
        );
      }
    }
  };

  if (loading) {
    return <div>Chargement...</div>;
  }

  return (
    <div className="dashboard">
      <h1>Tableau de bord</h1>

      {/* Wallet Display */}
      <section className="wallet-section">
        <h2>Mon Portefeuille</h2>
        <div className="wallet-card">
          <p className="balance-label">Solde</p>
          <p className="balance-amount">
            {user?.wallet?.balance_formatted || '0,00 €'}
          </p>
        </div>
      </section>

      {/* Password Change Form */}
      <section className="password-section">
        <h2>Changer mot de passe</h2>
        <form onSubmit={handlePasswordChange}>
          {passwordError && (
            <div className="error">{passwordError}</div>
          )}

          <div>
            <label>Mot de passe actuel</label>
            <input
              type="password"
              value={passwordForm.current_password}
              onChange={(e) => setPasswordForm({
                ...passwordForm,
                current_password: e.target.value,
              })}
              required
            />
          </div>

          <div>
            <label>Nouveau mot de passe</label>
            <input
              type="password"
              value={passwordForm.password}
              onChange={(e) => setPasswordForm({
                ...passwordForm,
                password: e.target.value,
              })}
              required
              minLength={8}
            />
          </div>

          <div>
            <label>Confirmer le mot de passe</label>
            <input
              type="password"
              value={passwordForm.password_confirmation}
              onChange={(e) => setPasswordForm({
                ...passwordForm,
                password_confirmation: e.target.value,
              })}
              required
            />
          </div>

          <button type="submit">Mettre à jour le mot de passe</button>
        </form>
      </section>
    </div>
  );
}

export default UserDashboard;
```

---

## API Endpoints Summary

### Wallet Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/wallet` | Get user's wallet | ✅ Yes |
| PUT | `/api/wallet/password` | Change password | ✅ Yes |

### User Endpoints (Include Wallet)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/user` | Get user info + wallet | ✅ Yes |
| POST | `/api/register` | Register + create wallet | ❌ No |
| POST | `/api/login` | Login + return wallet | ❌ No |

---

## Currency Formatting

### Backend Formatting
```php
// Raw value (for calculations)
$balance = "1500.00";

// Formatted value (for display)
$balanceFormatted = number_format((float)$balance, 2, ',', ' ') . ' €';
// Result: "1 500,00 €"
```

### Frontend Display
```typescript
// Use balance_formatted for display
{user.wallet.balance_formatted}  // "1 500,00 €"

// Use balance for calculations
const total = parseFloat(user.wallet.balance) + 100;
```

---

## Error Messages (French)

### Password Change Errors
- `"Le mot de passe actuel est obligatoire."` - Current password required
- `"Le mot de passe actuel est incorrect."` - Current password incorrect
- `"Le nouveau mot de passe est obligatoire."` - New password required
- `"La confirmation du mot de passe ne correspond pas."` - Password confirmation mismatch
- `"Le mot de passe doit contenir au moins 8 caractères."` - Password too short
- `"Le mot de passe doit contenir au moins une majuscule, une minuscule et un chiffre."` - Password requirements

### Success Messages
- `"Mot de passe mis à jour avec succès."` - Password updated successfully

---

## Testing

### Test Registration (Wallet Created)
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'

# Response includes wallet with balance "0.00" and "0,00 €"
```

### Test Get Wallet
```bash
curl http://localhost:8000/api/wallet \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Response includes balance_formatted: "1 500,00 €"
```

### Test Change Password
```bash
curl -X PUT http://localhost:8000/api/wallet/password \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "current_password": "OldPassword123",
    "password": "NewPassword123",
    "password_confirmation": "NewPassword123"
  }'

# Response: {"success": true, "message": "Mot de passe mis à jour avec succès."}
```

### Test Refund (Wallet Credit)
1. Create a booking
2. Process refund via admin: `POST /admin/bookings/{booking}/refund`
3. Check wallet balance (should increase)

---

## Complete Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    USER REGISTRATION                         │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │  Create User  │
                    └───────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Create Wallet │
                    │  (balance: 0) │
                    └───────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Return Token  │
                    │ + User + Wallet│
                    └───────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    BOOKING REFUND                            │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Admin Refunds │
                    │   Booking     │
                    └───────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Status →      │
                    │ 'refunded'    │
                    └───────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Auto-Credit   │
                    │   Wallet      │
                    └───────────────┘

┌─────────────────────────────────────────────────────────────┐
│                    USER DASHBOARD                            │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ GET /api/user │
                    └───────────────┘
                            │
                            ▼
                    ┌───────────────┐
                    │ Display:      │
                    │ - Balance     │
                    │ - Password    │
                    │   Change Form │
                    └───────────────┘
```

---

## Files Modified

### Controllers
- ✅ `app/Http/Controllers/Api/WalletController.php` - Added `updatePassword()` method
- ✅ `app/Http/Controllers/Api/AuthController.php` - Added currency formatting to all responses

### Routes
- ✅ `routes/api.php` - Added `PUT /api/wallet/password` route

### Models
- ✅ `app/Models/Booking.php` - Wallet credit on refund (already implemented)

---

## Summary Checklist

- [x] Registration creates wallet automatically (0.00 €)
- [x] Refund credits wallet instantly
- [x] Dashboard shows balance (formatted: "1 500,00 €")
- [x] Password change functionality
- [x] Sanctum token authentication
- [x] Role-based admin access
- [x] French UX labels and messages
- [x] € currency formatting (French format)
- [x] Full stack sync (backend auto-creates, frontend displays)

---

**Last Updated:** January 2026  
**Status:** ✅ Production Ready - All Expected Outcomes Implemented

