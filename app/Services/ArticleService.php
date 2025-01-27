<?php

namespace App\Services;

use App\Repositories\ArticleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService extends BaseService
{
    public function __construct(ArticleRepository $article_repository)
    {
        $this->repo = $article_repository;
    }

    public function getArticles($search, $filters): LengthAwarePaginator
    {
        return $this->repo->getPaginated([], [], $search, $filters);
    }
}
