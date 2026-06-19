<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreCaseRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مخولاً لإجراء هذا الطلب.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // السماح بالطلب في حال كان المستخدم مسجلاً لدخول النظام
        return auth()->check() || auth('sanctum')->check();
    }

    /**
     * قواعد التحقق الصارمة التي تطبق على الطلب.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|min:5|max:255',
            'client_id' => 'required|integer|exists:clients,id',
            'case_type_id' => 'required|integer|exists:case_types,id',
            'case_status_id' => 'required|integer|exists:case_statuses,id',
            'registration_date' => 'required|date|date_format:Y-m-d',
            'claims_amount' => 'nullable|numeric|min:0',
            'court_name' => 'nullable|string|max:255',
            'description' => 'nullable|string|min:10',
        ];
    }

    /**
     * رسائل الخطأ المخصصة والمعربة بالكامل لعرضها للمستخدم.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'عنوان القضية حقل إجباري ولا يمكن تركه فارغاً.',
            'title.string' => 'يجب أن يكون عنوان القضية نصاً صالحاً.',
            'title.min' => 'يجب ألا يقل عنوان القضية عن :min أحرف لضمان وضوحه.',
            'title.max' => 'يجب ألا يتجاوز عنوان القضية :max حرفاً.',
            
            'client_id.required' => 'يجب تحديد العميل المرتبط بهذه القضية.',
            'client_id.integer' => 'معرف العميل يجب أن يكون رقماً صحيحاً.',
            'client_id.exists' => 'العميل المحدد غير موجود بسجلاتنا أو تم حذفه.',
            
            'case_type_id.required' => 'نوع القضية حقل إجباري لتصنيف المعاملة.',
            'case_type_id.integer' => 'معرف نوع القضية يجب أن يكون رقماً صحيحاً.',
            'case_type_id.exists' => 'نوع القضية المحدد غير مدرج في قائمة التصنيفات المدعومة.',
            
            'case_status_id.required' => 'حالة القضية حقل إجباري لتتبع سير العمل.',
            'case_status_id.integer' => 'معرف الحالة يجب أن يكون رقماً صحيحاً.',
            'case_status_id.exists' => 'حالة القضية المحددة غير صالحة أو غير معرفة.',
            
            'registration_date.required' => 'تاريخ تسجيل القضية مطلوب ومهم لجدولة الجلسات.',
            'registration_date.date' => 'صيغة تاريخ التسجيل غير صحيحة.',
            'registration_date.date_format' => 'يجب إدخال التاريخ بالصيغة القياسية YYYY-MM-DD.',
            
            'claims_amount.numeric' => 'قيمة المطالبات المالية يجب أن تكون قيمة رقمية.',
            'claims_amount.min' => 'قيمة المطالبات المالية لا يمكن أن تكون أقل من صفر.',
            
            'court_name.string' => 'اسم المحكمة المختصة يجب أن يكون نصاً صالحاً.',
            'court_name.max' => 'يجب ألا يتجاوز اسم المحكمة :max حرفاً.',
            
            'description.string' => 'شرح وتفاصيل القضية يجب أن يكون نصاً مكتوباً.',
            'description.min' => 'يجب أن يحتوي الوصف على :min أحرف على الأقل لشرح خلفية القضية.',
        ];
    }

    /**
     * أسماء الحقول المعربة والمستخدمة في رسائل التحقق التلقائية.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'عنوان القضية',
            'client_id' => 'العميل المرتبط',
            'case_type_id' => 'نوع القضية',
            'case_status_id' => 'حالة القضية',
            'registration_date' => 'تاريخ التسجيل',
            'claims_amount' => 'قيمة المطالبات المالية',
            'court_name' => 'المحكمة المختصة',
            'description' => 'شرح وتفاصيل القضية',
        ];
    }

    /**
     * التعامل مع فشل التحقق وإرسال استجابة JSON موحدة ومناسبة لواجهات الـ API.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'لقد حدث خطأ في البيانات المرسلة، يرجى مراجعة الأخطاء المرفقة.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}