<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * إرجاع استجابة ناجحة بتنسيق JSON
     *
     * @param mixed $data البيانات المراد إرجاعها
     * @param string $message رسالة النجاح
     * @param int $code كود الحالة HTTP
     * @return JsonResponse
     */
    public function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * إرجاع استجابة خطأ بتنسيق JSON
     *
     * @param string $message رسالة الخطأ
     * @param int $code كود الحالة HTTP
     * @return JsonResponse
     */
    public function errorResponse(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null,
        ], $code);
    }

    /**
     * إرجاع استجابة خطأ عند فشل التحقق من البيانات
     *
     * @param mixed $errors أخطاء التحقق
     * @param string $message رسالة توضيحية
     * @return JsonResponse
     */
    public function validationErrorResponse($errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], 422);
    }
}