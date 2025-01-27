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
    public function articles()
    {
        // business entertainment general health science sports technology
        $query = 'business';
        $language = config('app.locale');

        return $this->news_api->getEverything($query, null, null, null, null, null, $language);
    }
}
