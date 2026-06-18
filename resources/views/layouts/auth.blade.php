<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Omarlow</title>
    <link rel="stylesheet" href="{{ asset('assets/css/auth.css') }}">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Omarlow" class="logo">
            </div>
            
            @yield('content')
        </div>
    </div>
    
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>
