<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class NewYorkTimeApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.new_york_times.key');
        $this->baseUrl = config('services.new_york_times.url');
    }

    /**
     * @throws Exception
     */
    public function fetchArticles(): array
    {
        $response = Http::get("{$this->baseUrl}/articlesearch.json", [
            'api-key' => $this->apiKey
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to fetch articles from New York Times API' . $response);
        }

        return $response->json('response.docs', []);
    }
}
