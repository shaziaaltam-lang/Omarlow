<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\InvoiceService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private InvoiceService $invoiceService)
    {
    }

    public function index()
    {
        $invoices = $this->invoiceService->getAllInvoices();
        return $this->successResponse($invoices, 'تم جلب الفواتير بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices',
            'client_id' => 'required|exists:clients,id',
            'case_id' => 'required|exists:cases,id',
            'amount' => 'required|numeric|min:0',
            'due_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $invoice = $this->invoiceService->createInvoice($validated);
        return $this->successResponse($invoice, 'تم إنشاء الفاتورة بنجاح', 201);
    }

    public function show($id)
    {
        $invoice = \App\Models\Invoice::find($id);
        
        if (!$invoice) {
            return $this->errorResponse('الفاتورة غير موجودة', null, 404);
        }

        return $this->successResponse($invoice);
    }

    public function markAsPaid($id)
    {
        $invoice = \App\Models\Invoice::find($id);
        
        if (!$invoice) {
            return $this->errorResponse('الفاتورة غير موجودة', null, 404);
        }

        $invoice = $this->invoiceService->markInvoiceAsPaid($invoice);
        return $this->successResponse($invoice, 'تم تحديث حالة الفاتورة بنجاح');
    }
}
