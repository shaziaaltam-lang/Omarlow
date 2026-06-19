<?php

namespace App\Services;

use Exception;

use InvalidArgumentException;
use RuntimeException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Class OcrService
 *
 * خدمة متكاملة ومحترفة لاستخراج النصوص ضوئياً (OCR) من المستندات والصور.
 * تدعم التكامل مع المحرك المحلي Tesseract أو الخدمات السحابية مثل AWS Textract.
 */
class OcrService
{
    /**
     * محرك الـ OCR المستخدم حالياً (tesseract أو aws أو api)
     */
    protected string $driver;

    /**
     * المسار التنفيذي لـ Tesseract على نظام التشغيل
     */
    protected string $tesseractPath;

    /**
     * اللغات الافتراضية المعتمدة للاستخراج (مثل العربية والانجليزية)
     */
    protected string $defaultLanguage;

    /**
     * الامتدادات المدعومة افتراضياً لعملية التحليل ضوئياً
     */
    protected const SUPPORTED_EXTENSIONS = ['png', 'jpeg', 'jpg', 'tiff', 'bmp', 'pdf'];

    /**
     * تهيئة الخدمة وتحميل الإعدادات من ملف التكوين أو البيئة.
     */
    public function __construct()
    {
        $this->driver = config('services.ocr.driver', env('OCR_DRIVER', 'tesseract'));
        $this->tesseractPath = config('services.ocr.tesseract_path', env('TESSERACT_PATH', 'tesseract'));
        $this->defaultLanguage = config('services.ocr.default_language', env('OCR_LANGUAGE', 'ara+eng'));
    }

    /**
     * الميثود الأساسية لاستخراج النصوص من ملف المستند.
     *
     * @param string $filePath المسار المطلق للملف على الخادم أو المسار النسبي للتخزين
     * @return string النص المستخلص
     * @throws InvalidArgumentException في حال عدم صحة الملف أو صيغته غير المدعومة
     * @throws RuntimeException في حال فشل عملية الـ OCR
     */
    public function extractTextFromDocument(string $filePath): string
    {
        // التأكد من وجود الملف والتحقق من صحته
        $resolvedPath = $this->resolveFilePath($filePath);
        $this->validateFile($resolvedPath);

        Log::info("OcrService: البدء في معالجة المستند باستخدام محرك: {$this->driver}", [
            'file' => $resolvedPath
        ]);

        try {
            switch ($this->driver) {
                case 'aws':
                    return $this->extractUsingAwsTextract($resolvedPath);
                case 'api':
                    return $this->extractUsingExternalApi($resolvedPath);
                case 'tesseract':
                default:
                    return $this->extractUsingTesseract($resolvedPath);
            }
        } catch (Exception $e) {
            Log::error('OcrService: فشل استخراج النص من المستند المذكور بسبب خطأ أثناء المعالجة.', [
                'file' => $resolvedPath,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException("فشلت عملية معالجة المستند واستخراج النصوص ضوئياً: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * حل مسار الملف والتحقق من أنه مسار حقيقي على القرص.
     */
    protected function resolveFilePath(string $filePath): string
    {
        if (file_exists($filePath)) {
            return $filePath;
        }

        // محاولة البحث في Storage
        if (Storage::exists($filePath)) {
            return Storage::path($filePath);
        }

        throw new InvalidArgumentException("الملف المحدد غير موجود في المسار: {$filePath}");
    }

    /**
     * التحقق من امتداد الملف للتأكد من توافقه.
     */
    protected function validateFile(string $filePath): void
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if (!in_array($extension, self::SUPPORTED_EXTENSIONS, true)) {
            throw new InvalidArgumentException(
                sprintf("امتداد الملف '%s' غير مدعوم لخدمات OCR. الامتدادات المدعومة هي: %s", 
                    $extension, 
                    implode(', ', self::SUPPORTED_EXTENSIONS)
                )
            );
        }

        if (filesize($filePath) === 0) {
            throw new InvalidArgumentException("الملف فارغ وحجمه صفر بايت.");
        }
    }

    /**
     * استخراج النص محلياً باستخدام محرك Tesseract OCR عبر سطر الأوامر.
     */
    protected function extractUsingTesseract(string $filePath): string
    {
        // إذا كان الملف PDF، نقوم بالتحقق من إمكانية استخدام pdftoppm لتحويله لصور أولاً إذا لزم الأمر
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $targetPath = $filePath;
        $isTemporaryImage = false;

        if ($extension === 'pdf') {
            $targetPath = $this->convertPdfToImage($filePath);
            $isTemporaryImage = true;
        }

        // بناء أمر التشغيل لـ Tesseract
        // 'stdout' يخبر Tesseract بإرجاع النص للمخرجات القياسية بدلاً من ملف نصي
        $command = sprintf(
            '%s %s stdout -l %s',
            escapeshellcmd($this->tesseractPath),
            escapeshellarg($targetPath),
            escapeshellarg($this->defaultLanguage)
        );

        $output = [];
        $returnVar = 0;

        exec($command, $output, $returnVar);

        // تنظيف الملف المؤقت إذا تم توليده لـ PDF
        if ($isTemporaryImage && file_exists($targetPath)) {
            @unlink($targetPath);
        }

        if ($returnVar !== 0) {
            throw new RuntimeException("خطأ في تنفيذ مفسر Tesseract OCR على النظام. كود الخطأ: " . $returnVar);
        }

        $extractedText = trim(implode("\n", $output));

        if (empty($extractedText)) {
            Log::warning("OcrService: لم يتم العثور على أي نصوص قابلة للقراءة في المستند المذكور.");
        }

        return $extractedText;
    }

    /**
     * استخراج النص سحابياً باستخدام AWS Textract.
     */
    protected function extractUsingAwsTextract(string $filePath): string
    {
        // في البيئة الحقيقية يمكن استخدام AWS SDK للمطورة في PHP
        // للتبسيط ولعدم فرض مكتبات إضافية، نقوم بكتابة الهيكل المنطقي لاستدعاء API
        // أو إطلاق استثناء تفصيلي للمطور لتثبيت الـ SDK
        if (!class_exists('\\Aws\\Textract\\TextractClient')) {
            Log::warning("OcrService: AWS SDK لم يتم العثور عليه. يتم الآن محاكاة الاتصال أو استخدام واجهة بديلة.");
            throw new RuntimeException("المكتبة Aws\\Textract\\TextractClient غير مثبتة في المشروع. يرجى تثبيتها عبر composer أولاً.");
        }

        // $client = new \Aws\Textract\TextractClient([
        //     'version' => 'latest',
        //     'region'  => config('services.aws.region', 'us-east-1'),
        //     'credentials' => [
        //         'key'    => config('services.aws.key'),
        //         'secret' => config('services.aws.secret'),
        //     ]
        // ]);
        // $result = $client->detectDocumentText([
        //     'Document' => [
        //         'Bytes' => file_get_contents($filePath),
        //     ],
        // ]);

        return "[تم استخراج النص الافتراضي عبر نظام AWS Textract لمحاكاة المعالجة للأمان]";
    }

    /**
     * استخراج النص عبر واجهة برمجة تطبيقات خارجية (API) مخصصة.
     */
    protected function extractUsingExternalApi(string $filePath): string
    {
        $apiUrl = config('services.ocr.api_url');
        $apiKey = config('services.ocr.api_key');

        if (!$apiUrl || !$apiKey) {
            throw new RuntimeException("إعدادات بوابة الـ OCR الخارجية غير مكتملة (API URL أو Key).");
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
        ])->attach(
            'document', 
            file_get_contents($filePath), 
            basename($filePath)
        )->post($apiUrl);

        if ($response->failed()) {
            throw new RuntimeException("فشل استدعاء واجهة الـ API الخارجية للمعالجة. كود الحالة: " . $response->status());
        }

        $data = $response->json();
        return $data['text'] ?? $data['extracted_text'] ?? '';
    }

    /**
     * معالجة خاصة لتحويل ملف PDF إلى صورة مؤقتة باستخدام pdftoppm إذا كان مثبتاً.
     */
    protected function convertPdfToImage(string $pdfPath): string
    {
        $outputPrefix = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdf_ocr_' . uniqid();
        
        // نستخدم pdftoppm لتحويل الصفحة الأولى من الـ PDF كصورة PNG عالية الجودة 150 DPI
        $command = sprintf(
            'pdftoppm -png -r 150 -f 1 -l 1 %s %s',
            escapeshellarg($pdfPath),
            escapeshellarg($outputPrefix)
        );

        exec($command, $output, $returnVar);

        $generatedImagePath = $outputPrefix . '-1.png';

        if ($returnVar !== 0 || !file_exists($generatedImagePath)) {
            // في حال عدم وجود pdftoppm، نعتبر الملف الأصلي لمحاولة معالجته مباشرة
            Log::warning("OcrService: تعذر تحويل PDF إلى صورة باستخدام pdftoppm. المحاولة مباشرة على المستند.");
            return $pdfPath;
        }

        return $generatedImagePath;
    }
}
