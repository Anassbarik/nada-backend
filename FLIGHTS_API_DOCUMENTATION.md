# Flights API Documentation

## Overview
The Flights API allows the frontend to fetch flight information for accommodations/events. Flights are displayed under hotels in the events landing page.

**Important:** Only flights where `beneficiary_type = 'organizer'` are returned in the public API. Client flights are private and not shown on the events landing page.

## API Endpoints

### 1. Get Flights for Event
**Endpoint:** `GET /api/events/{slug}/flights`

**Description:** Returns a list of all flights for a specific accommodation/event.

**Authentication:** Not required (public endpoint)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "accommodation_id": 1,
      "full_name": "John Doe",
      "flight_class": "economy",
      "flight_class_label": "Economy",
      "flight_category": "round_trip",
      "flight_category_label": "Aller-Retour (Round Trip)",
      "departure": {
        "date": "2026-02-05",
        "time": "14:30",
        "flight_number": "AT2222",
        "airport": "CMN",
        "price_ttc": 1500.00
      },
      "arrival": {
        "date": "2026-02-05",
        "time": "16:45",
        "airport": "RAK"
      },
      "return": {
        "date": "2026-02-10",
        "departure_time": "10:00",
        "departure_airport": "RAK",
        "arrival_date": "2026-02-10",
        "arrival_time": "12:15",
        "arrival_airport": "CMN",
        "flight_number": "AT1111",
        "price_ttc": 1200.00
      },
      "total_price": 2700.00,
      "reference": "FLIGHT-20260205-ABCD",
      "eticket_url": "https://example.com/storage/flights/etickets/...",
      "beneficiary_type": "organizer",
      "status": "paid",
      "payment_method": "bank",
      "created_at": "2026-01-22 15:00:00",
      "updated_at": "2026-01-22 15:00:00"
    }
  ]
}
```

**Response Fields:**
- `id`: Flight unique identifier
- `accommodation_id`: ID of the accommodation/event
- `full_name`: Client's full name
- `flight_class`: Flight class (economy, business, first)
- `flight_class_label`: Human-readable flight class label
- `flight_category`: Flight type - "one_way" or "round_trip"
- `flight_category_label`: Human-readable flight category label
- `departure`: Departure information
  - `date`: Departure date (YYYY-MM-DD)
  - `time`: Departure time (HH:MM)
  - `flight_number`: Flight number (e.g., "AT2222")
  - `airport`: Departure airport code (e.g., "CMN", "CDG")
  - `price_ttc`: Departure flight price TTC (MAD)
- `arrival`: Arrival information
  - `date`: Arrival date (YYYY-MM-DD)
  - `time`: Arrival time (HH:MM)
  - `airport`: Arrival airport code (e.g., "RAK", "ORY")
- `return`: Return flight information (null if one-way)
  - `date`: Return departure date
  - `departure_time`: Return departure time
  - `departure_airport`: Return departure airport code
  - `arrival_date`: Return arrival date
  - `arrival_time`: Return arrival time
  - `arrival_airport`: Return arrival airport code
  - `flight_number`: Return flight number
  - `price_ttc`: Return flight price TTC (MAD)
- `total_price`: Total flight price (departure + return if round trip)
- `reference`: Unique flight reference (e.g., "FLIGHT-20260205-ABCD")
- `eticket_url`: URL to eTicket file (if uploaded)
- `beneficiary_type`: Always "organizer" in public API (client flights are private)
- `status`: Payment status - "pending" (not paid) or "paid"
- `payment_method`: Payment method - "wallet", "bank", "both", or null
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### 2. Get Single Flight
**Endpoint:** `GET /api/events/{slug}/flights/{flight}`

**Description:** Returns detailed information about a specific flight.

**Authentication:** Not required (public endpoint)

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "accommodation_id": 1,
    "full_name": "John Doe",
    "flight_class": "economy",
    "flight_class_label": "Economy",
    "flight_category": "round_trip",
    "flight_category_label": "Aller-Retour (Round Trip)",
    "departure": {
      "date": "2026-02-05",
      "time": "14:30",
      "flight_number": "AT2222",
      "airport": "CMN",
      "price_ttc": 1500.00
    },
    "arrival": {
      "date": "2026-02-05",
      "time": "16:45",
      "airport": "RAK"
    },
    "return": {
      "date": "2026-02-10",
      "departure_time": "10:00",
      "departure_airport": "RAK",
      "arrival_date": "2026-02-10",
      "arrival_time": "12:15",
      "arrival_airport": "CMN",
      "flight_number": "AT1111",
      "price_ttc": 1200.00
    },
    "total_price": 2700.00,
    "reference": "FLIGHT-20260205-ABCD",
    "eticket_url": "https://example.com/storage/flights/etickets/...",
    "beneficiary_type": "organizer",
    "status": "paid",
    "payment_method": "bank",
    "accommodation": {
      "id": 1,
      "name": "Seafood4Africa",
      "slug": "seafood4africa"
    },
    "created_at": "2026-01-22 15:00:00",
    "updated_at": "2026-01-22 15:00:00"
  }
}
```

### 3. Get Event with Flights (Included in Event Response)
**Endpoint:** `GET /api/events/{slug}`

**Description:** The event response now includes a `flights` array with all flights for the accommodation.

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Seafood4Africa",
    "slug": "seafood4africa",
    // ... other event fields ...
    "hotels": [
      // ... hotels array ...
    ],
    "flights": [
      {
        "id": 1,
        "accommodation_id": 1,
        "full_name": "John Doe",
        "flight_class": "economy",
        "flight_class_label": "Economy",
        "flight_category": "one_way",
        "flight_category_label": "Aller Simple (One Way)",
        "departure": {
          "date": "2026-02-05",
          "time": "14:30",
          "flight_number": "AT2222",
          "airport": "CMN",
          "price_ttc": 1500.00
        },
        "arrival": {
          "date": "2026-02-05",
          "time": "16:45",
          "airport": "RAK"
        },
        "return": null,
        "total_price": 1500.00,
        "reference": "FLIGHT-20260205-ABCD",
        "eticket_url": null,
        "beneficiary_type": "organizer",
        "status": "paid",
        "payment_method": "bank",
        "created_at": "2026-01-22 15:00:00",
        "updated_at": "2026-01-22 15:00:00"
      }
    ],
    "airports": [
      // ... airports array ...
    ]
  }
}
```

## Error Responses

### Event Not Found
```json
{
  "success": false,
  "message": "Event not found."
}
```
**Status Code:** 404

### Flight Not Found
```json
{
  "success": false,
  "message": "Flight not found for this event."
}
```
**Status Code:** 404

## Booking Reference Linking

Flights created by admins generate bookings with unique `booking_reference` codes. Clients can use these references to link hotel package bookings to their existing flight bookings.

**See `FLIGHT_BOOKING_LINKING_GUIDE.md` for complete implementation details.**

**Quick Reference:**
- `GET /api/bookings/reference/{reference}` - Verify booking reference
- `POST /api/bookings` with `booking_reference` field - Link hotel to flight booking

## Notes

1. **Only organizer flights are shown publicly** - Flights with `beneficiary_type = 'organizer'` are returned. Client flights (`beneficiary_type = 'client'`) are private and not included in public API responses.

2. **Flights are only available for Accommodations**, not Events. If you query an Event (not Accommodation), the flights array will be empty.

2. **Flights are ordered by latest first** (most recent flights appear first).

3. **Return flight is optional** - if `return` is `null`, it's a one-way flight.

4. **eTicket URL** - Only included if an eTicket file was uploaded. Otherwise, `eticket_url` will be `null`.

5. **Airport Codes** - Airport fields contain IATA/ICAO codes (e.g., "CMN", "CDG", "RAK"). These are nullable strings.

6. **Prices** - All prices are in MAD (Moroccan Dirham). `total_price` is the sum of departure and return prices for round trips.

7. **Time Format** - All times are returned in `HH:MM` format (24-hour).

8. **Date Format** - All dates are returned in `YYYY-MM-DD` format.

## Frontend Integration

### Example: Fetching Flights for an Event

```javascript
// Fetch flights for a specific event
fetch('/api/events/seafood4africa/flights')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const flights = data.data;
      // Render flights in your component
      flights.forEach(flight => {
        console.log(`Flight ${flight.reference}: ${flight.departure.flight_number}`);
      });
    }
  });
```

### Example: Using Flights from Event Response

```javascript
// Fetch event details (includes flights)
fetch('/api/events/seafood4africa')
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      const event = data.data;
      const hotels = event.hotels || [];
      const flights = event.flights || [];
      
      // Render hotels
      // Render flights below hotels
    }
  });
```

## Display Recommendations

Flights should be displayed:
- **Below hotels** in the event landing page
- **Grouped by date** or **sorted by departure date**
- **Show key information**: Flight number, dates, times, class
- **Link to eTicket** if available (use `eticket_url`)
- **Indicate one-way vs round-trip** based on `return` field

## Route Order

The flights routes are placed **before** the generic `/events/{slug}` route to avoid route conflicts. The order in `routes/api.php` is:

1. `/events/{slug}/hotels` (specific)
2. `/events/{slug}/airports` (specific)
3. `/events/{slug}/flights` (specific) ← **New**
4. `/events/{slug}` (generic - must be last)

This ensures the specific routes are matched before the generic event route.

