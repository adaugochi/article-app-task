<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class GuardianApiService
{
    protected string $apiKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.guardian.key');
        $this->baseUrl = config('services.guardian.url');
    }

    /**
     * @throws Exception
     */
    public function fetchArticles($page): array
    {
        $response = Http::get("{$this->baseUrl}/search", [
            'api-key' => $this->apiKey,
            'page' => $page,
            'page-size' => 100,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to fetch articles from Guardian API' . $response);
        }

        return $response->json('response', []);
    }
}
