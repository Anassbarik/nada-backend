# Flight Booking Linking Guide

## Overview

This guide explains how to link hotel package bookings to existing flight bookings using the booking reference system. This allows clients who received flight booking details via email to later add hotel packages to their booking.

## Flow Diagram

```
1. Admin creates flight booking
   └─> Creates Booking with flight_id, no user_id (if beneficiary is not client)
   └─> Fills guest_name, guest_email with flight client info
   └─> Generates unique booking_reference
   └─> Admin sends email to client with booking_reference

2. Client receives email with booking_reference
   └─> Client visits frontend app
   └─> Client browses packages
   └─> Client selects package and fills booking form

3. Client submits booking form
   └─> If booking_reference provided:
       ├─> System finds existing flight booking
       ├─> Verifies it's flight-only (not already linked)
       ├─> Creates/updates user account
       └─> Updates existing booking with hotel/package details
   └─> If no booking_reference:
       └─> Creates new booking as normal
```

## API Endpoints

### 1. Find Booking by Reference (Public)

**Endpoint:** `GET /api/bookings/reference/{reference}`

**Description:** Verify a booking reference and check if it can be linked to a hotel package.

**Authentication:** Not required (public endpoint)

**Response:**
```json
{
  "success": true,
  "data": {
    "booking_reference": "BOOK-20260122-ABC",
    "accommodation": {
      "id": 1,
      "name": "Seafood4Africa",
      "slug": "seafood4africa"
    },
    "flight": {
      "id": 5,
      "full_name": "John Doe",
      "departure_date": "2026-02-05",
      "departure_flight_number": "AT2222"
    },
    "guest_name": "John Doe",
    "guest_email": "john@example.com",
    "can_link": true,
    "already_linked": false
  }
}
```

**Response Fields:**
- `booking_reference`: The booking reference code
- `accommodation`: Event/accommodation details
- `flight`: Flight details (if exists)
- `guest_name`: Guest name from booking
- `guest_email`: Guest email from booking
- `can_link`: `true` if booking can be linked (flight-only, not already linked)
- `already_linked`: `true` if booking already has hotel/package

**Error Responses:**

**404 - Booking Not Found:**
```json
{
  "success": false,
  "message": "Booking reference not found."
}
```

### 2. Create Booking with Reference Linking

**Endpoint:** `POST /api/bookings` or `POST /api/events/{slug}/hotels/{hotel:slug}/bookings`

**Description:** Create a new booking or link hotel package to existing flight booking.

**Authentication:** Required (user must be authenticated)

**Request Body:**
```json
{
  "package_id": 123,
  "booking_reference": "BOOK-20260122-ABC",  // Optional: for linking to flight booking
  "full_name": "John Doe",
  "email": "john@example.com",
  "phone": "+212612345678",
  "payment_method": "bank",
  "booker_is_resident": true,
  "resident_name_1": "John Doe",
  "checkin_date": "2026-02-05",
  "checkout_date": "2026-02-10",
  // ... other booking fields
}
```

**Request Fields:**
- `booking_reference` (optional): Reference code from flight booking email
- `package_id` (required): Hotel package ID
- All other standard booking fields

**Response:**
```json
{
  "success": true,
  "message": "Booking created successfully.",
  "data": {
    "id": 456,
    "booking_reference": "BOOK-20260122-ABC",
    "flight_id": 5,
    "hotel_id": 10,
    "package_id": 123,
    "status": "pending",
    // ... other booking fields
  }
}
```

**Error Responses:**

**422 - Invalid Reference:**
```json
{
  "success": false,
  "message": "Booking reference not found or invalid. Please check your reference number.",
  "errors": {
    "booking_reference": ["Invalid booking reference. It must be a flight-only booking that hasn't been linked to a hotel yet."]
  }
}
```

**422 - Reference Already Linked:**
```json
{
  "success": false,
  "message": "This booking reference has already been linked to a hotel package.",
  "errors": {
    "booking_reference": ["This booking is already complete."]
  }
}
```

**422 - Wrong Event:**
```json
{
  "success": false,
  "message": "Booking reference does not match this event.",
  "errors": {
    "booking_reference": ["The booking reference belongs to a different event."]
  }
}
```

## Frontend Implementation

### Step 1: Booking Form with Reference Field

Add an optional "Booking Reference" field to your booking form:

```jsx
// BookingForm.jsx
const [bookingReference, setBookingReference] = useState('');
const [referenceInfo, setReferenceInfo] = useState(null);
const [checkingReference, setCheckingReference] = useState(false);

// Function to verify booking reference
const checkBookingReference = async (reference) => {
  if (!reference || reference.length < 5) {
    setReferenceInfo(null);
    return;
  }

  setCheckingReference(true);
  try {
    const response = await axios.get(`/api/bookings/reference/${reference}`);
    setReferenceInfo(response.data.data);
    
    // Pre-fill form with guest info if available
    if (response.data.data.guest_name) {
      setFormData(prev => ({
        ...prev,
        full_name: prev.full_name || response.data.data.guest_name,
        email: prev.email || response.data.data.guest_email,
      }));
    }
  } catch (error) {
    if (error.response?.status === 404) {
      setReferenceInfo({ error: 'Booking reference not found' });
    } else {
      setReferenceInfo({ error: 'Error checking reference' });
    }
  } finally {
    setCheckingReference(false);
  }
};

// Debounced check
useEffect(() => {
  const timer = setTimeout(() => {
    if (bookingReference) {
      checkBookingReference(bookingReference);
    }
  }, 500);
  return () => clearTimeout(timer);
}, [bookingReference]);

return (
  <form onSubmit={handleSubmit}>
    {/* Booking Reference Field */}
    <div className="form-group">
      <label htmlFor="booking_reference">
        Booking Reference (Optional)
        <span className="text-sm text-gray-500 ml-2">
          If you have a flight booking reference, enter it here to link your hotel booking
        </span>
      </label>
      <input
        type="text"
        id="booking_reference"
        value={bookingReference}
        onChange={(e) => setBookingReference(e.target.value.toUpperCase())}
        placeholder="BOOK-20260122-ABC"
        className="form-control"
      />
      
      {/* Reference Status */}
      {checkingReference && (
        <div className="text-sm text-blue-600">Checking reference...</div>
      )}
      
      {referenceInfo && !referenceInfo.error && (
        <div className="text-sm text-green-600">
          ✓ Reference found: {referenceInfo.guest_name}
          {referenceInfo.flight && (
            <div className="text-xs mt-1">
              Flight: {referenceInfo.flight.departure_flight_number} on {referenceInfo.flight.departure_date}
            </div>
          )}
        </div>
      )}
      
      {referenceInfo?.error && (
        <div className="text-sm text-red-600">
          ✗ {referenceInfo.error}
        </div>
      )}
      
      {referenceInfo?.already_linked && (
        <div className="text-sm text-yellow-600">
          ⚠ This booking is already complete
        </div>
      )}
    </div>

    {/* Rest of booking form */}
    {/* ... */}
  </form>
);
```

### Step 2: Submit Booking with Reference

Include `booking_reference` in the booking submission:

```javascript
const handleSubmit = async (formData) => {
  try {
    const bookingData = {
      package_id: selectedPackage.id,
      booking_reference: bookingReference || undefined, // Include if provided
      full_name: formData.full_name,
      email: formData.email,
      phone: formData.phone,
      payment_method: formData.payment_method,
      // ... other fields
    };

    const response = await axios.post('/api/bookings', bookingData);
    
    // Success - booking created or linked
    if (bookingReference) {
      toast.success('Hotel package linked to your flight booking!');
    } else {
      toast.success('Booking created successfully!');
    }
    
    router.push(`/bookings/${response.data.data.id}`);
  } catch (error) {
    if (error.response?.status === 422) {
      // Handle validation errors
      const errors = error.response.data.errors;
      if (errors.booking_reference) {
        toast.error(errors.booking_reference[0]);
      }
    } else {
      toast.error('Error creating booking');
    }
  }
};
```

### Step 3: User Registration Flow

If the user doesn't have an account yet:

```javascript
// If user is not authenticated
if (!isAuthenticated) {
  // Show registration form
  // After registration, proceed with booking
  const registerResponse = await axios.post('/api/register', {
    name: formData.full_name,
    email: formData.email,
    password: formData.password,
    password_confirmation: formData.password_confirmation,
  });
  
  // Store token
  localStorage.setItem('token', registerResponse.data.token);
  axios.defaults.headers.common['Authorization'] = `Bearer ${registerResponse.data.token}`;
  
  // Now create booking (with reference if provided)
  await axios.post('/api/bookings', {
    ...bookingData,
    booking_reference: bookingReference,
  });
}
```

## Backend Behavior

### When `booking_reference` is provided:

1. **Validation:**
   - Booking must exist with the given reference
   - Booking must have `flight_id` (flight booking)
   - Booking must NOT have `hotel_id` or `package_id` (not already linked)
   - Booking must belong to the same accommodation as the selected package

2. **Update Existing Booking:**
   - Links `user_id` to the authenticated user
   - Adds `hotel_id` and `package_id`
   - Updates guest information (name, email, phone) if provided
   - Adds hotel package price to existing flight price
   - Updates payment amounts (wallet + bank)
   - Keeps the same `booking_reference`

3. **Result:**
   - One booking record with both flight and hotel information
   - Same booking reference for both flight and hotel

### When `booking_reference` is NOT provided:

- Creates a new booking as normal
- Generates a new booking reference
- No linking to existing flight booking

## Important Notes

1. **User Account Creation:**
   - If admin didn't create a user account (beneficiary is organizer or no email), `user_id` is `null`
   - When client links hotel booking, their new user account is linked to the booking
   - Guest fields (`guest_name`, `guest_email`) are preserved from flight booking

2. **Price Calculation:**
   - When linking, hotel package price is **added** to existing flight price
   - Total price = flight price + hotel package price
   - Payment amounts are also added together

3. **Status:**
   - Booking status follows normal payment logic
   - If wallet payment covers full amount, status becomes `confirmed`
   - Otherwise, status remains `pending`

4. **Security:**
   - Booking reference lookup is public (no auth required)
   - But booking creation/update requires authentication
   - Reference must match exactly (case-sensitive)
   - Reference must be for flight-only booking

## Testing Checklist

- [ ] Verify booking reference lookup works
- [ ] Test linking hotel to existing flight booking
- [ ] Test error when reference doesn't exist
- [ ] Test error when reference already linked
- [ ] Test error when reference belongs to different event
- [ ] Test creating booking without reference (normal flow)
- [ ] Test user registration + booking linking in one flow
- [ ] Verify prices are added correctly
- [ ] Verify guest info is preserved/updated correctly

## Example Email Template

When admin sends flight booking details to client:

```
Subject: Your Flight Booking Confirmation - BOOK-20260122-ABC

Dear [Client Name],

Your flight booking has been confirmed!

Booking Reference: BOOK-20260122-ABC
Flight: AT2222
Departure: 2026-02-05 at 14:30

To add a hotel package to your booking:
1. Visit [website URL]
2. Browse available packages
3. Select your preferred package
4. Enter your booking reference: BOOK-20260122-ABC
5. Complete the booking form

Your booking reference will automatically link your hotel booking to your flight booking.

Best regards,
[Admin Name]
```

