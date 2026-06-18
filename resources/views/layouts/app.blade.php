<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Omarlow</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div id="app">
        @include('partials.header')
        
        <div class="container-fluid">
            <div class="row">
                @include('partials.sidebar')
                
                <main class="col-md-9 ms-sm-auto px-md-4">
                    @include('partials.breadcrumb')
                    @include('partials.alert')
                    @yield('content')
                </main>
            </div>
        </div>
        
        @include('partials.footer')
    </div>
    
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
