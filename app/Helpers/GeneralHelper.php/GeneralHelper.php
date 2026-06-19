<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

if (!function_exists('formatDateForDisplay')) {
    /**
     * تنسيق التاريخ للعرض بطريقة مقروءة.
     *
     * هذه الدالة تستقبل تاريخاً (سلسلة نصية) وتقوم بتنسيقه إلى صيغة محددة للعرض.
     * يمكن استخدامها لتوحيد عرض التواريخ في واجهة المستخدم.
     *
     * @param string $date التاريخ المراد تنسيقه (مثال: '2023-10-26 14:30:00').
     * @param string $format صيغة التنسيق المطلوبة (مثال: 'Y-m-d H:i:s', 'd/m/Y').
     * @return string|null التاريخ المنسق أو null إذا كان التاريخ المدخل غير صالح.
     */
    function formatDateForDisplay(string $date, string $format = 'Y-m-d')
    {
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            // في حال وجود خطأ في تحليل التاريخ، يمكن تسجيله هنا أو إرجاع قيمة افتراضية.
            return null;
        }
    }
}

if (!function_exists('generateUniqueCode')) {
    /**
     * توليد كود فريد عشوائي.
     *
     * تقوم هذه الدالة بإنشاء سلسلة نصية عشوائية وفريدة من الأحرف الأبجدية الرقمية.
     * يمكن استخدامها لتوليد رموز تتبع، مفاتيح API، أو أي معرفات فريدة.
     *
     * @param int $length طول الكود المطلوب.
     * @return string الكود العشوائي الفريد.
     */
    function generateUniqueCode(int $length = 10): string
    {
        // استخدام Str::random من Laravel لتوليد سلسلة عشوائية آمنة وفعالة
        return Str::random($length);
    }
}

if (!function_exists('isJson')) {
    /**
     * التحقق مما إذا كانت السلسلة النصية هي JSON صالح.
     *
     * تتحقق هذه الدالة مما إذا كانت سلسلة نصية معينة تمثل JSON صالحًا.
     * مفيدة للتحقق من البيانات قبل محاولة فك تشفيرها.
     *
     * @param string $string السلسلة النصية المراد التحقق منها.
     * @return bool True إذا كانت السلسلة JSON صالحًا، وإلا False.
     */
    function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
