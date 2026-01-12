<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Unsubscribe') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0;">{{ __('Unsubscribe') }}</h1>
    </div>

    <div style="background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; border-top: none;">
        <div style="background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #059669;">
            <h2 style="color: #059669; margin-top: 0;">{{ $alreadyUnsubscribed ? __('Already Unsubscribed') : __('Successfully Unsubscribed') }}</h2>
            <p>{{ $message }}</p>
            @if(!$alreadyUnsubscribed)
                <p>{{ __('You will no longer receive newsletters from us.') }}</p>
            @endif
        </div>

        <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
            {{ __('Thank you') }},<br>
            <strong>{{ __('The') }} {{ config('app.name') }} {{ __('team') }}</strong>
        </p>
    </div>
</body>
</html>

