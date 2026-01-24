# Flights Frontend Implementation Guide

## Overview

This guide provides complete instructions for implementing flights display in the frontend, including:
- Flights carousel on events landing page
- Flight details page
- Price visibility handling (three separate settings)
- Modern design language integration

## Backend Changes Summary

### Three Price Visibility Settings

The backend now supports **three separate settings** for flight price visibility:

1. **`show_flight_prices_public`** - Controls prices on events landing page and flight details page (public)
2. **`show_flight_prices_client_dashboard`** - Controls prices in client dashboard (for their own bookings)
3. **`show_flight_prices_organizer_dashboard`** - Controls prices in organizer dashboard (for their events)

All three settings default to `true` if not set.

## API Endpoints

### 1. Get Event with Flights
**Endpoint:** `GET /api/events/{slug}`

**Response Structure:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "SEAFOOD4AFRICA",
    "slug": "seafood4africa",
    "show_flight_prices_public": true,
    "show_flight_prices_client_dashboard": true,
    "show_flight_prices_organizer_dashboard": true,
    "flights": [
      {
        "id": 123,
        "accommodation_id": 1,
        "full_name": "John Doe",
        "flight_class": "economy",
        "flight_class_label": "Economy",
        "flight_category": "round_trip",
        "flight_category_label": "Aller-Retour (Round Trip)",
        "departure": {
          "date": "2026-02-01",
          "time": "10:30",
          "flight_number": "AT2222",
          "airport": "CMN",
          "price_ttc": 1500.00  // Only if show_flight_prices_public = true
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
          "flight_number": "AT1111",
          "price_ttc": 1200.00  // Only if show_flight_prices_public = true
        },
        "reference": "FLIGHT-20260107-XYZ",
        "eticket_url": "https://...",
        "beneficiary_type": "organizer",
        "status": "pending",
        "payment_method": "wallet",
        "total_price": 2700.00,  // Only if show_flight_prices_public = true
        "created_at": "2026-01-07 10:30:00",
        "updated_at": "2026-01-07 10:30:00"
      }
    ]
  }
}
```

### 2. List Flights for Event
**Endpoint:** `GET /api/events/{slug}/flights`

**Response:**
```json
{
  "success": true,
  "data": [
    // Same structure as flights array above
  ]
}
```

### 3. Get Single Flight Details
**Endpoint:** `GET /api/events/{slug}/flights/{flight}`

**Response:**
```json
{
  "success": true,
  "data": {
    // Same structure as single flight object above
    "accommodation": {
      "id": 1,
      "name": "SEAFOOD4AFRICA",
      "slug": "seafood4africa"
    }
  }
}
```

### 4. Get User's Bookings (Client Dashboard)
**Endpoint:** `GET /api/bookings` (requires authentication)

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "booking_reference": "BOOK-20260107-ABC",
      "flight": {
        "departure": {
          "price_ttc": 1500.00  // Only if show_flight_prices_client_dashboard = true
        },
        "return": {
          "price_ttc": 1200.00  // Only if show_flight_prices_client_dashboard = true
        },
        "total_price": 2700.00  // Only if show_flight_prices_client_dashboard = true
      },
      "event": {
        "show_flight_prices_client_dashboard": true
      }
    }
  ]
}
```

## Frontend Implementation

### 1. Flights Carousel on Events Landing Page

#### Location
Display flights in a carousel/slider section on the events landing page, typically after the hotels section.

#### Design Requirements
- Use the same modern design language as the hotels carousel
- Responsive design (mobile, tablet, desktop)
- Smooth animations and transitions
- Card-based layout with hover effects

#### Component Structure

```tsx
// components/flights/FlightsCarousel.tsx
interface Flight {
  id: number;
  full_name: string;
  flight_class: string;
  flight_class_label: string;
  flight_category: string;
  flight_category_label: string;
  departure: {
    date: string;
    time: string;
    flight_number: string;
    airport: string;
    price_ttc?: number;  // Optional
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
    price_ttc?: number;  // Optional
  };
  reference: string;
  total_price?: number;  // Optional
}

interface FlightsCarouselProps {
  flights: Flight[];
  showPrices: boolean;  // From event.show_flight_prices_public
  eventSlug: string;
}

export function FlightsCarousel({ flights, showPrices, eventSlug }: FlightsCarouselProps) {
  if (!flights || flights.length === 0) {
    return null; // Don't render if no flights
  }

  return (
    <section className="flights-section py-12 bg-gray-50">
      <div className="container mx-auto px-4">
        <div className="flex items-center justify-between mb-8">
          <h2 className="text-3xl font-bold">Available Flights</h2>
          <a 
            href={`/events/${eventSlug}/flights`}
            className="text-blue-600 hover:underline"
          >
            View All Flights →
          </a>
        </div>
        
        <div className="flights-carousel">
          {/* Implement carousel similar to hotels carousel */}
          {/* Use Swiper, Glide, or similar library */}
          {flights.map((flight) => (
            <FlightCard 
              key={flight.id} 
              flight={flight} 
              showPrices={showPrices}
              eventSlug={eventSlug}
            />
          ))}
        </div>
      </div>
    </section>
  );
}
```

#### Flight Card Component

```tsx
// components/flights/FlightCard.tsx
interface FlightCardProps {
  flight: Flight;
  showPrices: boolean;
  eventSlug: string;
}

export function FlightCard({ flight, showPrices, eventSlug }: FlightCardProps) {
  const isRoundTrip = flight.flight_category === 'round_trip';
  
  return (
    <div className="flight-card bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow p-6">
      {/* Flight Class Badge */}
      <div className="flex items-center justify-between mb-4">
        <span className="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
          {flight.flight_class_label}
        </span>
        <span className="text-sm text-gray-500">
          {flight.flight_category_label}
        </span>
      </div>

      {/* Departure Flight */}
      <div className="mb-4">
        <div className="flex items-center gap-3 mb-2">
          <div className="flex-1">
            <div className="font-semibold text-lg">{flight.departure.flight_number}</div>
            <div className="text-sm text-gray-600">
              {flight.departure.airport} → {flight.arrival.airport}
            </div>
          </div>
          {showPrices && flight.departure.price_ttc && (
            <div className="text-right">
              <div className="text-lg font-bold text-blue-600">
                {flight.departure.price_ttc.toFixed(2)} MAD
              </div>
              <div className="text-xs text-gray-500">Departure</div>
            </div>
          )}
        </div>
        <div className="text-sm text-gray-500">
          {new Date(flight.departure.date).toLocaleDateString('fr-FR', { 
            weekday: 'short', 
            day: 'numeric', 
            month: 'short' 
          })} at {flight.departure.time}
        </div>
      </div>

      {/* Return Flight (if round trip) */}
      {isRoundTrip && flight.return && (
        <div className="mb-4 pt-4 border-t border-gray-200">
          <div className="flex items-center gap-3 mb-2">
            <div className="flex-1">
              <div className="font-semibold text-lg">{flight.return.flight_number}</div>
              <div className="text-sm text-gray-600">
                {flight.return.departure_airport} → {flight.return.arrival_airport}
              </div>
            </div>
            {showPrices && flight.return.price_ttc && (
              <div className="text-right">
                <div className="text-lg font-bold text-blue-600">
                  {flight.return.price_ttc.toFixed(2)} MAD
                </div>
                <div className="text-xs text-gray-500">Return</div>
              </div>
            )}
          </div>
          <div className="text-sm text-gray-500">
            {new Date(flight.return.date).toLocaleDateString('fr-FR', { 
              weekday: 'short', 
              day: 'numeric', 
              month: 'short' 
            })} at {flight.return.departure_time}
          </div>
        </div>
      )}

      {/* Total Price (if round trip and prices shown) */}
      {isRoundTrip && showPrices && flight.total_price && (
        <div className="pt-4 border-t-2 border-blue-200">
          <div className="flex items-center justify-between">
            <span className="font-semibold">Total Price:</span>
            <span className="text-2xl font-bold text-blue-600">
              {flight.total_price.toFixed(2)} MAD
            </span>
          </div>
        </div>
      )}

      {/* Price Hidden Message */}
      {!showPrices && (
        <div className="pt-4 border-t border-gray-200">
          <p className="text-sm text-gray-500 italic text-center">
            Price information available upon request
          </p>
        </div>
      )}

      {/* View Details Button */}
      <a
        href={`/events/${eventSlug}/flights/${flight.id}`}
        className="mt-4 block w-full text-center bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors"
      >
        View Details
      </a>
    </div>
  );
}
```

### 2. Flight Details Page

#### Route
`/events/{slug}/flights/{flightId}`

#### Page Structure

```tsx
// pages/events/[slug]/flights/[flightId].tsx
export default function FlightDetailsPage() {
  const router = useRouter();
  const { slug, flightId } = router.query;
  const { data, loading, error } = useFlightDetails(slug, flightId);

  if (loading) return <LoadingSpinner />;
  if (error) return <ErrorMessage error={error} />;
  if (!data) return <NotFound />;

  const { flight, accommodation } = data.data;
  const showPrices = accommodation.show_flight_prices_public ?? true;

  return (
    <div className="flight-details-page">
      <FlightDetailsHeader flight={flight} />
      <FlightDetailsContent flight={flight} showPrices={showPrices} />
      <FlightDetailsActions flight={flight} eventSlug={slug} />
    </div>
  );
}
```

#### Flight Details Component

```tsx
// components/flights/FlightDetails.tsx
export function FlightDetailsContent({ flight, showPrices }) {
  return (
    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
      {/* Departure Section */}
      <FlightSegment
        title="Departure Flight"
        flight={flight.departure}
        arrival={flight.arrival}
        showPrice={showPrices && flight.departure.price_ttc !== undefined}
        price={flight.departure.price_ttc}
      />

      {/* Return Section (if exists) */}
      {flight.return && (
        <FlightSegment
          title="Return Flight"
          flight={flight.return}
          showPrice={showPrices && flight.return.price_ttc !== undefined}
          price={flight.return.price_ttc}
        />
      )}

      {/* Price Summary */}
      {showPrices && flight.total_price && (
        <div className="lg:col-span-2">
          <PriceSummary flight={flight} />
        </div>
      )}
    </div>
  );
}
```

### 3. Price Visibility Handling

#### Helper Functions

```typescript
// utils/flightPrices.ts

/**
 * Check if prices should be shown for public display
 */
export function shouldShowPublicPrices(accommodation: Accommodation): boolean {
  return accommodation?.show_flight_prices_public ?? true;
}

/**
 * Check if prices should be shown in client dashboard
 */
export function shouldShowClientDashboardPrices(accommodation: Accommodation): boolean {
  return accommodation?.show_flight_prices_client_dashboard ?? true;
}

/**
 * Check if a flight has price data available
 */
export function hasFlightPrice(flight: Flight, context: 'public' | 'client' | 'organizer'): boolean {
  if (context === 'public') {
    return flight.departure.price_ttc !== undefined || 
           flight.total_price !== undefined;
  }
  // Similar logic for other contexts
  return false;
}

/**
 * Get flight price display value
 */
export function getFlightPriceDisplay(flight: Flight, showPrices: boolean): string | null {
  if (!showPrices) return null;
  
  if (flight.total_price) {
    return `${flight.total_price.toFixed(2)} MAD`;
  }
  
  if (flight.departure.price_ttc) {
    return `${flight.departure.price_ttc.toFixed(2)} MAD`;
  }
  
  return null;
}
```

### 4. Integration with Events Landing Page

```tsx
// pages/events/[slug].tsx
export default function EventPage() {
  const { slug } = useRouter().query;
  const { data } = useEvent(slug);
  
  if (!data) return <NotFound />;
  
  const event = data.data;
  const flights = event.flights || [];
  const showPrices = event.show_flight_prices_public ?? true;

  return (
    <div>
      {/* Hero Section */}
      <EventHero event={event} />
      
      {/* Hotels Section */}
      <HotelsSection hotels={event.hotels} />
      
      {/* Flights Carousel */}
      {flights.length > 0 && (
        <FlightsCarousel 
          flights={flights}
          showPrices={showPrices}
          eventSlug={slug}
        />
      )}
      
      {/* Other sections... */}
    </div>
  );
}
```

## Design Guidelines

### Color Scheme
- Primary: `#00adf1` (Blue) - matches existing design
- Secondary: `#83ce2f` (Green) - for confirmed/active states
- Accent: `#f7cb00` (Yellow) - for warnings/pending
- Text: Gray scale for hierarchy

### Typography
- Headings: Bold, 2xl-4xl
- Body: Regular, base-lg
- Labels: Medium, sm-base
- Prices: Bold, xl-2xl, primary color

### Spacing
- Card padding: `p-6`
- Section spacing: `py-12`
- Gap between cards: `gap-6` or `gap-8`

### Components to Match
- Use the same card style as hotel cards
- Same hover effects and transitions
- Same responsive breakpoints
- Same button styles

## Testing Checklist

- [ ] Flights carousel displays correctly on events landing page
- [ ] Flight cards show all flight details (dates, times, airports, flight numbers)
- [ ] Prices are shown when `show_flight_prices_public = true`
- [ ] Prices are hidden when `show_flight_prices_public = false`
- [ ] "Price information available upon request" message shows when prices hidden
- [ ] Flight details page displays all information correctly
- [ ] Round-trip flights show both departure and return details
- [ ] One-way flights show only departure details
- [ ] Responsive design works on mobile, tablet, desktop
- [ ] Carousel navigation works smoothly
- [ ] Links to flight details page work correctly
- [ ] Client dashboard respects `show_flight_prices_client_dashboard` setting
- [ ] No errors when price fields are missing

## API Response Examples

### Event with Flights (Public)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "SEAFOOD4AFRICA",
    "slug": "seafood4africa",
    "show_flight_prices_public": true,
    "flights": [
      {
        "id": 123,
        "departure": {
          "date": "2026-02-01",
          "time": "10:30",
          "flight_number": "AT2222",
          "airport": "CMN",
          "price_ttc": 1500.00
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
          "flight_number": "AT1111",
          "price_ttc": 1200.00
        },
        "total_price": 2700.00
      }
    ]
  }
}
```

### Event with Hidden Prices
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "SEAFOOD4AFRICA",
    "slug": "seafood4africa",
    "show_flight_prices_public": false,
    "flights": [
      {
        "id": 123,
        "departure": {
          "date": "2026-02-01",
          "time": "10:30",
          "flight_number": "AT2222",
          "airport": "CMN"
          // No price_ttc field
        },
        "arrival": {
          "date": "2026-02-01",
          "time": "14:30",
          "airport": "RAK"
        }
        // No total_price field
      }
    ]
  }
}
```

## Important Notes

1. **Always check for price field existence** before displaying prices
2. **Use optional chaining** when accessing nested price fields
3. **Show appropriate message** when prices are hidden (don't show "N/A" or empty values)
4. **Maintain consistent layout** whether prices are shown or hidden
5. **Test with all three visibility settings** to ensure correct behavior
6. **Use the same design language** as hotels for consistency

## Questions or Issues?

Refer to:
- `FLIGHT_PRICE_VISIBILITY_FRONTEND_GUIDE.md` for detailed price visibility implementation
- `FLIGHTS_API_DOCUMENTATION.md` for complete API reference
- Backend team for setting changes or API questions

