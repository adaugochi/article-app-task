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
        $articles = $this->service->getArticles($request);
        return response()->json(['data' => $articles]);
    }
}
