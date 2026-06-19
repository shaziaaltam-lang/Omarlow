<?php

namespace App\Jobs;

use App\Services\DocumentService;
use App\Services\OcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Illuminate\Support\Facades\Log;

class ProcessDocumentForOCR implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * عدد مرات إعادة محاولة المهمة.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * مدة الانتظار قبل إعادة المحاولة (بالثواني).
     *
     * @var int
     */
    public $backoff = 5;

    /**
     * معرف المستند المراد معالجته بواسطة OCR.
     *
     * @var int
     */
    public readonly int $documentId;

    /**
     * إنشاء مثيل جديد للمهمة.
     *
     * @param int $documentId معرف المستند
     */
    public function __construct(int $documentId)
    {
        $this->documentId = $documentId;
    }

    /**
     * تنفيذ المهمة.
     *
     * @param OcrService $ocrService خدمة معالجة OCR
     * @param DocumentService $documentService خدمة إدارة المستندات
     * @return void
     * @throws Throwable في حالة فشل المعالجة بعد جميع المحاولات
     */
    public function handle(OcrService $ocrService, DocumentService $documentService): void
    {
        // 1. استرداد المستند باستخدام معرفه من خلال DocumentService
        $document = $documentService->findDocumentById($this->documentId);

        if (!$document) {
            Log::error("المستند بالمعرف {$this->documentId} لم يتم العثور عليه لمعالجة OCR.");
            // يمكن هنا رمي استثناء أو إنهاء المهمة بشكل صامت
            return;
        }

        try {
            // 2. استدعاء OcrService لمعالجة المستند
            // نفترض أن OcrService->processDocument يعيد المحتوى النصي المعالج أو بيانات أخرى
            $ocrContent = $ocrService->processDocument($document);

            // 3. تحديث حالة المستند ومحتواه المعالج عبر DocumentService
            // نفترض وجود دالة مثل updateDocumentOcrResult لتحديث المستند
            $documentService->updateDocumentOcrResult(
                $this->documentId,
                $ocrContent,
                'معالج بنجاح'
            );

            Log::info("تمت معالجة المستند بالمعرف {$this->documentId} بواسطة OCR بنجاح.");

        } catch (Throwable $e) {
            // تسجيل الخطأ وتحديث حالة المستند كفشل
            Log::error("فشل معالجة المستند بالمعرف {$this->documentId} لـ OCR: " . $e->getMessage());
            $documentService->updateDocumentOcrResult(
                $this->documentId,
                null,
                'فشل المعالجة',
                $e->getMessage()
            );
            // إعادة رمي الاستثناء للسماح لـ Laravel بإعادة محاولة المهمة أو إرسالها إلى قائمة الانتظار الفاشلة
            throw $e;
        }
    }
}
