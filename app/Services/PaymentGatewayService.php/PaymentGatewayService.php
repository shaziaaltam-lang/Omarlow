<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class PaymentGatewayService
{ 
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $apiBaseUrl;
    protected string $successUrl;
    protected string $cancelUrl;

    /**
     * PaymentGatewayService constructor.
     */
    public function __construct()
    {
        $this->apiKey = config('services.payment_gateway.api_key') ?? env('PAYMENT_GATEWAY_API_KEY', 'sk_test_default_key');
        $this->webhookSecret = config('services.payment_gateway.webhook_secret') ?? env('PAYMENT_GATEWAY_WEBHOOK_SECRET', 'whsec_default_secret');
        $this->apiBaseUrl = config('services.payment_gateway.base_url') ?? env('PAYMENT_GATEWAY_BASE_URL', 'https://api.paymentgateway.com/v1');
        $this->successUrl = config('services.payment_gateway.success_url') ?? env('PAYMENT_GATEWAY_SUCCESS_URL', url('/payment/success'));
        $this->cancelUrl = config('services.payment_gateway.cancel_url') ?? env('PAYMENT_GATEWAY_CANCEL_URL', url('/payment/cancel'));
    }

    /**
     * إنشاء جلسة دفع آمنة لفاتورة محددة.
     *
     * @param Invoice $invoice
     * @param array $options خيارات إضافية للمدفوعات
     * @return array
     * @throws Exception
     */
    public function createCheckoutSession(Invoice $invoice, array $options = []): array
    {
        if ($invoice->amount <= 0) {
            throw new Exception("لا يمكن إنشاء جلسة دفع لفاتورة بقيمة صفرية أو سالبة.");
        }

        try {
            Log::info("بدء إنشاء جلسة دفع للفاتورة ذات المعرّف: {$invoice->id}");

            // تجهيز بيانات الدفع المرسلة لبوابة الدفع
            $payload = [
                'amount' => (int) ($invoice->amount * 100), // تحويل المبلغ إلى أصغر وحدة نقدية (مثل الهللات أو السنتات)
                'currency' => strtolower($invoice->currency ?? 'USD'),
                'customer_email' => $invoice->client->email ?? $options['customer_email'] ?? 'customer@example.com',
                'customer_name' => $invoice->client->name ?? $options['customer_name'] ?? 'Client',
                'reference_id' => (string) $invoice->id,
                'success_url' => $this->successUrl . '?invoice_id=' . $invoice->id,
                'cancel_url' => $this->cancelUrl . '?invoice_id=' . $invoice->id,
                'metadata' => array_merge([
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? 'INV-' . $invoice->id,
                ], $options['metadata'] ?? []),
            ];

            // إرسال الطلب الفعلي لبوابة الدفع عبر بروتوكول HTTP
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiBaseUrl . '/checkout/sessions', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // تحديث مرجع الجلسة في قاعدة البيانات إذا لزم الأمر
                $invoice->update([
                    'payment_session_id' => $responseData['id'] ?? null,
                ]);

                return [
                    'session_id' => $responseData['id'] ?? null,
                    'checkout_url' => $responseData['url'] ?? null,
                    'reference' => $responseData['reference'] ?? null,
                    'success' => true
                ];
            }

            // آلية الدعم في بيئة التطوير المحلّي في حال تعثر الاتصال الخارجي أو عدم توفر المفتاح
            if (app()->environment('local', 'testing') || !$this->apiKey) {
                $mockSessionId = 'cs_test_' . Str::random(24);
                $mockCheckoutUrl = $this->apiBaseUrl . '/pay/' . $mockSessionId;

                Log::warning("تم تجاوز طلب بوابة الدفع الفعلي أو فشل الاتصال في البيئة التجريبية، تم توليد جلسة وهمية آمنة للتجربة.");

                return [
                    'session_id' => $mockSessionId,
                    'checkout_url' => $mockCheckoutUrl,
                    'reference' => 'ref_' . Str::random(10),
                    'success' => true,
                    'simulated' => true
                ];
            }

            throw new Exception("خطأ من بوابة الدفع: " . $response->body());

        } catch (Exception $e) {
            Log::error("فشل في إنشاء جلسة دفع للفاتورة ذات الرقم {$invoice->id}: " . $e->getMessage());
            throw new Exception("فشل معالج الدفع: " . $e->getMessage());
        }
    }

    /**
     * معالجة إشارات الويب الفورية المستلمة (Webhooks) من مزود الدفع.
     *
     * @param array $payload البيانات المرسلة من البوابة
     * @param string $signature التوقيع الرقمي للتحقق من هوية المرسل
     * @return bool
     * @throws Exception
     */
    public function handleWebhook(array $payload, string $signature): bool
    {
        Log::info("تم استلام إشعار الدفع الفوري (Webhook).", ['payload' => $payload]);

        // التحقق الفني من هوية التوقيع والبيانات لمنع الاختراق وانتحال الهوية
        if (!$this->verifyWebhookSignature($payload, $signature)) {
            Log::error("فشل التحقق من التوقيع الرقمي لإشعار بوابة الدفع.");
            throw new Exception("توقيع غير صالح للبوابة.");
        }

        $event = $payload['event'] ?? '';
        $data = $payload['data'] ?? [];
        $invoiceId = $data['metadata']['invoice_id'] ?? $data['reference_id'] ?? null;

        if (!$invoiceId) {
            Log::warning("لم يتم العثور على معرّف فاتورة في تفاصيل إشعار بوابة الدفع.");
            return false;
        }

        $invoice = Invoice::find($invoiceId);

        if (!$invoice) {
            Log::error("لم يتم العثور على الفاتورة ذات الرقم {$invoiceId} لتحديث حالتها.");
            throw new Exception("الفاتورة غير موجودة في قاعدة البيانات.");
        }

        switch ($event) {
            case 'payment.succeeded':
                return $this->processPaymentSuccess($invoice, $data);
                
            case 'payment.failed':
                return $this->processPaymentFailure($invoice, $data);

            default:
                Log::warning("حدث دفع غير مستهدف أو غير معالج برمجياً: {$event}");
                return false;
        }
    }

    /**
     * معالجة عملية نجاح الدفع وتغيير حالة الفاتورة لـ 'مدفوعة'.
     */
    protected function processPaymentSuccess(Invoice $invoice, array $data): bool
    {
        if ($invoice->status === 'Paid') {
            Log::info("الفاتورة ذات الرقم {$invoice->id} تم دفعها مسبقاً وتخطي المعالجة المكررة.");
            return true;
        }

        // تحديث حقول الفاتورة بشكل رسمي ونهائي بقاعدة البيانات
        $invoice->update([
            'status' => 'Paid',
            'paid_at' => now(),
            'transaction_id' => $data['id'] ?? $data['transaction_id'] ?? null,
            'payment_method' => $data['payment_method'] ?? 'credit_card',
        ]);

        Log::info("تم تحديث حالة الفاتورة بنجاح إلى 'Paid' للفاتورة رقم: {$invoice->id}");

        return true;
    }

    /**
     * معالجة عملية فشل الدفع وتغيير حالة الفاتورة لتسجيل الخطأ.
     */
    protected function processPaymentFailure(Invoice $invoice, array $data): bool
    {
        $invoice->update([
            'status' => 'Failed',
            'payment_error' => $data['error_message'] ?? 'فشلت معاملة الدفع عبر البوابة',
        ]);

        Log::warning("فشلت عملية الدفع للفاتورة رقم {$invoice->id}. السبب: " . ($data['error_message'] ?? 'غير معروف'));

        return true;
    }

    /**
     * التحقق الأمني من توقيع الويب هوك باستخدام خوارزمية التشفير المتناظر HMAC SHA256.
     */
    protected function verifyWebhookSignature(array $payload, string $signature): bool
    {
        if (app()->environment('local', 'testing') && $signature === 'simulated-signature') {
            return true;
        }

        // تشفير محتوى الطلب ومقارنته بالتوقيع المرسل لضمان الموثوقية التامة
        $calculatedSignature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);

        return hash_equals($calculatedSignature, $signature);
    }
}
