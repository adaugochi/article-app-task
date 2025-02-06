<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use App\Enum\DataSourceEnum;
use Carbon\Carbon;
use jcobhams\NewsApi\NewsApi;
use jcobhams\NewsApi\NewsApiException;

class NewsApiService implements ArticleApiInterface
{
    protected NewsApi $news_api;

    public function __construct(NewsApi $news_api)
    {
        $this->news_api = $news_api;
    }

    /**
     * @throws NewsApiException
     */
    public function fetchArticles(): array
    {
        $categories = ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'];
        $language = config('app.locale');
        $allArticles = [];

        foreach ($categories as $query) {
            $response = $this->news_api->getEverything($query, null, null, null, null, null, $language);

            $articles = data_get($response, 'articles', []);
            $allArticles = array_merge($allArticles, $articles);
        }

        $data = [];
        foreach ($allArticles as $article) {
            $publishedAt = Carbon::parse(data_get($article, 'publishedAt'))->format('Y-m-d H:i:s');

            $data[] = [
                'url' => data_get($article, 'url'),
                'title' => data_get($article, 'title'),
                'description' => data_get($article, 'description'),
                'category' => data_get($article, 'source.name'),
                'source' => DataSourceEnum::NEWS_API->value,
                'published_at' => $publishedAt,
                'image_url' => data_get($article, 'urlToImage')
            ];
        }

        return $data;
    }
}
