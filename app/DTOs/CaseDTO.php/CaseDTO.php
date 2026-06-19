<?php

namespace App\DTOs;

use Illuminate\Http\Request;

readonly class CaseDTO
{
    public function __construct(
        public string $title,
        public string $description,
        public int $clientId,
        public int $caseTypeId,
        public int $caseStatusId,
        public ?string $openedAt = null
    ) {}

    /**
     * تحويل بيانات الطلب إلى كائن DTO.
     * 
     * @param Request $request
     * @return self
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            title: $request->validated('title'),
            description: $request->validated('description'),
            clientId: (int) $request->validated('client_id'),
            caseTypeId: (int) $request->validated('case_type_id'),
            caseStatusId: (int) $request->validated('case_status_id'),
            openedAt: $request->validated('opened_at')
        );
    }

    /**
     * تحويل الكائن إلى مصفوفة للمساعدة في العمليات اللاحقة.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'client_id' => $this->clientId,
            'case_type_id' => $this->caseTypeId,
            'case_status_id' => $this->caseStatusId,
            'opened_at' => $this->openedAt,
        ];
    }
}