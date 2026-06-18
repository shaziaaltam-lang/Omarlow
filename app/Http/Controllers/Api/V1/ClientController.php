<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\ClientService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private ClientService $clientService)
    {
    }

    public function index()
    {
        $clients = $this->clientService->getAllClients();
        return $this->successResponse($clients, 'تم جلب العملاء بنجاح');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
        ]);

        $client = $this->clientService->createClient($validated);
        return $this->successResponse($client, 'تم إنشاء العميل بنجاح', 201);
    }

    public function show($id)
    {
        $client = \App\Models\Client::find($id);
        
        if (!$client) {
            return $this->errorResponse('العميل غير موجود', null, 404);
        }

        return $this->successResponse($client);
    }

    public function update(Request $request, $id)
    {
        $client = \App\Models\Client::find($id);
        
        if (!$client) {
            return $this->errorResponse('العميل غير موجود', null, 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:clients,email,' . $id,
            'phone' => 'sometimes|required|string',
        ]);

        $client = $this->clientService->updateClient($client, $validated);
        return $this->successResponse($client, 'تم تحديث العميل بنجاح');
    }

    public function destroy($id)
    {
        $client = \App\Models\Client::find($id);
        
        if (!$client) {
            return $this->errorResponse('العميل غير موجود', null, 404);
        }

        $this->clientService->deleteClient($client);
        return $this->successResponse(null, 'تم حذف العميل بنجاح');
    }
}
