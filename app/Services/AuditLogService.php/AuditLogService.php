<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;

class AuditLogService
{
    /**
     * تسجيل حدث جديد في قاعدة بيانات التدقيق.
     *
     * @param string $action نوع العملية (مثلاً: create, update, delete)
     * @param string $model اسم الكيان المتأثر
     * @param int|string $modelId معرف الكيان
     * @param array $oldData البيانات السابقة
     * @param array $newData البيانات الجديدة
     * @return bool
     */
    public function log(string $action, string $model, $modelId, array $oldData = [], array $newData = []): bool
    {
        try {
            return DB::table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'action' => $action,
                'model_type' => $model,
                'model_id' => $modelId,
                'old_values' => json_encode($oldData),
                'new_values' => json_encode($newData),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('فشل تسجيل التدقيق: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * جلب سجلات التدقيق لكيان معين.
     *
     * @param string $model
     * @param int $modelId
     * @return \Illuminate\Support\Collection
     */
    public function getLogsForModel(string $model, int $modelId)
    {
        return DB::table('audit_logs')
            ->where('model_type', $model)
            ->where('model_id', $modelId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}