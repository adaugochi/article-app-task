<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use App\Enum\DataSourceEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Http;

class NewYorkTimeApiService implements ArticleApiInterface
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

        $articles = data_get($response->json(), 'response.docs', []);
        $data = [];
        foreach ($articles as $key => $article) {
            $publishedAt = Carbon::parse(data_get($article, 'pub_date'))->format('Y-m-d H:i:s');

            $data[] = [
                'url' => data_get($article, 'web_url'),
                'title' => data_get($article, 'snippet'),
                'description' => data_get($article, 'lead_paragraph'),
                'source' => DataSourceEnum::NEW_YORK_TIMES->value,
                'category' => data_get($article, 'subsection_name'),
                'published_at' => $publishedAt
            ];
        }

        return $data;
    }
}
