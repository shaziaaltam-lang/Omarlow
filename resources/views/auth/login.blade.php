@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
<div class="login-form">
    <h2 class="text-center mb-4">تسجيل الدخول</h2>
    
    <form action="{{ route('login') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                   id="email" name="email" value="{{ old('email') }}" required>
            @error('email')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-3">
            <label for="password" class="form-label">كلمة المرور</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                   id="password" name="password" required>
            @error('password')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">
                تذكرني
            </label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
    </form>
    
    <div class="text-center mt-3">
        <p>ليس لديك حساب؟ <a href="{{ route('register') }}">إنشاء حساب جديد</a></p>
    </div>
</div>
@endsection
