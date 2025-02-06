<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use App\Enum\DataSourceEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class GuardianApiService implements ArticleApiInterface
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
    public function fetchArticles(): array
    {
        $response = Http::get("{$this->baseUrl}/search", [
            'api-key' => $this->apiKey,
            'page-size' => 100,
        ]);

        if ($response->failed()) {
            throw new Exception('Failed to fetch articles from Guardian API' . $response);
        }

        $articles = data_get($response->json(), 'response.results', []);
        $data = [];
        foreach ($articles as $articleData) {
            $publishedAt = Carbon::parse(data_get($articleData, 'webPublicationDate'))
                ->format('Y-m-d H:i:s');

            $data[] = [
                'url' => data_get($articleData, 'webUrl'),
                'title' => data_get($articleData, 'webTitle'),
                'published_at' => $publishedAt,
                'category' => data_get($articleData, 'pillarName'),
                'source' => DataSourceEnum::THE_GUARDIAN->value,
            ];
        }

        return $data;
    }
}
