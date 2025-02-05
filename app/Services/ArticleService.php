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

    public function getArticles($request): LengthAwarePaginator
    {
        $search = $request->input('q');
        $filters = $request->only(['dates', 'source', 'category']);
        return $this->repo->getPaginated($search, $filters);
    }

    public function getArticlesByCategory($category): LengthAwarePaginator
    {
        return $this->repo->getArticlesByCategory($category);
    }
}
