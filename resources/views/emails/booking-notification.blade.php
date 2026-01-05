<x-mail::message>
# New Booking Received

A new booking has been created and requires your attention.

## Booking Details

**Booking Reference:** {{ $booking->booking_reference ?? 'N/A' }}  
**Status:** {{ ucfirst($booking->status ?? 'pending') }}  
**Created At:** {{ $booking->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}

---

## Guest Information

**Full Name:** {{ $booking->full_name ?? $booking->guest_name ?? 'N/A' }}  
**Company:** {{ $booking->company ?? 'N/A' }}  
**Email:** {{ $booking->email ?? $booking->guest_email ?? 'N/A' }}  
**Phone:** {{ $booking->phone ?? $booking->guest_phone ?? 'N/A' }}

---

## Event & Hotel Details

**Event:** {{ $booking->event->name ?? 'N/A' }}  
**Hotel:** {{ $booking->hotel->name ?? 'N/A' }}  
**Package:** {{ $booking->package->nom_package ?? 'N/A' }}  
**Package Type:** {{ $booking->package->type_chambre ?? 'N/A' }}

---

## Booking Dates

**Check-in Date:** {{ $booking->checkin_date?->format('Y-m-d') ?? 'N/A' }}  
**Check-out Date:** {{ $booking->checkout_date?->format('Y-m-d') ?? 'N/A' }}  
**Guests Count:** {{ $booking->guests_count ?? 'N/A' }}

---

## Pricing

**Price:** {{ $booking->price ? number_format($booking->price, 2) . ' MAD' : ($booking->package->prix_ttc ? number_format($booking->package->prix_ttc, 2) . ' MAD' : 'N/A') }}

---

## Flight Information

**Flight Number:** {{ $booking->flight_number ?? 'N/A' }}  
**Flight Date:** {{ $booking->flight_date?->format('Y-m-d') ?? 'N/A' }}  
**Flight Time:** {{ $booking->flight_time?->format('H:i') ?? 'N/A' }}  
**Airport:** {{ $booking->airport ?? 'N/A' }}

---

## Resident Names

**Resident 1:** {{ $booking->resident_name_1 ?? 'N/A' }}  
**Resident 2:** {{ $booking->resident_name_2 ?? 'N/A' }}

---

## Special Instructions

{{ $booking->special_instructions ?? $booking->special_requests ?? 'None' }}

---

<x-mail::button :url="route('admin.bookings.index', absolute: true)">
View Booking in Admin Panel
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
