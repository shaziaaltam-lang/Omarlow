<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Omarlow - نظام إدارة مكتب محاماة</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .welcome-container {
            text-align: center;
            color: white;
        }
        .welcome-container h1 {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .welcome-container p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="welcome-container">
        <h1>مرحباً بك في Omarlow</h1>
        <p>نظام إدارة شامل لمكاتب المحاماة</p>
        <div class="gap-2 d-flex justify-content-center">
            @if(auth()->check())
            <a href="{{ route('dashboard') }}" class="btn btn-light btn-lg">لوحة التحكم</a>
            @else
            <a href="{{ route('login') }}" class="btn btn-light btn-lg">تسجيل الدخول</a>
            <a href="{{ route('register') }}" class="btn btn-outline-light btn-lg">إنشاء حساب جديد</a>
            @endif
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
