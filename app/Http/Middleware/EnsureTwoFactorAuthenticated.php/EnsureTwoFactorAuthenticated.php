<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. التحقق من أن المستخدم قام بتسجيل الدخول أولاً
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => __('auth.unauthenticated') ?: 'غير مصرح بالدخول. يرجى تسجيل الدخول أولاً.'
                ], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 2. التحقق مما إذا كان المستخدم قد فعل خاصية التحقق الثنائي (2FA)
        // يمكن تعديل الشرط بناءً على اسم الحقل في جدول المستخدمين (مثل two_factor_enabled أو وجود key في two_factor_secret)
        $hasTwoFactorEnabled = filter_var($user->two_factor_enabled ?? false, FILTER_VALIDATE_BOOLEAN) 
            || !empty($user->two_factor_secret);

        if ($hasTwoFactorEnabled) {
            // 3. التحقق مما إذا كان المستخدم قد أكمل بنجاح عملية التحقق الثنائي في الجلسة الحالية
            $isTwoFactorVerified = $request->session()->get('two_factor_verified', false);

            if (!$isTwoFactorVerified) {
                // استجابة مخصصة لواجهات برمجة التطبيقات APIs
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => __('auth.two_factor_required') ?: 'يجب إكمال عملية التحقق الثنائي للوصول إلى هذا المصدر.',
                        'two_factor_pending' => true
                    ], 403);
                }

                // التوجيه لصفحة تأكيد رمز التحقق الثنائي للمتصفحات
                return redirect()->route('two-factor.index')
                    ->with('warning', __('auth.two_factor_warning') ?: 'يرجى إدخال رمز التحقق الثنائي للمتابعة.');
            }
        }

        return $next($request);
    }
}