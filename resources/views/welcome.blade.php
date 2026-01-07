<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0; url={{ route('login') }}">
    <title>Redirecting...</title>
    </head>
<body>
    <script>
        window.location.href = "{{ route('login') }}";
    </script>
    <p>Redirecting to <a href="{{ route('login') }}">login page</a>...</p>
    </body>
</html>


