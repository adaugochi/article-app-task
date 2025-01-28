<?php

namespace App\Console\Commands;

use App\Enum\DataSourceEnum;
use App\Jobs\ProcessGuardianNewsApiJob;
use App\Models\Article;
use App\Services\GuardianApiService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FetchGuardianApiNews extends Command
{
    protected GuardianApiService $service;
    public function __construct(GuardianApiService $guardian_api_service)
    {
        parent::__construct();
        $this->service = $guardian_api_service;
    }

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:guardian-news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch news from the Guardian API and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fetching articles from the Guardian API...');
        $page = 1;
        $bulkData = [];

        try {
            $response = $this->service->fetchArticles($page);

            if (isset($response['results'])) {
                $articles = $response['results'];
                $totalPages = $response['pages'];
                $this->info("Processing page $page of $totalPages...");

                foreach ($articles as $articleData) {
                    $publishedAt = Carbon::parse(data_get($articleData, 'webPublicationDate'))
                        ->format('Y-m-d H:i:s');

                    $bulkData[] = [
                        'url' => data_get($articleData, 'webUrl'),
                        'title' => data_get($articleData, 'webTitle'),
                        'published_at' => $publishedAt,
                        'category' => data_get($articleData, 'pillarName'),
                        'source' => DataSourceEnum::THE_GUARDIAN->value,
                    ];
                }

                if (!empty($bulkData)) {
                    DB::transaction(function () use ($bulkData) {
                        $chunkSize = 100;
                        foreach (array_chunk($bulkData, $chunkSize) as $chunk) {  // Chunk data to avoid memory issues
                            Article::query()->upsert(
                                $chunk,
                                ['url'], // Unique key
                                ['title', 'category', 'source', 'published_at']
                            );
                        }
                    });

                    $this->info('Articles successfully saved to the database.');
                } else {
                    $this->info('No new articles to process.');
                }
            }
        } catch (\Exception $exception) {
            $this->error('Error fetching news: ' . $exception->getMessage());
        }

    }
}
