<?php

namespace App
otifications;

use App
Models\LegalCase;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CaseAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * مثيل القضية القانونية.
     *
     * @var \App\Models\LegalCase
     */
    protected $legalCase;

    /**
     * إنشاء مثيل إشعار جديد.
     */
    public function __construct(LegalCase $legalCase)
    {
        $this->legalCase = $legalCase;
    }

    /**
     * الحصول على قنوات تسليم الإشعار.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * الحصول على تمثيل البريد الإلكتروني للإشعار.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/cases/' . $this->legalCase->id); // افتراض وجود مسار لعرض تفاصيل القضية

        return (new MailMessage)
                    ->subject(__('إشعار: تم تعيين قضية جديدة لك'))
                    ->greeting(__('مرحباً') . ' ' . $notifiable->name . '،')
                    ->line(__('لقد تم تعيين قضية جديدة لك تحتاج إلى اهتمامك.'))
                    ->line(__('رقم القضية: ') . $this->legalCase->case_number)
                    ->line(__('عنوان القضية: ') . $this->legalCase->title)
                    ->line(__('الوصف: ') . $this->legalCase->description)
                    ->action(__('عرض تفاصيل القضية'), $url)
                    ->line(__('شكراً لاستخدامك نظامنا!'));
    }

    /**
     * الحصول على تمثيل المصفوفة للإشعار.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'case_id' => $this->legalCase->id,
            'case_number' => $this->legalCase->case_number,
            'case_title' => $this->legalCase->title,
            'assigned_to_user_id' => $notifiable->id,
            'message' => __('تم تعيين قضية جديدة لك برقم :case_number وعنوان :case_title', [
                'case_number' => $this->legalCase->case_number,
                'case_title' => $this->legalCase->title,
            ]),
            'url' => url('/cases/' . $this->legalCase->id)
        ];
    }
}
