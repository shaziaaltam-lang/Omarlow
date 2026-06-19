<?php

namespace App\DTOs;

use App\Models\Client;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * ClientDTO
 *
 * كائن نقل البيانات (DTO) لتمثيل معلومات العميل بشكل ثابت وغير قابل للتعديل.
 * يضمن هذا الكائن تجانس البيانات عند نقلها بين طبقات التطبيق المختلفة.
 */
class ClientDTO
{
    /**
     * يُنشئ مثيلاً جديدًا من ClientDTO.
     *
     * @param string $name اسم العميل.
     * @param string $email البريد الإلكتروني للعميل.
     * @param string|null $phone رقم هاتف العميل (اختياري).
     * @param string|null $address عنوان العميل (اختياري).
     */
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone = null,
        public readonly ?string $address = null
    ) {
    }

    /**
     * ينشئ كائن ClientDTO من طلب HTTP (Request).
     *
     * تُطبق هذه الدالة تحققًا أساسيًا لأنواع البيانات لضمان صحة المدخلات.
     *
     * @param Request $request طلب HTTP الوارد.
     * @return static كائن ClientDTO جديد.
     * @throws InvalidArgumentException إذا كانت البيانات المطلوبة مفقودة أو غير صالحة.
     */
    public static function fromRequest(Request $request): static
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $address = $request->input('address');

        // التحقق الأساسي من أنواع البيانات للمتطلبات الإلزامية
        if (!is_string($name) || empty(trim($name))) {
            throw new InvalidArgumentException('اسم العميل مطلوب ويجب أن يكون نصاً غير فارغ.');
        }
        if (!is_string($email) || empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('البريد الإلكتروني للعميل مطلوب ويجب أن يكون بريداً إلكترونياً صالحاً وغير فارغ.');
        }
        // التحقق من أن الهاتف والعنوان إذا وُجدا، فإنهما نصوص
        if (isset($phone) && !is_string($phone)) {
            throw new InvalidArgumentException('رقم الهاتف يجب أن يكون نصاً.');
        }
        if (isset($address) && !is_string($address)) {
            throw new InvalidArgumentException('العنوان يجب أن يكون نصاً.');
        }

        return new static(
            name: $name,
            email: $email,
            phone: $phone,
            address: $address
        );
    }

    /**
     * ينشئ كائن ClientDTO من نموذج Eloquent (Client Model).
     *
     * تُطبق هذه الدالة تحققًا أساسيًا لأنواع البيانات لضمان صحة خصائص النموذج.
     *
     * @param Client $clientModel نموذج العميل من قاعدة البيانات.
     * @return static كائن ClientDTO جديد.
     * @throws InvalidArgumentException إذا كانت خصائص النموذج مفقودة أو غير صالحة.
     */
    public static function fromModel(Client $clientModel): static
    {
        // التحقق الأساسي من وجود الخصائص في النموذج وتطابق أنواعها
        if (!isset($clientModel->name) || !is_string($clientModel->name) || empty(trim($clientModel->name))) {
            throw new InvalidArgumentException('خاصية "name" مفقودة أو غير صالحة في نموذج العميل.');
        }
        if (!isset($clientModel->email) || !is_string($clientModel->email) || empty(trim($clientModel->email)) || !filter_var($clientModel->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('خاصية "email" مفقودة أو غير صالحة في نموذج العميل.');
        }

        return new static(
            name: $clientModel->name,
            email: $clientModel->email,
            phone: $clientModel->phone,
            address: $clientModel->address
        );
    }
}
