@extends('layouts.auth')

@section('title', 'إنشاء حساب جديد')

@section('content')
<div class="register-form">
    <h2 class="text-center mb-4">إنشاء حساب جديد</h2>
    
    <form action="{{ route('register') }}" method="POST">
        @csrf
        
        <div class="mb-3">
            <label for="name" class="form-label">الاسم الكامل</label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                   id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>
        
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
        
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
            <input type="password" class="form-control" id="password_confirmation" 
                   name="password_confirmation" required>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">إنشاء الحساب</button>
    </form>
    
    <div class="text-center mt-3">
        <p>لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a></p>
    </div>
</div>
@endsection
