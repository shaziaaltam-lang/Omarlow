<?php

namespace App\Http\Responses;

/**
 * كلاس ApiResponseSchema
 * يوفر هيكلية موحدة وموحدة لاستجابات واجهة برمجة التطبيقات (API) الخاصة بالنظام.
 */
class ApiResponseSchema
{
    /**
     * إنشاء استجابة نجاح قياسية.
     *
     * @param mixed $data البيانات المراد إرجاعها.
     * @param string $message رسالة نصية توضيحية للعملية.
     * @param int $code كود الحالة الخاص بـ HTTP.
     * @return array
     */
    public static function successResponse(mixed $data = [], string $message = 'تمت العملية بنجاح', int $code = 200): array
    {
        return [
            'success' => true,
            'data'    => $data,
            'message' => $message,
            'errors'  => null,
            'code'    => $code
        ];
    }

    /**
     * إنشاء استجابة خطأ قياسية.
     *
     * @param string $message رسالة الخطأ للمستخدم.
     * @param array|null $errors قائمة تفصيلية بالأخطاء (مثل أخطاء التحقق).
     * @param int $code كود الحالة الخاص بـ HTTP.
     * @return array
     */
    public static function errorResponse(string $message = 'حدث خطأ ما', ?array $errors = null, int $code = 400): array
    {
        return [
            'success' => false,
            'data'    => [],
            'message' => $message,
            'errors'  => $errors,
            'code'    => $code
        ];
    }
}