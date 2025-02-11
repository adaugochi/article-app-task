<?php

namespace App\Services;

use App\Contracts\ArticleApiInterface;
use App\Models\Article;
use App\Repositories\ArticleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ArticleService extends BaseService
{
    protected array $articleApis;

    public function __construct(ArticleRepository $article_repository, ArticleApiInterface ...$articleApis)
    {
        $this->repo = $article_repository;
        $this->articleApis = $articleApis;
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

    public function storeArticles(): void
    {
        foreach ($this->articleApis as $api) { // Loop through injected APIs
            try {
                $articles = $api->fetchArticles(); // Fetch articles from API
                Log::info("Fetching...");
                DB::transaction(function () use ($articles) {
                    $chunkSize = 100;
                    foreach (array_chunk($articles, $chunkSize) as $chunk) {  // Chunk data to avoid memory issues
                        Article::query()->upsert(
                            $chunk,
                            ['url'], // Unique key
                            ['title', 'description', 'category', 'source', 'published_at', 'image_url']
                        );
                    }
                });
            } catch (\Exception $e) {
                Log::error('Article Fetching Error: ' . $e->getMessage());
            }
        }
    }
}
