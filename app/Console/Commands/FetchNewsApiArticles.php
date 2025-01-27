<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\NewsApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
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
            $data = $this->newsService->articles();
            $articles = data_get($data, 'articles', []);
            foreach ($articles as $key => $article) {
                $publishedAt = Carbon::parse(data_get($article, 'publishedAt'))->format('Y-m-d H:i:s');

                Article::query()->updateOrCreate(
                    ['url' => data_get($article, 'url')],
                    [
                        'title' => data_get($article, 'title'),
                        'description' => data_get($article, 'description'),
                        'source' => data_get($article, 'source.name'),
                        'published_at' => $publishedAt,
                        'image_url' => data_get($article, 'urlToImage')
                    ]
                );
            }

            $this->info('Articles fetched and updated successfully.');

        } catch (NewsApiException $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }
}
