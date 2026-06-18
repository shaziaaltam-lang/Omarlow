<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceService
{
    public function getAllInvoices($paginate = true, $perPage = 15)
    {
        $query = Invoice::with(['client', 'case']);
        
        if ($paginate) {
            return $query->paginate($perPage);
        }
        
        return $query->get();
    }

    public function createInvoice(array $data)
    {
        return Invoice::create($data);
    }

    public function updateInvoice(Invoice $invoice, array $data)
    {
        $invoice->update($data);
        return $invoice;
    }

    public function getOverdueInvoices()
    {
        return Invoice::where('status', 'pending')
            ->where('due_date', '<', now())
            ->get();
    }

    public function markInvoiceAsPaid(Invoice $invoice)
    {
        $invoice->update([
            'status' => 'paid',
            'paid_date' => now(),
        ]);
        
        return $invoice;
    }
}
