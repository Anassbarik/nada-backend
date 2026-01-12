<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #00adf1; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">{{ config('app.name') }}</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
        <h2 style="color: #00adf1; margin-top: 0;">{{ $subject }}</h2>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            {!! nl2br(e($content)) !!}
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            {{ __('Best regards') }},<br>
            <strong>{{ __('The') }} {{ config('app.name') }} {{ __('team') }}</strong>
        </p>
    </div>

    <div style="background: #f3f4f6; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;">
        <p style="margin: 0;">{{ __('You are receiving this email because you subscribed to our newsletter.') }}</p>
        <p style="margin: 5px 0;">
            <a href="{{ url('/api/newsletter/unsubscribe?email=' . urlencode($email ?? '')) }}" style="color: #00adf1; text-decoration: none;">
                {{ __('Unsubscribe') }}
            </a>
        </p>
    </div>
</body>
</html>

