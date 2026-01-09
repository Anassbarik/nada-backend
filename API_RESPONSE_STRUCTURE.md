# API Response Structure Documentation

## 1. Hotel API Response

### Endpoint: `GET /api/events/{event-slug}/hotels/{hotel-slug}`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "event_id": 1,
    "name": "DAKHLA SUR MER",
    "slug": "dakhla-sur-mer",
    "location": "13 Boulevard Mohammed V, Dakhla 73000",
    "location_url": "https://maps.app.goo.gl/EthnBu2YoGf8zsux7",
    "duration": "12-minutes drive to the exhibition site",
    "description": "Hotel description text...",
    "website": "https://www.dakhlasurmer.com",
    "rating": 4.5,
    "review_count": 120,
    "status": "active",
    "created_at": "2026-01-03T22:24:03.000000Z",
    "updated_at": "2026-01-08T22:46:59.000000Z",
    "rating_stars": 4.5,
    "event": {
      "id": 1,
      "name": "Seafood4Africa",
      "slug": "seafood4africa",
      "venue": "Exhibition Center",
      "location": "Dakhla, Morocco",
      "google_maps_url": "https://maps.google.com/...",
      "start_date": "2026-02-02",
      "end_date": "2026-02-07",
      "formatted_dates": "02 Feb 2026 - 07 Feb 2026",
      "compact_dates": "02-07 Feb 2026",
      "website_url": "https://seafood4africa.com",
      "organizer_logo": "https://example.com/organizer-logo.png",
      "organizer_logo_path": "events/organizers/logo.png",
      "logo_url": "https://example.com/logo.png",
      "logo_path": "events/logos/logo.png",
      "banner_url": "https://example.com/banner.png",
      "banner_path": "events/banners/banner.png",
      "description": "Event description...",
      "menu_links": [],
      "status": "published",
      "created_at": "2026-01-01T00:00:00.000000Z",
      "updated_at": "2026-01-01T00:00:00.000000Z"
    },
    "packages": [
      {
        "id": 1,
        "hotel_id": 1,
        "nom_package": "Package Single",
        "type_chambre": "Chambre Single",
        "check_in": "2026-02-02",
        "check_out": "2026-02-07",
        "occupants": 1,
        "prix_ht": "7750.00",
        "prix_ttc": "9300.00",
        "quantite_chambres": 21,
        "chambres_restantes": 21,
        "disponibilite": true,
        "inclusions": [
          "Hébergement selon la chambre choisie",
          "Petit déjeuner inclus",
          "Transfert aéroport hôtel",
          "Transfert hôtel Salon Seafood4Africa (aller-retour quotidien)",
          "Assistance Seafood4Africa 24/7 (WhatsApp & téléphone)",
          "Le prix indiqué est par chambre et non par personne – Total TTC"
        ],
        "created_by": 1,
        "created_at": "2026-01-03T22:32:58.000000Z",
        "updated_at": "2026-01-07T20:13:16.000000Z"
      }
    ],
    "images": [
      {
        "id": 1,
        "url": "https://example.com/storage/hotels/1/image1.jpg",
        "alt_text": "Hotel exterior view",
        "is_primary": true
      },
      {
        "id": 2,
        "url": "https://example.com/storage/hotels/1/image2.jpg",
        "alt_text": "Hotel room",
        "is_primary": false
      }
    ]
  }
}
```

### Field Descriptions:

**Hotel Fields:**
- `id`: Hotel unique identifier
- `event_id`: ID of the event this hotel belongs to
- `name`: Hotel name
- `slug`: URL-friendly identifier for the hotel
- `location`: Physical address of the hotel
- `location_url`: Google Maps URL
- `duration`: Travel time/distance to event venue
- `description`: Hotel description text
- `website`: Hotel website URL
- `rating`: Numeric rating (0-5)
- `review_count`: Number of reviews
- `status`: Hotel status ("active" or "inactive")
- `rating_stars`: Same as rating (for frontend convenience)
- `event`: Full event object (see Event structure below)

**Package Fields:**
- `id`: Package unique identifier
- `hotel_id`: ID of the hotel this package belongs to
- `nom_package`: Package name
- `type_chambre`: Room type
- `check_in`: Check-in date (YYYY-MM-DD)
- `check_out`: Check-out date (YYYY-MM-DD)
- `occupants`: Number of occupants
- `prix_ht`: Price excluding tax (HT)
- `prix_ttc`: Price including tax (TTC)
- `quantite_chambres`: Total number of rooms
- `chambres_restantes`: Remaining available rooms
- `disponibilite`: Availability status (boolean)
- `inclusions`: **Array of strings** - What's included in the package (e.g., breakfast, transfers, etc.)
- `created_by`: ID of admin who created the package
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Image Fields:**
- `id`: Image unique identifier
- `url`: Full URL to the image
- `alt_text`: Alternative text for the image
- `is_primary`: Boolean indicating if this is the primary image

---

## 2. Event API Response (with Content and Airports)

### Endpoint: `GET /api/events/{event-slug}`

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Seafood4Africa",
    "slug": "seafood4africa",
    "venue": "Exhibition Center",
    "location": "Dakhla, Morocco",
    "google_maps_url": "https://maps.google.com/...",
    "start_date": "2026-02-02",
    "end_date": "2026-02-07",
    "formatted_dates": "02 Feb 2026 - 07 Feb 2026",
    "compact_dates": "02-07 Feb 2026",
    "website_url": "https://seafood4africa.com",
    "organizer_logo": "https://example.com/organizer-logo.png",
    "organizer_logo_path": "events/organizers/logo.png",
    "logo_url": "https://example.com/logo.png",
    "logo_path": "events/logos/logo.png",
    "banner_url": "https://example.com/banner.png",
    "banner_path": "events/banners/banner.png",
    "description": "Event description text...",
    "menu_links": [],
    "status": "published",
    "created_at": "2026-01-01T00:00:00.000000Z",
    "updated_at": "2026-01-01T00:00:00.000000Z",
    "airports": [
      {
        "id": 1,
        "event_id": 1,
        "name": "Mohammed V International Airport",
        "code": "CMN",
        "city": "Casablanca",
        "country": "Morocco",
        "description": "Main international airport serving Casablanca",
        "distance_from_venue": "45.50",
        "distance_unit": "km",
        "sort_order": 1,
        "active": true,
        "created_by": 1,
        "created_at": "2026-01-09T18:00:00.000000Z",
        "updated_at": "2026-01-09T18:00:00.000000Z"
      },
      {
        "id": 2,
        "event_id": 1,
        "name": "Marrakech Menara Airport",
        "code": "RAK",
        "city": "Marrakech",
        "country": "Morocco",
        "description": null,
        "distance_from_venue": "350.00",
        "distance_unit": "km",
        "sort_order": 2,
        "active": true,
        "created_by": 1,
        "created_at": "2026-01-09T18:00:00.000000Z",
        "updated_at": "2026-01-09T18:00:00.000000Z"
      }
    ],
    "contents": {
      "conditions": {
        "hero_image": "https://example.com/storage/events/1/conditions-hero.jpg",
        "hero_image_path": "events/1/conditions-hero.jpg",
        "content": "Simple text content (if using content field instead of sections)",
        "sections": [
          {
            "type": "heading",
            "content": "Terms and Conditions"
          },
          {
            "type": "paragraph",
            "content": "Lorem ipsum dolor sit amet..."
          },
          {
            "type": "list",
            "items": [
              "Item 1",
              "Item 2",
              "Item 3"
            ]
          }
        ]
      },
      "info": {
        "hero_image": "https://example.com/storage/events/1/info-hero.jpg",
        "hero_image_path": "events/1/info-hero.jpg",
        "sections": [
          {
            "type": "heading",
            "content": "Event Information"
          },
          {
            "type": "paragraph",
            "content": "Event details..."
          }
        ]
      },
      "faq": {
        "hero_image": "https://example.com/storage/events/1/faq-hero.jpg",
        "hero_image_path": "events/1/faq-hero.jpg",
        "sections": [
          {
            "type": "heading",
            "content": "Frequently Asked Questions"
          },
          {
            "type": "faq_item",
            "question": "What is the event about?",
            "answer": "This event is about..."
          }
        ]
      }
    },
    "hotels": [
      {
        "id": 1,
        "event_id": 1,
        "name": "DAKHLA SUR MER",
        "slug": "dakhla-sur-mer",
        "location": "13 Boulevard Mohammed V, Dakhla 73000",
        "location_url": "https://maps.app.goo.gl/EthnBu2YoGf8zsux7",
        "duration": "12-minutes drive to the exhibition site",
        "description": "Hotel description...",
        "website": "https://www.dakhlasurmer.com",
        "rating": 4.5,
        "review_count": 120,
        "status": "active",
        "created_at": "2026-01-03T22:24:03.000000Z",
        "updated_at": "2026-01-08T22:46:59.000000Z",
        "packages": [
          {
            "id": 1,
            "hotel_id": 1,
            "nom_package": "Package Single",
            "type_chambre": "Chambre Single",
            "check_in": "2026-02-02",
            "check_out": "2026-02-07",
            "occupants": 1,
            "prix_ht": "7750.00",
            "prix_ttc": "9300.00",
            "quantite_chambres": 21,
            "chambres_restantes": 21,
            "disponibilite": true,
            "inclusions": [
              "Hébergement selon la chambre choisie",
              "Petit déjeuner inclus",
              "Transfert aéroport hôtel"
            ],
            "created_by": 1,
            "created_at": "2026-01-03T22:32:58.000000Z",
            "updated_at": "2026-01-07T20:13:16.000000Z"
          }
        ],
        "images": [
          {
            "id": 1,
            "url": "https://example.com/storage/hotels/1/image1.jpg",
            "alt_text": "Hotel exterior",
            "is_primary": true
          }
        ]
      }
    ]
  }
}
```

### Event Content Structure:

The `contents` object contains content for different page types:

**Available Page Types:**
- `conditions`: Terms and conditions page
- `info` or `informations`: General information page
- `faq`: Frequently asked questions page

**Each Content Object Contains:**
- `hero_image`: Full URL to the hero image
- `hero_image_path`: Storage path to the hero image
- `content`: Simple text content (nullable, used for simple text-based content)
- `sections`: Array of content sections (structure varies by page type, used for structured content)

**Section Types:**
- `heading`: Section heading
- `paragraph`: Text paragraph
- `list`: List of items
- `faq_item`: FAQ question/answer pair
- Custom types as needed

**Note:** If a page type doesn't exist, it won't be in the `contents` object. Always check if the key exists before accessing.

**Airports Array:**
- Contains all active airports for the event
- Ordered by `sort_order` then `name`
- Only includes airports where `active` is `true`
- Each airport includes all fields listed above

---

## 3. Additional Endpoints

### Get Airports for Event: `GET /api/events/{event-slug}/airports`
Returns an array of active airports for the event.

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "event_id": 1,
      "name": "Mohammed V International Airport",
      "code": "CMN",
      "city": "Casablanca",
      "country": "Morocco",
      "description": "Main international airport serving Casablanca",
      "distance_from_venue": "45.50",
      "distance_unit": "km",
      "sort_order": 1,
      "active": true,
      "created_by": 1,
      "created_at": "2026-01-09T18:00:00.000000Z",
      "updated_at": "2026-01-09T18:00:00.000000Z"
    }
  ]
}
```

**Airport Fields:**
- `id`: Airport unique identifier
- `event_id`: ID of the event this airport belongs to
- `name`: Airport name
- `code`: Airport code (IATA/ICAO) - nullable
- `city`: City where airport is located - nullable
- `country`: Country where airport is located - nullable
- `description`: Airport description - nullable
- `distance_from_venue`: Distance from event venue (decimal) - nullable
- `distance_unit`: Unit of distance ("km" or "miles")
- `sort_order`: Display order (lower numbers first)
- `active`: Whether airport is active (boolean)
- `created_by`: ID of admin who created the airport
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

### Get Hotels for Event: `GET /api/events/{event-slug}/hotels`
Returns an array of hotels (same structure as above, but in an array).

### Get All Hotels: `GET /api/hotels`
Returns all active hotels from all published events.

### Get Event Content by Type: `GET /api/events/{event-slug}/{type}`
Where `type` can be: `conditions`, `info`, `informations`, or `faq`

```json
{
  "success": true,
  "data": {
    "event": {
      "id": 1,
      "name": "Seafood4Africa",
      "slug": "seafood4africa"
    },
    "type": "conditions",
    "content": "Content text or JSON string of sections"
  }
}
```

---

## Important Notes:

1. **Inclusions Field**: Always an array of strings. Can be `null` or empty array `[]` if no inclusions are set.

2. **Content Sections**: The structure of sections is flexible and can vary. Always check the structure before rendering.

3. **Image URLs**: All image URLs are full URLs, not relative paths.

4. **Dates**: All dates are in ISO 8601 format (YYYY-MM-DD or YYYY-MM-DDTHH:mm:ss.sssZ).

5. **Null Values**: Some fields may be `null` if not set. Always handle null cases in the frontend.

