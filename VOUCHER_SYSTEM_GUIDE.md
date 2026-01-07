# Voucher System Implementation Guide

**Status:** ✅ Complete  
**Last Updated:** January 2026

---

## Overview

The voucher system ("Bon de Confirmation") is fully implemented alongside the existing invoice system. Vouchers are generated automatically on booking creation but are only visible to users and emailed when the booking status is set to `'paid'`.

---

## Key Differences: Invoices vs Vouchers

| Feature | Invoices | Vouchers |
|---------|----------|----------|
| **Audience** | Admin only | Users only |
| **Visibility** | Always visible in admin dashboard | Hidden until status='paid' |
| **Editable** | Yes (admin can edit) | No (auto-generated) |
| **Email** | Never sent to users | Sent automatically when status='paid' |
| **Purpose** | Internal billing/accounting | User confirmation document |

---

## Database Schema

### Bookings Table
```sql
status ENUM('pending', 'confirmed', 'paid', 'cancelled', 'refunded')
```

### Vouchers Table
```sql
- id (bigint, primary key)
- booking_id (bigint, foreign key → bookings.id, cascade delete)
- user_id (bigint, foreign key → users.id, cascade delete)
- voucher_number (varchar, unique) - Format: VOC-YYYYMMDDHHMMSS-XXXX
- pdf_path (varchar, nullable) - Path to PDF file
- emailed (boolean, default false) - Whether voucher was emailed
- created_at, updated_at (timestamps)
```

---

## Models & Relationships

### Booking Model
```php
public function voucher(): HasOne
{
    return $this->hasOne(Voucher::class);
}
```

### Voucher Model
```php
public function booking(): BelongsTo
{
    return $this->belongsTo(Booking::class);
}

public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

---

## Workflow

### 1. Booking Creation

**When:** `POST /api/bookings`

**Process:**
1. Booking created with status `'pending'`
2. **Invoice created** (admin-only, editable)
3. **Voucher created** (user-only, hidden until paid)
4. Both PDFs generated immediately

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully.",
  "data": {
    "booking": { ... },
    "invoice": {
      "id": 1,
      "invoice_number": "FAC-20260107123456-ABCD",
      "status": "draft"
    },
    "voucher": {
      "id": 1,
      "voucher_number": "VOC-20260107123456-EFGH",
      "emailed": false
    }
  }
}
```

---

### 2. Status Update to 'paid'

**When:** Admin updates booking status to `'paid'` via `PATCH /admin/bookings/{booking}/status`

**Process:**
1. Booking status → `'paid'`
2. **Voucher email sent automatically** to user
3. Voucher `emailed` flag set to `true`
4. Voucher becomes visible in user dashboard

**Admin Action:**
```php
// Admin BookingController@updateStatus
$booking->update(['status' => 'paid']);
// → Automatically emails voucher to user
```

---

### 3. User Dashboard

**When:** User accesses `GET /api/vouchers`

**Returns:** Only vouchers for bookings with status `'paid'`

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "voucher_number": "VOC-20260107123456-EFGH",
      "emailed": true,
      "booking": {
        "id": 1,
        "booking_reference": "BOOK-20260107-ABC",
        "status": "paid",
        "checkin_date": "2026-02-01",
        "checkout_date": "2026-02-05",
        "price": 1500.00,
        "event": { ... },
        "hotel": { ... }
      },
      "pdf_url": "/storage/vouchers/1.pdf",
      "created_at": "2026-01-07 12:34:56"
    }
  ]
}
```

---

## API Endpoints

### 1. Get User's Vouchers

**Endpoint:** `GET /api/vouchers`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/json
```

**Returns:** Only vouchers for paid bookings

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "voucher_number": "VOC-20260107123456-EFGH",
      "emailed": true,
      "booking": { ... },
      "pdf_url": "/storage/vouchers/1.pdf"
    }
  ]
}
```

---

### 2. Download Voucher PDF

**Endpoint:** `GET /api/vouchers/{voucher}`

**Headers:**
```
Authorization: Bearer {token}
Accept: application/pdf
```

**Returns:** PDF file

**Security:**
- ✅ Verifies user owns the voucher
- ✅ Verifies booking status is `'paid'`
- ✅ Returns 403 if unauthorized
- ✅ Returns 404 if PDF not found

**Response:** PDF file download

---

## Admin Workflow

### Update Booking Status

**Endpoint:** `PATCH /admin/bookings/{booking}/status`

**Request:**
```json
{
  "status": "paid"
}
```

**Process:**
1. Updates booking status
2. If status = `'paid'` and voucher exists:
   - Loads booking with user, voucher, event, hotel, package
   - Sends `VoucherMail` to user's email
   - Updates voucher `emailed` flag to `true`
   - Logs errors if email fails (doesn't block status update)

**Response:**
```
Redirect with success message: "Statut mis à jour avec succès."
```

---

## Email Template

### VoucherMail

**Subject:** `"Votre Bon de Confirmation - {booking_reference}"`

**Content:**
- French language
- Booking details
- Voucher number
- PDF attachment (voucher PDF)

**Template:** `resources/views/emails/voucher.blade.php`

**Attachment:** Voucher PDF file

---

## PDF Template

### Voucher PDF

**Template:** `resources/views/vouchers/template.blade.php`

**Content:**
- "Bon de Confirmation" title (green color)
- Voucher number
- Booking reference
- Client information
- Booking details (event, hotel, package, dates, etc.)
- Total amount (€ currency)
- "PAYÉ" status badge
- Confirmation message

**Storage:** `storage/app/public/vouchers/{voucher_id}.pdf`

---

## Frontend Integration

### Display User's Vouchers

```typescript
// Get user's vouchers (only paid bookings)
const response = await axios.get('/api/vouchers', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});

const vouchers = response.data.data;
// Only shows vouchers for bookings with status='paid'
```

### Download Voucher PDF

```typescript
// Download voucher PDF
const voucherId = 1;
window.open(`/api/vouchers/${voucherId}`, '_blank');
// Or use axios to download
const response = await axios.get(`/api/vouchers/${voucherId}`, {
  headers: {
    'Authorization': `Bearer ${token}`,
  },
  responseType: 'blob',
});

// Create download link
const url = window.URL.createObjectURL(new Blob([response.data]));
const link = document.createElement('a');
link.href = url;
link.setAttribute('download', 'voucher.pdf');
document.body.appendChild(link);
link.click();
```

### Display in Dashboard

```tsx
function VouchersList() {
  const [vouchers, setVouchers] = useState([]);
  const token = localStorage.getItem('token');

  useEffect(() => {
    axios.get('/api/vouchers', {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
      },
    })
    .then(res => setVouchers(res.data.data))
    .catch(err => console.error(err));
  }, [token]);

  return (
    <div>
      <h2>Mes Bons de Confirmation</h2>
      {vouchers.map(voucher => (
        <div key={voucher.id} className="voucher-card">
          <h3>Bon N°: {voucher.voucher_number}</h3>
          <p>Réservation: {voucher.booking.booking_reference}</p>
          <p>Statut: {voucher.booking.status}</p>
          {voucher.emailed && (
            <p className="emailed">✓ Email envoyé</p>
          )}
          <a 
            href={voucher.pdf_url} 
            target="_blank"
            rel="noopener noreferrer"
          >
            Télécharger le PDF
          </a>
        </div>
      ))}
    </div>
  );
}
```

---

## Security Features

### Voucher Access Control

- ✅ Only authenticated users can access vouchers
- ✅ Users can only see their own vouchers
- ✅ Vouchers only visible for paid bookings
- ✅ PDF download requires ownership verification
- ✅ Status verification before PDF access

### Admin Protection

- ✅ Admin routes protected with `role:admin` middleware
- ✅ Status update requires authentication
- ✅ Email sending is non-blocking (errors logged, don't fail status update)

---

## Status Flow

```
pending → confirmed → paid → (voucher emailed) → user sees voucher
   ↓
cancelled (voucher hidden)
   ↓
refunded (voucher hidden, wallet credited)
```

**Voucher Visibility:**
- ✅ Visible: `status = 'paid'`
- ❌ Hidden: `status = 'pending'`, `'confirmed'`, `'cancelled'`, `'refunded'`

---

## Files Created/Modified

### Models
- ✅ `app/Models/Voucher.php` - Voucher model with relationships

### Controllers
- ✅ `app/Http/Controllers/Api/VoucherController.php` - Voucher API controller
- ✅ `app/Http/Controllers/Api/BookingController.php` - Updated to create voucher on booking
- ✅ `app/Http/Controllers/Admin/BookingController.php` - Updated to email voucher on status='paid'

### Mail
- ✅ `app/Mail/VoucherMail.php` - Voucher email mailable

### Views
- ✅ `resources/views/vouchers/template.blade.php` - Voucher PDF template
- ✅ `resources/views/emails/voucher.blade.php` - Voucher email template

### Migrations
- ✅ `database/migrations/2026_01_07_131225_create_vouchers_table.php` - Create vouchers table
- ✅ `database/migrations/2026_01_07_131237_add_paid_status_to_bookings_table.php` - Add 'paid' status

### Routes
- ✅ `routes/api.php` - Added voucher routes

---

## Testing

### Test Voucher Creation

```bash
# Create booking
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "package_id": 1,
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890"
  }'

# Response should include voucher (but not visible to user until paid)
```

### Test Voucher Visibility

```bash
# Get vouchers (should be empty if no paid bookings)
curl http://localhost:8000/api/vouchers \
  -H "Authorization: Bearer {token}"

# After admin sets status to 'paid', voucher should appear
```

### Test Voucher Download

```bash
# Download voucher PDF
curl http://localhost:8000/api/vouchers/1 \
  -H "Authorization: Bearer {token}" \
  --output voucher.pdf
```

---

## Summary Checklist

- [x] Vouchers table created
- [x] 'paid' status added to bookings enum
- [x] Voucher model with relationships
- [x] Voucher created automatically on booking
- [x] Voucher PDF generated on creation
- [x] Voucher email sent when status='paid'
- [x] Voucher API endpoints (index, show)
- [x] Voucher only visible for paid bookings
- [x] Security: User ownership verification
- [x] French UX: Templates and emails in French
- [x] Clear separation: Invoices (admin) vs Vouchers (user)

---

**Last Updated:** January 2026  
**Status:** ✅ Production Ready

