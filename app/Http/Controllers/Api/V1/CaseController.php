<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CaseService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CaseController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private CaseService $caseService)
    {
    }

    public function index()
    {
        $cases = $this->caseService->getAllCases();
        return $this->successResponse($cases, 'تم جلب الحالات بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_number' => 'required|string|unique:cases',
            'client_id' => 'required|exists:clients,id',
            'case_type_id' => 'required|exists:case_types,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        $case = $this->caseService->createCase($validated);
        return $this->successResponse($case, 'تم إنشاء الحالة بنجاح', 201);
    }

    public function show($id)
    {
        $case = \App\Models\LegalCase::find($id);
        
        if (!$case) {
            return $this->errorResponse('الحالة غير موجودة', null, 404);
        }

        return $this->successResponse($case);
    }

    public function update(Request $request, $id)
    {
        $case = \App\Models\LegalCase::find($id);
        
        if (!$case) {
            return $this->errorResponse('الحالة غير موجودة', null, 404);
        }

        $validated = $request->validate([
            'case_status_id' => 'sometimes|exists:case_statuses,id',
            'lawyer_id' => 'sometimes|exists:users,id',
            'description' => 'sometimes|string',
        ]);

        $case = $this->caseService->updateCase($case, $validated);
        return $this->successResponse($case, 'تم تحديث الحالة بنجاح');
    }
}
