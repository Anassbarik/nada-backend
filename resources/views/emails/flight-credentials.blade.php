<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Booking Credentials</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #00adf1; margin: 0;">Flight Booking Credentials</h1>
        <p style="margin: 10px 0 0 0; color: #666;">Flight Reference: {{ $flight->reference }}</p>
        @if(isset($booking) && $booking->booking_reference)
            <p style="margin: 5px 0 0 0; color: #666;"><strong>Booking Reference:</strong> {{ $booking->booking_reference }}</p>
        @endif
    </div>

    @if(isset($booking) && $booking->booking_reference)
    <div style="background-color: #e7f3ff; padding: 20px; border: 1px solid #b3d9ff; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #333; margin-top: 0;">Booking Information</h2>
        <p><strong>Booking Reference:</strong> <span style="font-family: monospace; background-color: #fff; padding: 5px 10px; border-radius: 3px;">{{ $booking->booking_reference }}</span></p>
        <p><strong>Booking Status:</strong> {{ ucfirst($booking->status) }}</p>
        <p style="color: #666; font-size: 14px; margin-top: 10px;">⚠️ <strong>Important:</strong> Keep your booking reference handy. You will need it if you want to book a hotel package and link it to this flight booking.</p>
    </div>
    @endif

    <div style="background-color: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #333; margin-top: 0;">Flight Information</h2>
        <p><strong>Client Name:</strong> {{ $flight->full_name }}</p>
        <p><strong>Flight Class:</strong> {{ $flight->flight_class_label }}</p>
        <p><strong>Departure:</strong> {{ $flight->departure_date ? \Carbon\Carbon::parse($flight->departure_date)->format('F d, Y') : 'N/A' }} at {{ $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : 'N/A' }} - Flight {{ $flight->departure_flight_number }}</p>
        <p><strong>Arrival:</strong> {{ $flight->arrival_date ? \Carbon\Carbon::parse($flight->arrival_date)->format('F d, Y') : 'N/A' }} at {{ $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') : 'N/A' }}</p>
        @if($flight->return_date)
        <p><strong>Return:</strong> {{ \Carbon\Carbon::parse($flight->return_date)->format('F d, Y') }} at {{ $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : 'N/A' }} - Flight {{ $flight->return_flight_number }}</p>
        @endif
    </div>

    <div style="background-color: #fff3cd; padding: 20px; border: 1px solid #ffc107; border-radius: 5px; margin-bottom: 20px;">
        <h2 style="color: #856404; margin-top: 0;">Your Login Credentials</h2>
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Password:</strong> <span style="font-family: monospace; background-color: #f8f9fa; padding: 5px 10px; border-radius: 3px;">{{ $password }}</span></p>
        <p style="color: #856404; margin-top: 15px;"><strong>⚠️ Important:</strong> Please save this password securely. You can change it after logging in.</p>
    </div>

    <div style="background-color: #d1ecf1; padding: 20px; border: 1px solid #bee5eb; border-radius: 5px; margin-bottom: 20px;">
        <p style="margin: 0;">You can access your account at: <a href="{{ url('/dashboard') }}" style="color: #00adf1;">{{ url('/dashboard') }}</a></p>
    </div>

    <div style="text-align: center; color: #666; font-size: 12px; margin-top: 30px;">
        <p>This email was sent automatically. Please do not reply to this email.</p>
    </div>
</body>
</html>

