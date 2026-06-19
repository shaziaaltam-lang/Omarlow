<?php

namespace App\Events;

use App\Models\LegalCase;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CaseCreated
{
    use Dispatchable, SerializesModels;

    /**
     * إنشاء مثيل جديد للحدث.
     *
     * @param  \App\Models\LegalCase  $legalCase
     * @return void
     */
    public function __construct(public readonly LegalCase $legalCase)
    {
        // بيانات القضية القانونية (LegalCase) متاحة كخاصية $this->legalCase
    }

    /**
     * الحصول على قنوات البث التي يجب أن يبث الحدث عليها.
     *
     * @return array
     */
    // public function broadcastOn()
    // {
    //     return []; // يمكن إضافة قنوات البث هنا مثل: return new PrivateChannel('cases.' . $this->legalCase->id);
    // }
}
