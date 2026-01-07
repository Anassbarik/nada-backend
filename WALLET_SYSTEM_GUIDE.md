# Wallet System Implementation Guide

**Status:** ✅ Complete  
**Last Updated:** January 2026

---

## Overview

The wallet system is fully integrated with the existing authentication and booking flow. Every user automatically gets a wallet upon registration, and refunds are automatically credited to their wallet.

---

## Database Schema

### Wallets Table
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key → users.id, unique)
- balance (decimal 10,2, default 0.00)
- created_at (timestamp)
- updated_at (timestamp)
```

**Constraints:**
- One wallet per user (unique constraint on `user_id`)
- Cascade delete (if user is deleted, wallet is deleted)

---

## Models & Relationships

### User Model
```php
public function wallet()
{
    return $this->hasOne(Wallet::class);
}
```

### Wallet Model
```php
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

### Booking Model
- Automatically credits wallet when status changes to `'refunded'`
- Uses `refund_amount` if set, otherwise uses `booking->price` or `package->prix_ttc`

---

## API Endpoints

### 1. Get User's Wallet

**Endpoint:** `GET /api/wallet`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "wallet": {
      "id": 1,
      "balance": "1500.00",
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

---

## Authentication Flow Integration

### Registration

When a user registers, a wallet is automatically created:

**Request:**
```json
POST /api/register
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "Password123",
  "password_confirmation": "Password123"
}
```

**Response:**
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallet": {
      "id": 1,
      "balance": "0.00"
    }
  }
}
```

### Login

Wallet is included in login response:

**Response:**
```json
{
  "token": "2|xyz789...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallet": {
      "id": 1,
      "balance": "1500.00"
    }
  }
}
```

### Get User Info

**Endpoint:** `GET /api/user`

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallet": {
      "id": 1,
      "balance": "1500.00"
    }
  }
}
```

---

## Refund Flow

### Automatic Wallet Credit

When a booking is refunded (status changes to `'refunded'`), the refund amount is automatically credited to the user's wallet.

**Process:**
1. Admin processes refund via `POST /admin/bookings/{booking}/refund`
2. Booking status is updated to `'refunded'`
3. `refund_amount` is set
4. Booking model's `updating` event fires
5. Wallet is automatically credited with refund amount
6. Room count is incremented (existing logic)

**Refund Amount Priority:**
1. `refund_amount` (if set in update)
2. `booking->price` (if available)
3. `package->prix_ttc` (fallback)

**Example:**
```php
// Admin refunds booking
$booking->update([
    'status' => 'refunded',
    'refund_amount' => 1500.00,
    'refund_notes' => 'Customer requested refund',
    'refunded_at' => now(),
]);

// Wallet is automatically credited:
// $user->wallet->balance += 1500.00
```

---

## Frontend Integration

### Get Wallet Balance

```typescript
// Get wallet info
const response = await axios.get('/api/wallet', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});

const { wallet } = response.data.data;
console.log(`Balance: ${wallet.balance}`);
```

### Display Wallet in Dashboard

```tsx
function WalletDisplay() {
  const [wallet, setWallet] = useState(null);
  const token = localStorage.getItem('token');

  useEffect(() => {
    axios.get('/api/wallet', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    })
    .then(res => setWallet(res.data.data.wallet))
    .catch(err => console.error(err));
  }, [token]);

  return (
    <div className="wallet-card">
      <h3>Mon Portefeuille</h3>
      <p className="balance">
        {wallet ? `${parseFloat(wallet.balance).toFixed(2)} MAD` : 'Chargement...'}
      </p>
    </div>
  );
}
```

### User Registration Response

```typescript
// After registration
const { token, user } = registerResponse.data;

// Wallet is automatically included
console.log(user.wallet.balance); // "0.00"

// Store wallet info
localStorage.setItem('wallet_balance', user.wallet.balance);
```

---

## Database Migrations

### 1. Create Wallets Table
```bash
php artisan migrate
```

Creates:
- `wallets` table with `user_id`, `balance`, timestamps
- Unique constraint on `user_id`
- Foreign key to `users` table

### 2. Create Wallets for Existing Users
```bash
php artisan migrate
```

Automatically creates wallets for all existing users who don't have one (with balance 0.00).

---

## Security Features

### Wallet Protection
- ✅ Wallet can only be accessed by authenticated user
- ✅ Wallet balance can only be modified by system (refunds)
- ✅ One wallet per user (enforced by unique constraint)
- ✅ Wallet is deleted when user is deleted (cascade)

### Refund Security
- ✅ Refunds can only be processed by admins
- ✅ Refund amount is validated (min: 0, max: booking total)
- ✅ Wallet credit happens automatically (no manual intervention)
- ✅ Transaction-safe (uses database transactions)

---

## Testing

### Test Wallet Creation on Registration

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "Password123",
    "password_confirmation": "Password123"
  }'

# Response should include wallet with balance 0.00
```

### Test Wallet Retrieval

```bash
curl http://localhost:8000/api/wallet \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"

# Response should include wallet balance
```

### Test Refund Wallet Credit

1. Create a booking
2. Process refund via admin panel
3. Check wallet balance (should be increased by refund amount)

---

## API Response Examples

### Get Wallet
```json
{
  "success": true,
  "data": {
    "wallet": {
      "id": 1,
      "balance": "1500.00",
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

### Register (with Wallet)
```json
{
  "token": "1|abc123...",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "wallet": {
      "id": 1,
      "balance": "0.00"
    }
  }
}
```

---

## Error Handling

### Wallet Not Found
If a user doesn't have a wallet (legacy users), the system automatically creates one with balance 0.00.

### Insufficient Balance
Currently, wallet balance is only increased (credits). Future implementations can add debit functionality for booking payments.

---

## Future Enhancements

### Potential Features
- [ ] Wallet transactions history
- [ ] Wallet-to-wallet transfers
- [ ] Wallet withdrawal requests
- [ ] Wallet payment for bookings (use wallet balance)
- [ ] Wallet top-up functionality
- [ ] Wallet expiration/expiry dates
- [ ] Wallet notifications

---

## Files Modified/Created

### Models
- ✅ `app/Models/Wallet.php` - Wallet model with relationships
- ✅ `app/Models/User.php` - Added `wallet()` relationship
- ✅ `app/Models/Booking.php` - Added wallet credit logic on refund

### Controllers
- ✅ `app/Http/Controllers/Api/WalletController.php` - Wallet API controller
- ✅ `app/Http/Controllers/Api/AuthController.php` - Updated to create wallet on registration
- ✅ `app/Http/Controllers/Admin/BookingController.php` - Refund method (wallet credit happens automatically)

### Migrations
- ✅ `database/migrations/2026_01_07_122046_create_wallets_table.php` - Create wallets table
- ✅ `database/migrations/2026_01_07_122226_create_wallets_for_existing_users.php` - Create wallets for existing users

### Routes
- ✅ `routes/api.php` - Added `GET /api/wallet` route

---

## Quick Reference

### Wallet Balance Format
- Stored as: `decimal(10,2)` in database
- Returned as: string in JSON (e.g., `"1500.00"`)
- Currency: MAD (Moroccan Dirham)

### Wallet Operations
- **Credit:** Automatic on booking refund
- **Debit:** Not implemented (future feature)
- **Transfer:** Not implemented (future feature)

### Wallet Access
- **API:** `GET /api/wallet` (requires authentication)
- **Included in:** Registration, Login, Get User responses

---

**Last Updated:** January 2026  
**Status:** ✅ Production Ready

