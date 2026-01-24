# Frontend Agent Prompt: Flights Implementation

## Task Overview

Implement a complete flights display system in the frontend application, including:
1. **Flights carousel** on the events landing page
2. **Flight details page** for individual flights
3. **Price visibility handling** with three separate settings
4. **Modern design language** matching existing hotel components

## Backend API Endpoints

### 1. Get Event with Flights
**Endpoint:** `GET /api/events/{slug}`

**Response includes:**
- Event data with `show_flight_prices_public` setting
- `flights`` array with flight details
- Prices conditionally included based on `show_flight_prices_public`

### 2. List Flights for Event
**Endpoint:** `GET /api/events/{slug}/flights`

**Returns:** Array of flights for the event

### 3. Get Single Flight Details
**Endpoint:** `GET /api/events/{slug}/flights/{flight}`

**Returns:** Single flight object with full details

## Implementation Requirements

### 1. Flights Carousel Component

**Location:** Events landing page (`/events/{slug}`), after hotels section

**Requirements:**
- Use the same modern design language as the hotels carousel
- Responsive design (mobile, tablet, desktop)
- Smooth carousel/slider functionality
- Card-based layout matching hotel cards
- Hover effects and transitions

**Design Elements:**
- Card background: White
- Border radius: `rounded-lg`
- Shadow: `shadow-md` with `hover:shadow-xl`
- Padding: `p-6`
- Gap between cards: `gap-6` or `gap-8`

**Flight Card Content:**
- Flight class badge (Economy/Business/First)
- Flight category label (One-way/Round Trip)
- Departure flight details:
  - Flight number (bold, large)
  - Route (airport → airport)
  - Date and time
  - Price (if `show_flight_prices_public = true`)
- Return flight details (if round trip):
  - Same structure as departure
  - Price (if `show_flight_prices_public = true`)
- Total price (if round trip and prices shown)
- "View Details" button linking to flight details page

**Price Handling:**
- Check `event.show_flight_prices_public` setting
- Only display prices if setting is `true` AND price fields exist
- Show "Price information available upon request" when prices hidden
- Never show "N/A", "0.00", or empty price values

### 2. Flight Details Page

**Route:** `/events/{slug}/flights/{flightId}`

**Page Structure:**
- Hero section with flight reference and status
- Two-column layout (desktop) / single column (mobile)
- Departure flight details card
- Return flight details card (if round trip)
- Price summary section (if prices shown)
- Call-to-action buttons

**Details to Display:**
- Flight reference
- Client name
- Flight class
- Flight category
- Departure:
  - Date, time
  - Flight number
  - Departure airport → Arrival airport
  - Price (if shown)
- Arrival:
  - Date, time
  - Airport
- Return (if applicable):
  - Same structure as departure
  - Price (if shown)
- Total price (if round trip and prices shown)
- Status badge
- Payment method

### 3. Price Visibility Logic

**Three Settings:**
1. `show_flight_prices_public` - Events landing page and flight details page
2. `show_flight_prices_client_dashboard` - Client dashboard (for their bookings)
3. `show_flight_prices_organizer_dashboard` - Organizer dashboard (for their events)

**Implementation:**
```typescript
// Check if prices should be shown
const showPrices = event.show_flight_prices_public ?? true;

// Check if price exists before displaying
if (showPrices && flight.departure.price_ttc) {
  // Display price
} else {
  // Show "Price information available upon request"
}
```

### 4. Design Language

**Colors:**
- Primary: `#00adf1` (Blue) - for prices, buttons, links
- Secondary: `#83ce2f` (Green) - for confirmed/active states
- Accent: `#f7cb00` (Yellow) - for warnings/pending
- Text: Gray scale for hierarchy

**Typography:**
- Headings: Bold, 2xl-4xl
- Body: Regular, base-lg
- Labels: Medium, sm-base
- Prices: Bold, xl-2xl, primary color

**Components:**
- Match hotel card styling exactly
- Same hover effects (`hover:shadow-xl`, `transition-shadow`)
- Same responsive breakpoints
- Same button styles

### 5. Flight Data Structure

```typescript
interface Flight {
  id: number;
  full_name: string;
  flight_class: string;
  flight_class_label: string;
  flight_category: string;
  flight_category_label: string;
  departure: {
    date: string; // "2026-02-01"
    time: string; // "10:30"
    flight_number: string; // "AT2222"
    airport: string; // "CMN"
    price_ttc?: number; // Optional - only if show_flight_prices_public = true
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
    price_ttc?: number; // Optional
  };
  reference: string;
  total_price?: number; // Optional - only if show_flight_prices_public = true
  eticket_url?: string;
  beneficiary_type: "organizer" | "client";
  status: "pending" | "paid";
  payment_method?: "wallet" | "bank" | "both";
}
```

## Implementation Steps

1. **Create Flight Components:**
   - `FlightsCarousel.tsx` - Main carousel component
   - `FlightCard.tsx` - Individual flight card
   - `FlightDetails.tsx` - Flight details page content
   - `FlightSegment.tsx` - Departure/return segment component
   - `PriceSummary.tsx` - Price breakdown component

2. **Create Utility Functions:**
   - `shouldShowPublicPrices()` - Check public price visibility
   - `hasFlightPrice()` - Check if price data exists
   - `getFlightPriceDisplay()` - Format price for display

3. **Integrate with Events Page:**
   - Add flights carousel after hotels section
   - Pass `show_flight_prices_public` from event data
   - Handle empty flights array gracefully

4. **Create Flight Details Route:**
   - Add route `/events/[slug]/flights/[flightId]`
   - Fetch flight data from API
   - Display all flight information
   - Handle price visibility

5. **Add Price Visibility Logic:**
   - Check setting before displaying prices
   - Show appropriate message when hidden
   - Maintain layout consistency

## Code Examples

### Flights Carousel Integration

```tsx
// pages/events/[slug].tsx
import { FlightsCarousel } from '@/components/flights/FlightsCarousel';

export default function EventPage() {
  const { data } = useEvent(slug);
  const event = data?.data;
  
  return (
    <div>
      {/* Hotels Section */}
      <HotelsSection hotels={event.hotels} />
      
      {/* Flights Carousel */}
      {event.flights && event.flights.length > 0 && (
        <FlightsCarousel 
          flights={event.flights}
          showPrices={event.show_flight_prices_public ?? true}
          eventSlug={event.slug}
        />
      )}
    </div>
  );
}
```

### Price Display Logic

```tsx
// components/flights/FlightCard.tsx
function FlightPrice({ flight, showPrices }) {
  if (!showPrices) {
    return (
      <p className="text-sm text-gray-500 italic text-center mt-4">
        Price information available upon request
      </p>
    );
  }
  
  if (flight.total_price) {
    return (
      <div className="text-2xl font-bold text-blue-600">
        {flight.total_price.toFixed(2)} MAD
      </div>
    );
  }
  
  return null;
}
```

## Testing Requirements

- [ ] Flights carousel displays on events landing page
- [ ] All flight details are visible (dates, times, airports, flight numbers)
- [ ] Prices show when `show_flight_prices_public = true`
- [ ] Prices hidden when `show_flight_prices_public = false`
- [ ] "Price information available upon request" shows when prices hidden
- [ ] Round-trip flights display both departure and return
- [ ] One-way flights display only departure
- [ ] Flight details page works correctly
- [ ] Responsive design on all screen sizes
- [ ] Carousel navigation works smoothly
- [ ] Links to flight details page work
- [ ] No errors when price fields are missing
- [ ] Design matches hotel components

## Design Reference

Use the existing hotels carousel and hotel cards as design reference:
- Same card styling
- Same hover effects
- Same spacing and layout
- Same color scheme
- Same typography
- Same responsive behavior

## Documentation

Refer to `FLIGHTS_FRONTEND_IMPLEMENTATION_GUIDE.md` for:
- Complete API documentation
- Detailed component examples
- Helper functions
- Testing checklist
- Design guidelines

## Important Notes

1. **Always check for price field existence** - Use optional chaining (`flight.departure?.price_ttc`)
2. **Never show empty prices** - If price is hidden, show message instead
3. **Maintain layout consistency** - Don't leave gaps where prices would be
4. **Use same design language** - Match hotels exactly for consistency
5. **Handle all edge cases** - Empty flights, missing data, etc.

## Questions?

Refer to:
- `FLIGHTS_FRONTEND_IMPLEMENTATION_GUIDE.md` - Complete implementation guide
- `FLIGHTS_API_DOCUMENTATION.md` - API reference
- Backend team for API questions

