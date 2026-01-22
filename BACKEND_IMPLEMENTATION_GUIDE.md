# Backend Implementation Guide: Payment Document & Flight Ticket Upload

## Overview
This document outlines the backend implementation requirements for the payment document and flight ticket upload features. The admin dashboard is implemented on the backend server, and users can upload both documents from their user dashboard.

## Features Implemented (Frontend)

### 1. Payment Document Upload
- Users can upload "Ordre de paiement" documents from their dashboard (`/dashboard`)
- Upload is available for each unpaid booking in the user's bookings list
- File types: PDF or images (JPG, PNG, WebP) - max 10MB
- Document is associated with a booking ID
- Users can see if a document has already been uploaded

### 2. Flight Ticket Upload
- Users can upload flight tickets from their dashboard (`/dashboard`)
- Upload is available for all bookings (paid and unpaid)
- File types: PDF or images (JPG, PNG, WebP) - max 10MB
- Document is associated with a booking ID
- Admins can compare the flight number in the uploaded ticket with the flight number submitted in the booking form
- Users can see if a ticket has already been uploaded

### 3. User Dashboard
- Users can see all their bookings
- For unpaid bookings, users can upload payment documents
- For all bookings, users can upload flight tickets
- Vouchers are only shown for paid bookings (backend should filter this)
- Bookings show their payment status

## Backend API Endpoints Required

### 1. Upload Payment Document
**Endpoint:** `POST /api/bookings/{bookingId}/payment-document`

**Authentication:** Required (Bearer token)

**Request:**
- Content-Type: `multipart/form-data`
- Field name: `payment_document`
- File types: PDF or images (JPG, PNG, WebP)
- Max size: 10MB

**Response:**
```json
{
  "success": true,
  "message": "Payment document uploaded successfully",
  "data": {
    "booking": {
      "id": 1,
      "payment_document_path": "storage/payment-documents/booking-1-ordre-paiement.pdf",
      "payment_document_url": "https://example.com/storage/payment-documents/booking-1-ordre-paiement.pdf",
      ...
    }
  }
}
```

### 2. Upload Flight Ticket
**Endpoint:** `POST /api/bookings/{bookingId}/flight-ticket`

**Authentication:** Required (Bearer token)

**Request:**
- Content-Type: `multipart/form-data`
- Field name: `flight_ticket`
- File types: PDF or images (JPG, PNG, WebP)
- Max size: 10MB

**Response:**
```json
{
  "success": true,
  "message": "Flight ticket uploaded successfully",
  "data": {
    "booking": {
      "id": 1,
      "flight_ticket_path": "storage/flight-tickets/booking-1-flight-ticket.pdf",
      "flight_ticket_url": "https://example.com/storage/flight-tickets/booking-1-flight-ticket.pdf",
      ...
    }
  }
}
```

**Database Changes:**
- Add `payment_document_path` column to `bookings` table (nullable string)
- Add `flight_ticket_path` column to `bookings` table (nullable string)
- Store the file paths or URLs in these columns

**User Bookings Endpoint Update:**
The existing `GET /api/bookings` endpoint should also return `payment_document_path`, `payment_document_url`, `flight_ticket_path`, and `flight_ticket_url` fields for each booking:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "booking_reference": "REF-12345",
      "status": "pending",
      "flight_number": "AF1234",
      "payment_document_path": "storage/payment-documents/booking-1-ordre-paiement.pdf",
      "payment_document_url": "https://example.com/storage/payment-documents/booking-1-ordre-paiement.pdf",
      "flight_ticket_path": "storage/flight-tickets/booking-1-flight-ticket.pdf",
      "flight_ticket_url": "https://example.com/storage/flight-tickets/booking-1-flight-ticket.pdf",
      ...
    }
  ]
}
```

**File Storage:**
- Store payment documents in `storage/app/payment-documents/` or similar
- Store flight tickets in `storage/app/flight-tickets/` or similar
- Use Laravel's Storage facade
- Generate unique filenames:
  - Payment documents: `booking-{id}-ordre-paiement-{timestamp}.{ext}`
  - Flight tickets: `booking-{id}-flight-ticket-{timestamp}.{ext}`

### 3. Mark Booking as Paid
**Endpoint:** `PUT /api/admin/bookings/{bookingId}/mark-paid`

**Authentication:** Required (Admin only - Bearer token with admin role)

**Response:**
```json
{
  "success": true,
  "message": "Booking marked as paid successfully. Voucher generated and sent via email.",
  "data": {
    "booking": {
      "id": 1,
      "status": "paid",
      ...
    },
    "voucher": {
      "id": 1,
      "voucher_number": "VOUCHER-12345",
      "booking_id": 1,
      ...
    }
  }
}
```

**Business Logic:**
1. Update booking status to "paid"
2. Generate a voucher for the booking (if not already generated)
3. Send voucher PDF via email to the booking's email address
4. Return success response with booking and voucher data

**Email Requirements:**
- Send voucher PDF as attachment
- Include booking details in email body
- Use Laravel's Mail facade with Mailable class

### 4. Download Payment Document (Admin)
**Endpoint:** `GET /api/admin/bookings/{bookingId}/payment-document`

**Authentication:** Required (Admin only - Bearer token with admin role)

**Response:**
- Content-Type: `application/pdf` or `image/jpeg`, etc. (based on file type)
- File download (binary)

**Error Handling:**
- 404 if booking not found
- 404 if document doesn't exist
- 403 if user is not admin

### 5. Download Flight Ticket (Admin)
**Endpoint:** `GET /api/admin/bookings/{bookingId}/flight-ticket`

**Authentication:** Required (Admin only - Bearer token with admin role)

**Response:**
- Content-Type: `application/pdf` or `image/jpeg`, etc. (based on file type)
- File download (binary)

**Error Handling:**
- 404 if booking not found
- 404 if ticket doesn't exist
- 403 if user is not admin

**Admin Dashboard Features:**
- Admin should be able to view both documents for each booking
- Admin can compare the flight number from the booking form (`flight_number` field) with the flight number visible in the uploaded flight ticket
- Admin can download both documents
- Admin can mark bookings as paid after verifying both documents
**Endpoint:** `GET /api/admin/bookings/{bookingId}/payment-document`

**Authentication:** Required (Admin only - Bearer token with admin role)

**Response:**
- Content-Type: `application/pdf` or `image/jpeg`, etc. (based on file type)
- File download (binary)

**Error Handling:**
- 404 if booking not found
- 404 if document doesn't exist
- 403 if user is not admin

## Database Schema Changes

### Bookings Table
Add the following columns:
```sql
ALTER TABLE bookings ADD COLUMN payment_document_path VARCHAR(255) NULL;
ALTER TABLE bookings ADD COLUMN flight_ticket_path VARCHAR(255) NULL;
```

Or if using migrations:
```php
Schema::table('bookings', function (Blueprint $table) {
    $table->string('payment_document_path')->nullable()->after('status');
    $table->string('flight_ticket_path')->nullable()->after('payment_document_path');
});
```

## File Storage Configuration

### Storage Setup
1. Create storage directories:
   - `storage/app/payment-documents/`
   - `storage/app/flight-tickets/`
2. Ensure directories are writable: 
   ```bash
   chmod -R 775 storage/app/payment-documents/
   chmod -R 775 storage/app/flight-tickets/
   ```
3. Create symbolic link for public access (if needed):
   ```bash
   php artisan storage:link
   ```

### Storage Path Examples
- Payment documents: `payment-documents/booking-{id}-ordre-paiement-{timestamp}.{ext}`
  - Example: `payment-documents/booking-123-ordre-paiement-1704067200.pdf`
  - Example: `payment-documents/booking-123-ordre-paiement-1704067200.jpg`
- Flight tickets: `flight-tickets/booking-{id}-flight-ticket-{timestamp}.{ext}`
  - Example: `flight-tickets/booking-123-flight-ticket-1704067200.pdf`
  - Example: `flight-tickets/booking-123-flight-ticket-1704067200.png`

## Authentication & Authorization

### Admin Role Check
- Implement middleware to check if user has admin role
- Example middleware: `EnsureUserIsAdmin`
- Apply to all `/api/admin/*` routes

### Admin User Setup
- Ensure admin users have appropriate role/permission
- Can use Laravel's built-in roles/permissions or custom implementation

## Voucher Generation

### When to Generate Vouchers
- Vouchers should only be generated when booking is marked as paid
- Vouchers should be visible to users only for paid bookings
- Existing voucher generation logic should be triggered when marking as paid

### Voucher Email
- Send voucher PDF as email attachment
- Include booking reference, dates, hotel, package details
- Use a professional email template

## API Response Format

All endpoints should follow Laravel's standard API response format:
```json
{
  "success": true,
  "message": "Success message",
  "data": { ... }
}
```

For errors:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

## Security Considerations

1. **File Upload Security:**
   - Validate file types (MIME type checking, not just extension)
   - Validate file size (max 10MB for both document types)
   - Accept only: PDF, JPG, JPEG, PNG, WebP
   - Scan files for malware (optional but recommended)
   - Store files outside public directory, serve via secure routes

2. **Admin Access:**
   - Strictly enforce admin role checks
   - Log all admin actions (marking as paid, downloading documents)
   - Rate limit admin endpoints

3. **File Access:**
   - Payment documents should only be accessible to admins
   - Flight tickets should only be accessible to admins
   - Do not expose direct file URLs in public storage
   - Use signed URLs or serve files through authenticated routes

4. **Flight Number Verification:**
   - Admin dashboard should display the flight number from the booking form
   - Admin can view/download the flight ticket to verify the flight number matches
   - Consider adding a verification status field if needed

## Testing Checklist

### User-Facing Features
- [ ] Upload payment document (valid PDF)
- [ ] Upload payment document (valid image: JPG, PNG, WebP)
- [ ] Upload payment document (invalid file type)
- [ ] Upload payment document (file too large)
- [ ] Upload payment document (unauthenticated - should fail)
- [ ] Upload flight ticket (valid PDF)
- [ ] Upload flight ticket (valid image: JPG, PNG, WebP)
- [ ] Upload flight ticket (invalid file type)
- [ ] Upload flight ticket (file too large)
- [ ] Upload flight ticket (unauthenticated - should fail)
- [ ] User bookings endpoint includes payment_document_path and flight_ticket_path when documents exist
- [ ] Verify vouchers are only shown for paid bookings in user dashboard

### Admin Features (Backend Dashboard)
- [ ] Admin can view all bookings with payment documents and flight tickets
- [ ] Admin can download payment documents
- [ ] Admin can download flight tickets
- [ ] Admin can compare flight number from booking form with flight ticket
- [ ] Admin can mark bookings as paid
- [ ] Verify voucher is generated when marking as paid
- [ ] Verify email is sent when marking as paid

## Frontend Integration Points

The frontend expects:
1. Booking ID in user bookings data (from `/api/bookings`)
2. Payment document upload endpoint at `/api/bookings/{bookingId}/payment-document`
3. Flight ticket upload endpoint at `/api/bookings/{bookingId}/flight-ticket`
4. User bookings endpoint (`/api/bookings`) should include:
   - `payment_document_path` and `payment_document_url` fields if payment document exists
   - `flight_ticket_path` and `flight_ticket_url` fields if flight ticket exists
5. Vouchers endpoint (`/api/vouchers`) should only return vouchers for paid bookings

**Note:** The admin dashboard is implemented on the backend server. The backend should provide:
- Admin interface to view all bookings with payment documents and flight tickets
- Ability to download payment documents
- Ability to download flight tickets
- Ability to compare flight number from booking form with flight ticket
- Ability to mark bookings as paid (which generates vouchers and sends emails)

## Notes

- The frontend already handles file validation, but backend should also validate
- The frontend shows vouchers only for paid bookings (backend should filter)
- Email sending should be queued for better performance
- Consider adding file cleanup job for old/unused payment documents

