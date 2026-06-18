<?php

namespace App\Services;

use App\Models\Client;

class ClientService
{
    public function getAllClients($paginate = true, $perPage = 15)
    {
        $query = Client::where('is_active', true);
        
        if ($paginate) {
            return $query->paginate($perPage);
        }
        
        return $query->get();
    }

    public function createClient(array $data)
    {
        return Client::create($data);
    }

    public function updateClient(Client $client, array $data)
    {
        $client->update($data);
        return $client;
    }

    public function deleteClient(Client $client)
    {
        return $client->delete();
    }
}
