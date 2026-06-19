<?php

namespace App\Services\Search;

use Elastic\Elasticsearch\Client;
use Exception;
use Illuminate\Support\Facades\Log;

interface SearchServiceInterface
{
    public function index(string $index, string $id, array $body): bool;
    public function search(string $index, array $query): array;
    public function delete(string $index, string $id): bool;
}

class ElasticSearchService implements SearchServiceInterface
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * فهرسة وثيقة جديدة أو تحديثها في ElasticSearch.
     */
    public function index(string $index, string $id, array $body): bool
    {
        try {
            $params = [
                'index' => $index,
                'id'    => $id,
                'body'  => $body,
            ];
            $this->client->index($params);
            return true;
        } catch (Exception $e) {
            Log::error('ElasticSearch Indexing Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إجراء بحث نصي كامل باستخدام استعلامات ElasticSearch.
     */
    public function search(string $index, array $query): array
    {
        try {
            $params = [
                'index' => $index,
                'body'  => [
                    'query' => $query
                ]
            ];
            $response = $this->client->search($params);
            return $response['hits']['hits'] ?? [];
        } catch (Exception $e) {
            Log::error('ElasticSearch Search Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * حذف وثيقة من الفهرس.
     */
    public function delete(string $index, string $id): bool
    {
        try {
            $params = [
                'index' => $index,
                'id'    => $id,
            ];
            $this->client->delete($params);
            return true;
        } catch (Exception $e) {
            Log::error('ElasticSearch Deletion Error: ' . $e->getMessage());
            return false;
        }
    }
}