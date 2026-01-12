<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('New Newsletter Subscription') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #00adf1; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">{{ __('New Newsletter Subscription') }}</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
        <p>{{ __('A new user has subscribed to your newsletter.') }}</p>

        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #00adf1;">
            <h3 style="margin-top: 0; color: #00adf1;">{{ __('Subscriber Details') }}</h3>
            <p style="margin: 5px 0;"><strong>{{ __('Email') }}:</strong> {{ $subscriber->email }}</p>
            @if($subscriber->name)
                <p style="margin: 5px 0;"><strong>{{ __('Name') }}:</strong> {{ $subscriber->name }}</p>
            @endif
            <p style="margin: 5px 0;"><strong>{{ __('Subscribed At') }}:</strong> {{ $subscriber->subscribed_at->format('Y-m-d H:i:s') }}</p>
            <p style="margin: 5px 0;"><strong>{{ __('Source') }}:</strong> {{ ucfirst($subscriber->source ?? 'Unknown') }}</p>
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            {{ __('You can manage newsletter subscribers in the admin panel.') }}
        </p>
    </div>

    <div style="background: #f3f4f6; padding: 15px; text-align: center; border-radius: 0 0 8px 8px; font-size: 12px; color: #6b7280;">
        <p style="margin: 0;">{{ __('This is an automated notification email.') }}</p>
    </div>
</body>
</html>

