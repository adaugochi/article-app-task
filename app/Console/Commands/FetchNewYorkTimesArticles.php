<?php

namespace App\Console\Commands;

use App\Enum\DataSourceEnum;
use App\Models\Article;
use App\Services\NewYorkTimeApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use jcobhams\NewsApi\NewsApiException;

class FetchNewYorkTimesArticles extends Command
{
    protected NewYorkTimeApiService $service;

    public function __construct(NewYorkTimeApiService $new_york_time_api_service)
    {
        parent::__construct();
        $this->service = $new_york_time_api_service;
    }
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:new-york-times:articles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $bulkData = [];

            $articles = $this->service->fetchArticles();

            foreach ($articles as $key => $article) {
                $publishedAt = Carbon::parse(data_get($article, 'pub_date'))->format('Y-m-d H:i:s');

                $bulkData[] = [
                    'url' => data_get($article, 'web_url'),
                    'title' => data_get($article, 'snippet'),
                    'description' => data_get($article, 'lead_paragraph'),
                    'source' => DataSourceEnum::NEW_YORK_TIMES->value,
                    'category' => data_get($article, 'subsection_name'),
                    'published_at' => $publishedAt
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
                            ['title', 'description', 'category', 'source', 'published_at']
                        );
                    }
                });

                $this->info('Articles successfully saved to the database.');
            } else {
                $this->info('No new articles to process.');
            }

        } catch (\Exception $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }
}
