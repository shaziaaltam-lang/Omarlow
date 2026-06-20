<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\Logging\DiagnosticLogger;
use Symfony\Component\HttpFoundation\Response;

class RequestLoggerMiddleware
{
    protected $logger;

    /**
     * إنشاء مثيل جديد من الوسيط مع حقن خدمة السجل التشخيصي.
     *
     * @param DiagnosticLogger $logger
     */
    public function __construct(DiagnosticLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * معالجة الطلب الوارد وتسجيل بياناته في سجلات النظام.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = microtime(true) - $startTime;

        $this->logRequest($request, $response, $duration);

        return $response;
    }

    /**
     * تنسيق وكتابة سجلات الطلب باستخدام الـ DiagnosticLogger.
     *
     * @param Request $request
     * @param Response $response
     * @param float $duration
     * @return void
     */
    protected function logRequest(Request $request, Response $response, float $duration): void
    {
        $logData = [
            'method' => $request->getMethod(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration * 1000, 2),
            'user_agent' => $request->userAgent(),
        ];

        $this->logger->info('تم تنفيذ طلب وارد جديد', $logData);
    }
}