<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\NewsApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use jcobhams\NewsApi\NewsApiException;

class FetchNewsApiArticles extends Command
{
    protected NewsApiService $newsService;

    public function __construct(NewsApiService $newsService)
    {
        parent::__construct();
        $this->newsService = $newsService;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:news-api:articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch articles from the News API and store them in the database';


    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        try {
            $articles = $this->newsService->fetchArticles();
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
            $this->info('Articles successfully saved to the database.');

        } catch (NewsApiException $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }
}
