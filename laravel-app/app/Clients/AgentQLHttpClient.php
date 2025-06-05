<?php

namespace App\Clients;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Exceptions\AgentQL\AgentQLException;

class AgentQLHttpClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
        private readonly int $timeout
    ) {}

    /**
     * Query data from a URL using AgentQL
     */
    public function queryData(string $url, string $query, array $params = []): array
    {
        $response = $this->makeRequest('/query-data', [
            'url' => $url,
            'query' => $query,
            'params' => $params,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Query data using natural language prompt
     */
    public function queryDataWithPrompt(string $url, string $prompt, array $params = []): array
    {
        $response = $this->makeRequest('/query-data', [
            'url' => $url,
            'prompt' => $prompt,
            'params' => $params,
        ]);

        return $this->handleResponse($response);
    }

    /**
     * Make HTTP request to AgentQL API
     */
    private function makeRequest(string $endpoint, array $data): Response
    {
        return Http::withHeaders([
            'X-API-Key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])
        ->timeout($this->timeout)
        ->post($this->baseUrl . $endpoint, $data);
    }

    /**
     * Handle API response and extract data
     */
    private function handleResponse(Response $response): array
    {
        if (!$response->successful()) {
            throw new AgentQLException(
                'AgentQL API request failed: ' . $response->body(),
                $response->status()
            );
        }

        $responseData = $response->json();

        if (!isset($responseData['data'])) {
            throw new AgentQLException('Invalid response format from AgentQL API');
        }

        return $responseData['data'];
    }
} 