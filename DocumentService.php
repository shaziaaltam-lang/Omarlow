<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\LegalCase;
use App\Models\Client;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\UploadedFile;
use Illuminate\Support
Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class DocumentService
 * 
 * خدمة متطورة لإدارة مستندات القضايا والعملاء، تدعم التخزين السحابي الآمن،
 * تتبع الإصدارات، واستخراج النصوص تلقائياً عبر محاكاة تقنية الـ OCR.
 */
class DocumentService
{
    use ApiResponseTrait;

    protected string $storageDisk = 'local'; // يمكن تغييرها إلى s3 أو public
    protected string $baseFolder = 'legal_documents';

    /**
     * رفع مستند جديد وتخزينه مع ربطه بالقضية والعميل وإنشاء أول إصدار له.
     *
     * @param UploadedFile $file
     * @param array $data تحتوي على client_id, legal_case_id, title, description
     * @return Document
     * @throws Exception
     */
    public function uploadDocument(UploadedFile $file, array $data): Document
    {
        // التحقق من وجود العميل والقضية قبل المتابعة
        $client = Client::findOrFail($data['client_id']);
        $legalCase = LegalCase::findOrFail($data['legal_case_id']);

        return DB::transaction(function () use ($file, $data, $client, $legalCase) {
            // 1. تحديد مسار التخزين المخصص للعميل والقضية لضمان التنظيم والأمان
            $folderPath = "{$this->baseFolder}/client_{$client->id}/case_{$legalCase->id}";
            $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($folderPath, $fileName, $this->storageDisk);

            if (!$storedPath) {
                throw new Exception('فشل في حفظ الملف على وسيط التخزين.');
            }

            // 2. محاكاة معالجة الـ OCR لاستخراج النصوص القانونية تلقائياً
            $extractedText = $this->simulateOCR($file, $data['title'] ?? '');

            // 3. إنشاء سجل المستند الأساسي
            $document = Document::create([
                'client_id' => $client->id,
                'legal_case_id' => $legalCase->id,
                'title' => $data['title'] ?? $file->getClientOriginalName(),
                'description' => $data['description'] ?? null,
                'current_version' => 1,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'latest_ocr_text' => $extractedText,
            ]);

            // 4. تسجيل الإصدار الأول للمستند للتمكين من استرجاعه لاحقاً
            $document->versions()->create([
                'version_number' => 1,
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'ocr_text' => $extractedText,
                'file_size' => $file->getSize(),
                'uploaded_by' => auth()->id() ?? 1, // المعرف الافتراضي للمستخدم أو الموثق
            ]);

            return $document->load(['client', 'legalCase', 'versions']);
        });
    }

    /**
     * ترقية المستند وإصدار نسخة جديدة منه مع الاحتفاظ بالنسخ السابقة.
     *
     * @param Document $document
     * @param UploadedFile $file
     * @param string|null $changeDescription وصف للتعديلات في الإصدار الجديد
     * @return Document
     * @throws Exception
     */
    public function createNewVersion(Document $document, UploadedFile $file, ?string $changeDescription = null): Document
    {
        return DB::transaction(function () use ($document, $file, $changeDescription) {
            $nextVersionNumber = $document->current_version + 1;

            // تخزين الملف الجديد في مساره المخصص
            $folderPath = "{$this->baseFolder}/client_{$document->client_id}/case_{$document->legal_case_id}";
            $fileName = Str::uuid() . '_v' . $nextVersionNumber . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($folderPath, $fileName, $this->storageDisk);

            if (!$storedPath) {
                throw new Exception('فشل حفظ ملف الإصدار الجديد على وسيط التخزين.');
            }

            // معالجة الـ OCR للإصدار الجديد واستخراج النصوص الجديدة
            $extractedText = $this->simulateOCR($file, $document->title . " (V{$nextVersionNumber})");

            // تحديث السجل الرئيسي للمستند ليعبر عن الإصدار الحالي
            $document->update([
                'current_version' => $nextVersionNumber,
                'mime_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'latest_ocr_text' => $extractedText,
            ]);

            // إنشاء سجل الإصدار الجديد بربطه بالمستند الرئيسي
            $document->versions()->create([
                'version_number' => $nextVersionNumber,
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'ocr_text' => $extractedText,
                'file_size' => $file->getSize(),
                'change_description' => $changeDescription ?? "تحديث المستند للإصدار {$nextVersionNumber}",
                'uploaded_by' => auth()->id() ?? 1,
            ]);

            return $document->load(['versions', 'client', 'legalCase']);
        });
    }

    /**
     * دالة ذكية لمحاكاة عملية قراءة النصوص الضوئية OCR وتوليد مخرجات نصوص قانونية مهيكلة.
     *
     * @param UploadedFile $file
     * @param string $documentTitle
     * @return string
     */
    private function simulateOCR(UploadedFile $file, string $documentTitle): string
    {
        $currentTime = now()->toDayDateTimeString();
        $mimeType = $file->getClientMimeType();
        $originalName = $file->getClientOriginalName();

        return "=== تقرير نظام التعرف الضوئي (OCR) الأوتوماتيكي ===\n" .
               "تاريخ المعالجة: {$currentTime}\n" .
               "عنوان المستند المُحلل: {$documentTitle}\n" .
               "اسم الملف الأصلي: {$originalName}\n" .
               "نوع الملف: {$mimeType}\n" .
               "------------------------------------------------\n" .
               "[نص مستخرج تلقائياً]: بناءً على المستند المرفق ومراجعة الأوراق الثبوتية وقرارات المحكمة، " .
               "يقرر الطرفان الالتزام بكافة البنود القانونية الواردة فيه. يتضمن هذا المستند تفاصيل التعاقد، " .
               "والتزامات الأطراف المالية والإدارية المعمول بها قانوناً وصلاحيات التنفيذ القضائي المنصوص عليها." . 
               "\n=== نهاية النص المستخرج ===";
    }

    /**
     * جلب مستند معين مع قضاياه والعميل وتفاصيل إصداراته بالكامل.
     *
     * @param int $id
     * @return Document
     * @throws ModelNotFoundException
     */
    public function getDocumentDetails(int $id): Document
    {
        return Document::with(['client', 'legalCase', 'versions' => function ($query) {
            $query->orderBy('version_number', 'desc');
        }])->findOrFail($id);
    } 

    /**
     * حذف مستند نهائياً مع كافة إصداراته وملفاته المادية المخزنة بأمان.
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function deleteDocument(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $document = Document::with('versions')->findOrFail($id);

            // حذف كافة الملفات المادية المخزنة على الـ Storage لكل إصدار
            foreach ($document->versions as $version) {
                if (Storage::disk($this->storageDisk)->exists($version->file_path)) {
                    Storage::disk($this->storageDisk)->delete($version->file_path);
                }
            }

            // حذف السجلات من قاعدة البيانات (بسبب حماية العلاقات الأجنبية يتم الحذف التسلسلي)
            $document->versions()->delete();
            return $document->delete();
        });
    }
}