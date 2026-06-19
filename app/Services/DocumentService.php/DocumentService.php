<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use InvalidArgumentException;
use Exception;

/**
 * Class DocumentService
 * 
 * خدمة متقدمة لإدارة المستندات القانونية، التحكم في الإصدارات، والرفع الآمن.
 */
class DocumentService
{
    /**
     * القرص الافتراضي لتخزين المستندات الحساسة.
     */
    protected string $storageDisk = 'local';

    /**
     * المجلد الافتراضي لتخزين المستندات القانونية.
     */
    protected string $storageFolder = 'legal_documents';

    /**
     * قائمة الامتدادات المسموح بها للمستندات القانونية لضمان الأمان.
     */
    protected array $allowedExtensions = [
        'pdf', 'docx', 'doc', 'rtf', 'odt', 'xls', 'xlsx', 'txt', 'png', 'jpg', 'jpeg'
    ];

    /**
     * رفع مستند قانوني جديد وحفظ إصدار أول له.
     *
     * @param UploadedFile $file الملف المرفوع
     * @param array $data بيانات إضافية (مثل عنوان المستند، رقم القضية، المعرفات)
     * @param mixed $user المستخدم الذي يقوم بالعملية
     * @return array معلومات المستند والإصدار الذي تم إنشاؤه
     * @throws InvalidArgumentException|Exception
     */
    public function uploadDocument(UploadedFile $file, array $data, $user): array
    {
        $this->validateFile($file);

        Log::info('بدء عملية رفع مستند قانوني جديد.', [
            'user_id' => $user->id ?? 'system',
            'original_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize()
        ]);

        return DB::transaction(function () use ($file, $data, $user) {
            // 1. توليد مسار تخزين فريد وآمن
            $fileName = uniqid('doc_', true) . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($this->storageFolder, $fileName, $this->storageDisk);

            if (!$storedPath) {
                throw new Exception('فشل حفظ الملف على القرص التخزيني.');
            }

            // 2. محاكاة إنشاء سجل المستند الرئيسي (Document Record)
            // في حال وجود نموذج Eloquent يمكن كتابة: Document::create(...)
            $documentData = [
                'title' => $data['title'] ?? $file->getClientOriginalName(),
                'case_id' => $data['case_id'] ?? null,
                'client_id' => $data['client_id'] ?? null,
                'user_id' => $user->id ?? null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // 3. إنشاء سجل الإصدار الأول (V1.0.0)
            $versionData = [
                'version_number' => '1.0.0',
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'change_description' => 'الإصدار الابتدائي للمستند',
                'uploaded_by' => $user->id ?? null,
                'created_at' => now(),
            ];

            Log::info('تم رفع المستند وتسجيل الإصدار الأول بنجاح.', [
                'document' => $documentData,
                'version' => $versionData
            ]);

            return [
                'success' => true,
                'document' => $documentData,
                'version' => $versionData,
                'stored_path' => $storedPath
            ];
        });
    }

    /**
     * إضافة إصدار جديد لمستند موجود مسبقاً (Version Control).
     *
     * @param array $existingDocument بيانات المستند الحالي
     * @param UploadedFile $file الملف الجديد لترقية الإصدار
     * @param mixed $user المستخدم القائم بالتحديث
     * @param string $changeDescription وصف التغييرات الحاصلة
     * @return array تفاصيل الإصدار الجديد المضاف
     * @throws AuthorizationException|InvalidArgumentException|Exception
     */
    public function createNewVersion(array $existingDocument, UploadedFile $file, $user, string $changeDescription = ''): array
    {
        // التحقق من صلاحية المستخدم للتعديل على المستند
        $this->authorizeUser($existingDocument, $user, 'update');
        $this->validateFile($file);

        Log::info('تحديث مستند وإنشاء إصدار جديد.', [
            'document_id' => $existingDocument['id'] ?? 'unknown',
            'user_id' => $user->id ?? 'system'
        ]);

        return DB::transaction(function () use ($existingDocument, $file, $user, $changeDescription) {
            // تحديد رقم الإصدار القادم بشكل تلقائي
            $currentVersionStr = $existingDocument['current_version'] ?? '1.0.0';
            $nextVersionStr = $this->incrementVersionString($currentVersionStr);

            $fileName = uniqid('doc_v_' . $nextVersionStr . '_', true) . '.' . $file->getClientOriginalExtension();
            $storedPath = $file->storeAs($this->storageFolder, $fileName, $this->storageDisk);

            if (!$storedPath) {
                throw new Exception('فشل في تخزين ملف الإصدار الجديد.');
            }

            $newVersion = [
                'document_id' => $existingDocument['id'] ?? null,
                'version_number' => $nextVersionStr,
                'file_path' => $storedPath,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'change_description' => $changeDescription ?: 'تحديث محتوى المستند القانوني',
                'uploaded_by' => $user->id ?? null,
                'created_at' => now()
            ];

            Log::info('تم إنشاء الإصدار الجديد للمستند بنجاح.', [
                'version' => $nextVersionStr,
                'path' => $storedPath
            ]);

            return [
                'success' => true,
                'new_version' => $newVersion,
                'stored_path' => $storedPath
            ];
        });
    }

    /**
     * تنزيل مستند قانوني بشكل آمن مع التحقق من الصلاحيات.
     *
     * @param array $document بيانات المستند المراد تحميله
     * @param array $version تفاصيل الإصدار المحدد للتحميل
     * @param mixed $user المستخدم الذي يطلب التحميل
     * @return StreamedResponse
     * @throws AuthorizationException|Exception
     */
    public function downloadDocument(array $document, array $version, $user): StreamedResponse
    {
        // التحقق من الصلاحيات للتأكد من أحقية الوصول للملف
        $this->authorizeUser($document, $user, 'view');

        $filePath = $version['file_path'] ?? null;

        if (!$filePath || !Storage::disk($this->storageDisk)->exists($filePath)) {
            Log::error('محاولة تحميل ملف غير موجود على الخادم.', ['file_path' => $filePath]);
            throw new Exception('عذراً، لم يتم العثور على الملف الفيزيائي في وحدة التخزين.');
        }

        Log::info('تنزيل مستند قانوني مصرح به.', [
            'document_id' => $document['id'] ?? 'unknown',
            'version' => $version['version_number'] ?? 'unknown',
            'user_id' => $user->id ?? 'system'
        ]);

        return Storage::disk($this->storageDisk)->download(
            $filePath,
            $version['file_name'] ?? 'downloaded_document'
        );
    }

    /**
     * حذف مستند نهائياً أو إصدار محدد مع حذف الملفات الفعلية من القرص.
     *
     * @param array $document المستند الرئيسي المراد حذفه
     * @param array $versions قائمة بكافة إصدارات المستند لحذف ملفاتها
     * @param mixed $user المستخدم طالب الحذف
     * @return bool
     * @throws AuthorizationException
     */
    public function deleteDocument(array $document, array $versions, $user): bool
    {
        $this->authorizeUser($document, $user, 'delete');

        Log::warning('بدء حذف مستند وإصداراته بالكامل.', [
            'document_id' => $document['id'] ?? 'unknown',
            'deleted_by' => $user->id ?? 'system'
        ]);

        return DB::transaction(function () use ($versions) {
            foreach ($versions as $version) {
                $filePath = $version['file_path'] ?? null;
                if ($filePath && Storage::disk($this->storageDisk)->exists($filePath)) {
                    Storage::disk($this->storageDisk)->delete($filePath);
                    Log::info('تم مسح الملف الفيزيائي بنجاح.', ['path' => $filePath]);
                }
            }
            return true;
        });
    }

    /**
     * التحقق من سلامة وصلاحية الملف القانوني المرفوع.
     */
    protected function validateFile(UploadedFile $file): void
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $this->allowedExtensions, true)) {
            Log::warning('محاولة رفع ملف بامتداد غير مدعوم أو غير آمن.', [
                'extension' => $extension,
                'mime' => $file->getClientMimeType()
            ]);
            throw new InvalidArgumentException(
                sprintf('امتداد الملف غير مدعوم للمستندات القانونية. الامتدادات المدعومة هي: %s', implode(', ', $this->allowedExtensions))
            );
        }
    }

    /**
     * التحقق الأمني من صلاحية المستخدم للقيام بعمليات العرض أو التعديل أو الحذف.
     */
    protected function authorizeUser(array $document, $user, string $ability): void
    {
        // مثال عملي: يحق للمالك، أو للمحامي المعني بالقضية، أو للمشرف تعديل/حذف الملف
        $ownerId = $document['user_id'] ?? null;

        if (!$user) {
            throw new AuthorizationException('المستخدم غير مصرح له بالوصول للعمليات الحساسة.');
        }

        // التحقق من أحقية الوصول (مثال مرن قابل للتطوير وفق منطق الصلاحيات لديك)
        if (isset($user->role) && $user->role === 'admin') {
            return; // المدير له صلاحيات مطلقة
        }

        if ($ownerId !== null && (int)$user->id === (int)$ownerId) {
            return; // المالك الأصلي له الصلاحية الكاملة على مستنده
        }

        // التحقق من الصلاحيات المتقدمة بناء على طبيعة الإجراء
        if ($ability === 'view' && isset($document['client_id']) && (int)$user->client_id === (int)$document['client_id']) {
            return; // يحق للعميل استعراض مستنداته الشخصية
        }

        Log::error('فشل التحقق الأمني من الصلاحية.', [
            'user_id' => $user->id ?? 'unknown',
            'ability' => $ability,
            'document' => $document
        ]);

        throw new AuthorizationException('ليس لديك الصلاحيات اللازمة لإتمام هذا الإجراء على المستند.');
    }

    /**
     * زيادة رقم الإصدار تلقائياً (مثال: من 1.0.0 إلى 1.1.0 أو 2.0.0 حسب النمط المعتمد)
     */
    protected function incrementVersionString(string $currentVersion): string
    {
        $parts = explode('.', $currentVersion);
        if (count($parts) < 3) {
            return '1.1.0';
        }

        $major = (int)$parts[0];
        $minor = (int)$parts[1];
        $patch = (int)$parts[2];

        // افتراض الترقية على مستوى الجزء الفرعي (Minor Version)
        $minor++;

        return "{$major}.{$minor}.{$patch}";
    }
}
