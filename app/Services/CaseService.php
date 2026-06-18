<?php

namespace App\Services;

use App\Models\LegalCase;

class CaseService
{
    public function getAllCases($paginate = true, $perPage = 15)
    {
        $query = LegalCase::with(['client', 'lawyer']);
        
        if ($paginate) {
            return $query->paginate($perPage);
        }
        
        return $query->get();
    }

    public function createCase(array $data)
    {
        return LegalCase::create($data);
    }

    public function updateCase(LegalCase $case, array $data)
    {
        $case->update($data);
        return $case;
    }

    public function getCasesByClient($clientId)
    {
        return LegalCase::where('client_id', $clientId)->get();
    }
}
