<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorAuthentication
{
    /**
     * معالجة الطلب الوارد والتحقق من حالة تفعيل وتأكيد ميزة التحقق بخطوتين.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // التحقق مما إذا كان المستخدم مسجلاً لديه ميزة التحقق بخطوتين مفعلة بالفعل
        if ($user && $this->userHasTwoFactorEnabled($user)) {
            
            // التحقق مما إذا كان المستخدم لم يتمم عملية التحقق للطلب الحالي أو الجلسة الحالية
            if (!$this->isTwoFactorVerified($request)) {
                
                // استثناء مسارات التحقق وتسجيل الخروج لتفادي حلقات التحويل اللانهائية
                if ($this->shouldPassThrough($request)) {
                    return $next($request);
                }

                // إذا كان الطلب يتوقع استجابة JSON (API)
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => __('auth.two_factor_required', 'Two-factor authentication code is required to access this resource.'),
                        'code' => 'TWO_FACTOR_REQUIRED',
                    ], Response::HTTP_FORBIDDEN);
                }

                // إذا كان الطلب عبر المتصفح (Web)، يتم توجيهه إلى صفحة إدخال الرمز
                return redirect()->route('two-factor.index')
                    ->with('warning', __('auth.two_factor_prompt', 'يرجى إكمال خطوة التحقق الثنائي للمتابعة.'));
            }
        }

        return $next($request);
    }

    /**
     * التحقق مما إذا كان المستخدم قد قام بتفعيل خاصية التحقق بخطوتين.
     *
     * @param  mixed  $user
     * @return bool
     */
    protected function userHasTwoFactorEnabled($user): bool
    {
        // التحقق في حال وجود دالة مخصصة بالنموذج أو فحص الحقول مباشرة
        if (method_exists($user, 'hasEnabledTwoFactor')) {
            return (bool) $user->hasEnabledTwoFactor();
        }

        return !empty($user->two_factor_secret) || (bool) ($user->two_factor_enabled ?? false);
    }

    /**
     * التحقق مما إذا كان المستخدم قد أثبت هويته بالرمز الثنائي في الجلسة أو الترويسة الحالية.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isTwoFactorVerified(Request $request): bool
    {
        // لطلبات الويب المعتمدة على الجلسات
        if ($request->hasSession()) {
            return (bool) $request->session()->get('two_factor_verified', false);
        }

        // لطلبات الـ API المعتمدة على ترويسة مخصصة أو فحص التوكن
        if ($request->headers->has('X-Two-Factor-Verified')) {
            return $request->header('X-Two-Factor-Verified') === 'true';
        }

        return false;
    }

    /**
     * تحديد ما إذا كان المسار الحالي مستثنى ويُسمح بالمرور عبره بدون إتمام التحقق بعد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough(Request $request): bool
    {
        $excepts = [
            'two-factor',               // مسار صفحة التحدي الرئيسي
            'two-factor/*',             // أي مسارات فرعية تابعة للتحقق الثنائي
            'api/v1/two-factor/*',      // مسارات الـ API الخاصة بالتحقق الثنائي
            'logout',                   // مسار تسجيل الخروج للويب
            'api/v1/logout',            // مسار تسجيل الخروج للـ API
        ];

        foreach ($excepts as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except)) {
                return true;
            }
        }

        // التحقق من خلال أسماء المسارات المسجلة لتوفير حماية مرنة إضافية
        $currentRouteName = $request->route() ? $request->route()->getName() : null;
        if (in_array($currentRouteName, ['two-factor.index', 'two-factor.verify', 'two-factor.resend', 'logout'])) {
            return true;
        }

        return false;
    }
}