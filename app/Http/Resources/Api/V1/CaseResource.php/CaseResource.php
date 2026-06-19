<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

/**
 * Class CaseResource
 * @package App\Http\Resources\Api\V1
 * 
 * @mixin \App\Models\LegalCase
 */
class CaseResource extends JsonResource
{
    /**
     * تحويل نموذج القضية القانونية إلى مصفوفة قابلة للتمثيل كـ JSON.
     * 
     * يتم تنسيق التواريخ، التعامل مع القيم الفارغة، وجلب العلاقات بشكل آمن ومترجم.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        return [
            'id' => $this->id,
            'case_number' => $this->case_number,
            'title' => $this->title,
            'description' => $this->description,
            'court_name' => $this->court_name ?? null,
            
            // تنسيق الحالات والأنواع المترجمة محلياً بناءً على الحقول المتوفرة أو ملفات الترجمة
            'status' => [
                'id' => $this->status_id ?? null,
                'code' => $this->relationLoaded('caseStatus') ? ($this->caseStatus->code ?? null) : null,
                'name' => $this->relationLoaded('caseStatus') 
                    ? ($locale === 'ar' ? ($this->caseStatus->name_ar ?? $this->caseStatus->name) : ($this->caseStatus->name_en ?? $this->caseStatus->name)) 
                    : ($this->status ?? __('app.statuses.' . ($this->status ?? 'unknown'))),
            ],
            
            'type' => [
                'id' => $this->type_id ?? null,
                'code' => $this->relationLoaded('caseType') ? ($this->caseType->code ?? null) : null,
                'name' => $this->relationLoaded('caseType')
                    ? ($locale === 'ar' ? ($this->caseType->name_ar ?? $this->caseType->name) : ($this->caseType->name_en ?? $this->caseType->name))
                    : ($this->type ?? __('app.types.' . ($this->type ?? 'unknown'))),
            ],

            // معالجة التواريخ وتنسيقها بشكل متناسق
            'filed_date' => $this->filed_date 
                ? ($this->filed_date instanceof Carbon ? $this->filed_date->format('Y-m-d') : Carbon::parse($this->filed_date)->format('Y-m-d')) 
                : null,
            
            'created_at' => $this->created_at 
                ? ($this->created_at instanceof Carbon ? $this->created_at->toIso8601String() : Carbon::parse($this->created_at)->toIso8601String()) 
                : null,
                
            'updated_at' => $this->updated_at 
                ? ($this->updated_at instanceof Carbon ? $this->updated_at->toIso8601String() : Carbon::parse($this->updated_at)->toIso8601String()) 
                : null,

            // جلب العميل المرتبط بشكل آمن في حال تحميل العلاقة مسبقاً لمنع N+1 Queries
            'client' => $this->whenLoaded('client', function () {
                return [
                    'id' => $this->client->id,
                    'name' => $this->client->name,
                    'email' => $this->client->email,
                    'phone' => $this->client->phone,
                    'company_name' => $this->client->company_name ?? null,
                ];
            }),

            // جلب المستخدم (المحامي / المسؤول) المعين للقضية
            'assigned_user' => $this->whenLoaded('assignedUser', function () {
                return [
                    'id' => $this->assignedUser->id,
                    'name' => $this->assignedUser->name,
                    'email' => $this->assignedUser->email,
                ];
            }),

            // جلب ملخص الفواتير والمدفوعات المرتبطة بالقضية للحصول على نظرة مالية سريعة
            'financial_summary' => $this->whenLoaded('invoices', function () {
                $totalAmount = $this->invoices->sum('total_amount');
                $paidAmount = $this->invoices->sum('paid_amount');
                $balance = $totalAmount - $paidAmount;
                
                return [
                    'total_invoices_count' => $this->invoices->count(),
                    'total_amount' => round((float)$totalAmount, 2),
                    'paid_amount' => round((float)$paidAmount, 2),
                    'remaining_balance' => round((float)$balance, 2),
                    'currency' => config('app.currency', 'SAR'),
                    'invoices' => $this->invoices->map(fn ($invoice) => [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'status' => $invoice->status,
                        'total' => round((float)$invoice->total_amount, 2),
                        'due_date' => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
                    ]),
                ];
            }),
            
            // بيانات إضافية مخصصة للتحقق من الصلاحيات أو الإجراءات المتاحة
            'meta' => [
                'is_active' => in_array($this->status, ['active', 'open', 'in_progress', 'قيد_النظر']),
                'can_update' => auth()->check() && auth()->user()->can('update', $this->resource),
            ]
        ];
    }
}
