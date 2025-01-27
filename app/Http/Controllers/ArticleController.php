<?php

namespace App\Http\Controllers;

use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    protected ArticleService $service;

    public function __construct(ArticleService $article_service)
    {
        $this->service = $article_service;
    }

    public function index(Request $request): JsonResponse
    {
        $q = $request->input('q');
        $filters = $request->only(['dates', 'source']);
        $articles = $this->service->getArticles($q, $filters);
        return response()->json(['data' => $articles]);
    }
}
