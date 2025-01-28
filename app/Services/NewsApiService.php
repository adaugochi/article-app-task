<?php

namespace App\Services;

use jcobhams\NewsApi\NewsApi;
use jcobhams\NewsApi\NewsApiException;

class NewsApiService
{
    protected NewsApi $news_api;

    public function __construct(NewsApi $news_api)
    {
        $this->news_api = $news_api;
    }

    /**
     * @throws NewsApiException
     */
    public function articles(): array
    {
        $categories = ['business', 'entertainment', 'general', 'health', 'science', 'sports', 'technology'];
        $language = config('app.locale');
        $allArticles = [];

        foreach ($categories as $query) {
            $response = $this->news_api->getEverything($query, null, null, null, null, null, $language);

            $articles = data_get($response, 'articles', []);
            $allArticles = array_merge($allArticles, $articles);
        }

        return $allArticles;
    }
}
