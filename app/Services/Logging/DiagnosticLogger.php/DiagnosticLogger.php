<?php

namespace App\Services\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\JsonFormatter;
use Exception;

/**
 * خدمة DiagnosticLogger المسؤولة عن توثيق الأخطاء والأحداث الحرجة في النظام.
 */
class DiagnosticLogger
{
    protected $logger;

    /**
     * تهيئة سجل التشخيص وربطه بملف مخصص.
     */
    public function __construct()
    {
        $this->logger = new Logger('diagnostic_logger');
        $logPath = storage_path('logs/diagnostic.log');
        
        $handler = new StreamHandler($logPath, Logger::DEBUG);
        $handler->setFormatter(new JsonFormatter());
        
        $this->logger->pushHandler($handler);
    }

    /**
     * تسجيل حدث حرج مع بيانات إضافية.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logCriticalEvent(string $message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * تسجيل خطأ عام مع تتبع الاستثناء.
     *
     * @param Exception $exception
     * @param array $context
     * @return void
     */
    public function logError(Exception $exception, array $context = []): void
    {
        $this->logger->error($exception->getMessage(), array_merge($context, [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]));
    }

    /**
     * تسجيل معلومات إضافية لأغراض التشخيص.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function logInfo(string $message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }
}