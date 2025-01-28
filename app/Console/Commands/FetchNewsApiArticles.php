<?php

namespace App\Console\Commands;

use App\Enum\DataSourceEnum;
use App\Models\Article;
use App\Services\NewsApiService;
use Carbon\Carbon;
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
            $bulkData = [];

            $articles = $this->newsService->articles();
            foreach ($articles as $key => $article) {
                $publishedAt = Carbon::parse(data_get($article, 'publishedAt'))->format('Y-m-d H:i:s');

                $bulkData[] = [
                    'url' => data_get($article, 'url'),
                    'title' => data_get($article, 'title'),
                    'description' => data_get($article, 'description'),
                    'category' => data_get($article, 'source.name'),
                    'source' => DataSourceEnum::NEWS_API->value,
                    'published_at' => $publishedAt,
                    'image_url' => data_get($article, 'urlToImage')
                ];
            }

            if (!empty($bulkData)) {
                $count = count($bulkData);
                $this->info("Processing $count of data...");
                DB::transaction(function () use ($bulkData) {
                    $chunkSize = 100;
                    foreach (array_chunk($bulkData, $chunkSize) as $chunk) {  // Chunk data to avoid memory issues
                        Article::query()->upsert(
                            $chunk,
                            ['url'], // Unique key
                            ['title', 'description', 'category', 'source', 'published_at', 'image_url']
                        );
                    }
                });

                $this->info('Articles successfully saved to the database.');
            } else {
                $this->info('No new articles to process.');
            }

        } catch (NewsApiException $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }
}
