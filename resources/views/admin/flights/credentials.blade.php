<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flight Credentials - {{ $flight->reference }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #1a1a1a;
            font-size: 24px;
        }
        .content {
            margin: 30px 0;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f9f9f9;
            border-left: 4px solid #4a5568;
        }
        .section h2 {
            margin-top: 0;
            color: #2d3748;
            font-size: 18px;
        }
        .info-row {
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
            color: #4a5568;
        }
        .info-value {
            color: #1a202c;
        }
        .credentials-box {
            background-color: #fff;
            border: 2px solid #4a5568;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #2d3748;
        }
        .credential-item {
            margin: 15px 0;
            padding: 10px;
            background-color: #f7fafc;
            border-left: 3px solid #4299e1;
        }
        .credential-label {
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
        }
        .credential-value {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #1a202c;
            background-color: #edf2f7;
            padding: 8px;
            border-radius: 3px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 12px;
        }
        .warning {
            background-color: #fff5f5;
            border-left: 4px solid #fc8181;
            padding: 15px;
            margin: 20px 0;
        }
        .warning p {
            margin: 0;
            color: #c53030;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Flight Booking Credentials</h1>
        <p>Flight Reference: {{ $flight->reference }}</p>
        @if(isset($booking) && $booking->booking_reference)
            <p>Booking Reference: {{ $booking->booking_reference }}</p>
        @endif
    </div>

    <div class="content">
        @if(isset($booking) && $booking->booking_reference)
        <div class="section">
            <h2>Booking Information</h2>
            <div class="info-row">
                <span class="info-label">Booking Reference:</span>
                <span class="info-value"><strong>{{ $booking->booking_reference }}</strong></span>
            </div>
            <div class="info-row">
                <span class="info-label">Booking Status:</span>
                <span class="info-value">{{ ucfirst($booking->status) }}</span>
            </div>
        </div>
        @endif

        <div class="section">
            <h2>Flight Information</h2>
            <div class="info-row">
                <span class="info-label">Flight Reference:</span>
                <span class="info-value">{{ $flight->reference }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Client Name:</span>
                <span class="info-value">{{ $flight->full_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Flight Class:</span>
                <span class="info-value">{{ $flight->flight_class_label }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Departure:</span>
                <span class="info-value">{{ $flight->departure_date ? \Carbon\Carbon::parse($flight->departure_date)->format('F d, Y') : 'N/A' }} at {{ $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('H:i') : 'N/A' }} - Flight {{ $flight->departure_flight_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Arrival:</span>
                <span class="info-value">{{ $flight->arrival_date ? \Carbon\Carbon::parse($flight->arrival_date)->format('F d, Y') : 'N/A' }} at {{ $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('H:i') : 'N/A' }}</span>
            </div>
            @if($flight->return_date)
            <div class="info-row">
                <span class="info-label">Return Departure:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($flight->return_date)->format('F d, Y') }} at {{ $flight->return_departure_time ? \Carbon\Carbon::parse($flight->return_departure_time)->format('H:i') : 'N/A' }} - Flight {{ $flight->return_flight_number }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Return Arrival:</span>
                <span class="info-value">{{ $flight->return_arrival_date ? \Carbon\Carbon::parse($flight->return_arrival_date)->format('F d, Y') : 'N/A' }} at {{ $flight->return_arrival_time ? \Carbon\Carbon::parse($flight->return_arrival_time)->format('H:i') : 'N/A' }}</span>
            </div>
            @endif
        </div>

        <div class="section">
            <h2>Client Information</h2>
            <div class="info-row">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
        </div>

        <div class="credentials-box">
            <h3>Login Credentials</h3>
            <div class="credential-item">
                <div class="credential-label">Email Address:</div>
                <div class="credential-value">{{ $user->email }}</div>
            </div>
            <div class="credential-item">
                <div class="credential-label">Password:</div>
                <div class="credential-value">{{ $password }}</div>
            </div>
        </div>

        <div class="warning">
            <p><strong>⚠️ Important:</strong> Please save this document securely. The password cannot be recovered if lost. You can change your password after logging in.</p>
            @if(isset($booking) && $booking->booking_reference)
            <p><strong>📋 Booking Reference:</strong> Keep your booking reference (<strong>{{ $booking->booking_reference }}</strong>) handy. You will need it if you want to book a hotel package and link it to this flight booking.</p>
            @endif
        </div>
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('F d, Y \a\t H:i') }}</p>
        <p>This document contains sensitive information. Please keep it secure.</p>
    </div>
</body>
</html>

