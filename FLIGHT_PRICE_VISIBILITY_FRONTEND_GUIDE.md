# Flight Price Visibility - Frontend Implementation Guide

## Overview

The `show_flight_prices` setting in the accommodation/event configuration now controls flight price visibility in **two contexts**:

1. **Public Events Landing Page** - Controls whether flight prices are shown to anonymous visitors
2. **Client Dashboard** - Controls whether logged-in clients can see flight prices for their own bookings

## Backend Implementation

### API Endpoints Affected

#### 1. `GET /api/bookings` (User's Bookings List)
- **Authentication**: Required (Bearer token)
- **Response**: Array of user's bookings
- **Flight Data Structure**:
  ```json
  {
    "id": 1,
    "booking_reference": "BOOK-20260107-ABC",
    "flight": {
      "id": 123,
      "full_name": "John Doe",
      "flight_class": "economy",
      "flight_class_label": "Economy",
      "flight_category": "round_trip",
      "flight_category_label": "Aller-Retour (Round Trip)",
      "departure": {
        "date": "2026-02-01",
        "time": "10:30",
        "flight_number": "AT2222",
        "airport": "CMN"
        // "price_ttc": 1500.00  // Only included if show_flight_prices = true
      },
      "arrival": {
        "date": "2026-02-01",
        "time": "14:30",
        "airport": "RAK"
      },
      "return": {
        "date": "2026-02-10",
        "departure_time": "16:00",
        "departure_airport": "RAK",
        "arrival_date": "2026-02-10",
        "arrival_time": "20:00",
        "arrival_airport": "CMN",
        "flight_number": "AT1111"
        // "price_ttc": 1200.00  // Only included if show_flight_prices = true
      },
      "reference": "FLIGHT-20260107-XYZ"
      // "total_price": 2700.00  // Only included if show_flight_prices = true
    },
    "event": {
      "id": 1,
      "name": "SEAFOOD4AFRICA",
      "show_flight_prices": true  // This indicates the setting for this accommodation
    }
  }
  ```

#### 2. `GET /api/bookings/{id}` (Single Booking Details)
- **Authentication**: Required (Bearer token)
- **Response**: Single booking object with same flight structure as above

### Key Points

1. **Price Fields Conditionally Included**:
   - `flight.departure.price_ttc` - Only present if `show_flight_prices = true`
   - `flight.return.price_ttc` - Only present if `show_flight_prices = true` (for round trips)
   - `flight.total_price` - Only present if `show_flight_prices = true`

2. **Setting Indicator**:
   - The `event.show_flight_prices` field in the booking response indicates the current setting
   - Use this to determine if prices should be displayed

3. **Always Available Fields** (regardless of setting):
   - Flight dates and times
   - Flight numbers
   - Airport codes
   - Flight class and category
   - Flight reference

## Frontend Implementation

### Recommended Approach

#### 1. Check for Price Availability

```typescript
interface Booking {
  id: number;
  booking_reference: string;
  flight?: {
    departure: {
      date: string;
      time: string;
      flight_number: string;
      airport: string;
      price_ttc?: number;  // Optional - only if show_flight_prices = true
    };
    arrival: {
      date: string;
      time: string;
      airport: string;
    };
    return?: {
      date: string;
      departure_time: string;
      departure_airport: string;
      arrival_date: string;
      arrival_time: string;
      arrival_airport: string;
      flight_number: string;
      price_ttc?: number;  // Optional - only if show_flight_prices = true
    };
    total_price?: number;  // Optional - only if show_flight_prices = true
  };
  event?: {
    show_flight_prices: boolean;
  };
}

// Helper function to check if prices should be shown
function shouldShowFlightPrices(booking: Booking): boolean {
  return booking.event?.show_flight_prices ?? true;
}

// Helper function to check if price exists
function hasFlightPrice(flight: Booking['flight']): boolean {
  if (!flight) return false;
  return 'price_ttc' in flight.departure || 'total_price' in flight;
}
```

#### 2. Display Logic

```tsx
// React Component Example
function FlightDetails({ booking }: { booking: Booking }) {
  const { flight } = booking;
  const showPrices = shouldShowFlightPrices(booking);
  
  if (!flight) return null;
  
  return (
    <div className="flight-details">
      <h3>Flight Information</h3>
      
      {/* Departure */}
      <div className="departure">
        <p>Departure: {flight.departure.date} at {flight.departure.time}</p>
        <p>Flight: {flight.departure.flight_number}</p>
        <p>Route: {flight.departure.airport} → {flight.arrival.airport}</p>
        
        {/* Conditionally show price */}
        {showPrices && flight.departure.price_ttc && (
          <p className="price">Price: {flight.departure.price_ttc} MAD</p>
        )}
      </div>
      
      {/* Return Flight (if exists) */}
      {flight.return && (
        <div className="return">
          <p>Return: {flight.return.date} at {flight.return.departure_time}</p>
          <p>Flight: {flight.return.flight_number}</p>
          <p>Route: {flight.return.departure_airport} → {flight.return.arrival_airport}</p>
          
          {/* Conditionally show price */}
          {showPrices && flight.return.price_ttc && (
            <p className="price">Price: {flight.return.price_ttc} MAD</p>
          )}
        </div>
      )}
      
      {/* Total Price (if available) */}
      {showPrices && flight.total_price && (
        <div className="total-price">
          <strong>Total Flight Price: {flight.total_price} MAD</strong>
        </div>
      )}
      
      {/* Message when prices are hidden */}
      {!showPrices && (
        <div className="price-hidden-message">
          <p className="text-muted">
            Flight prices are not available for this booking.
          </p>
        </div>
      )}
    </div>
  );
}
```

#### 3. Booking List Display

```tsx
function BookingCard({ booking }: { booking: Booking }) {
  const showPrices = shouldShowFlightPrices(booking);
  
  return (
    <div className="booking-card">
      <h4>{booking.booking_reference}</h4>
      
      {booking.flight && (
        <div className="flight-summary">
          <p>
            {booking.flight.departure.flight_number} 
            ({booking.flight.departure.airport} → {booking.flight.arrival.airport})
          </p>
          
          {/* Only show price if available */}
          {showPrices && booking.flight.total_price && (
            <p className="price">Total: {booking.flight.total_price} MAD</p>
          )}
        </div>
      )}
    </div>
  );
}
```

### Error Handling

Always check for the existence of price fields before displaying them:

```typescript
// ✅ Good - Safe access
const price = flight?.departure?.price_ttc;
if (price !== undefined) {
  displayPrice(price);
}

// ❌ Bad - Will break if price is hidden
const price = flight.departure.price_ttc;  // May be undefined
displayPrice(price);  // Will show "undefined" or break
```

### UI/UX Recommendations

1. **When Prices Are Hidden**:
   - Show all flight details (dates, times, airports, flight numbers)
   - Display a subtle message: "Price information not available"
   - Do not show empty price fields or "N/A" values
   - Maintain consistent layout (don't leave gaps where prices would be)

2. **When Prices Are Visible**:
   - Display prices prominently
   - Show breakdown for round trips (departure + return = total)
   - Format prices with currency symbol and proper formatting

3. **Visual Indicators**:
   - Consider using a subtle icon or badge when prices are hidden
   - Use consistent styling for price display across the application

## Testing Checklist

- [ ] Verify prices are shown when `show_flight_prices = true`
- [ ] Verify prices are hidden when `show_flight_prices = false`
- [ ] Test with one-way flights (only departure price)
- [ ] Test with round-trip flights (departure + return prices)
- [ ] Verify flight details (dates, times, airports) are always visible
- [ ] Test booking list view with mixed accommodations (some show prices, some don't)
- [ ] Test single booking detail view
- [ ] Verify no errors when price fields are missing
- [ ] Test with bookings that have no flight data

## Migration Notes

- Existing bookings will continue to work
- The `show_flight_prices` field defaults to `true` if not set
- Frontend should gracefully handle missing price fields
- No breaking changes - price fields are optional additions

## Questions or Issues?

If you encounter any issues or need clarification, please refer to:
- API endpoint documentation
- Backend team for setting changes
- This guide for frontend implementation patterns

