<?php

namespace App\Listeners;

use App\Events\CaseCreated;
use App\Services\ActivityLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogCaseActivity implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * خدمة تسجيل الأنشطة.
     *
     * @var ActivityLogService
     */
    protected ActivityLogService $activityLogService;

    /**
     * إنشاء مثيل المستمع.
     *
     * @param ActivityLogService $activityLogService
     * @return void
     */
    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * معالجة الحدث.
     *
     * @param  CaseCreated  $event
     * @return void
     */
    public function handle(CaseCreated $event): void
    {
        // استخراج كائن الحالة القانونية والمستخدم من الحدث
        $legalCase = $event->legalCase;
        $user = $event->user; // يفترض أن الحدث يمرر المستخدم الذي قام بالعملية

        // بناء رسالة النشاط
        $message = "تم إنشاء حالة قانونية جديدة: {$legalCase->name} بواسطة {$user->name}.";

        // تسجيل النشاط باستخدام خدمة ActivityLogService
        $this->activityLogService->recordActivity(
            $user->id,
            'case_created',
            $message,
            ['case_id' => $legalCase->id, 'case_name' => $legalCase->name]
        );
    }
}
