<?php

namespace App\Jobs;

use App\Enum\DataSourceEnum;
use App\Models\Article;
use App\Services\GuardianApiService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessGuardianNewsApiJob implements ShouldQueue
{
    use Queueable;

    protected GuardianApiService $service;

    public function __construct()
    {
        $this->service = new GuardianApiService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $page = Cache::get('guardian_api_current_page', 1); // Get the last processed page
        $totalPages = Cache::get('guardian_api_total_pages', 1);
        $bulkData = [];

        try {
            do {
                $response = $this->service->fetchArticles($page);

                if (isset($response['results'])) {
                    $articles = $response['results'];
                    $totalPages = $response['pages'];
                    Cache::put('guardian_api_total_pages', $totalPages);
                    Log::info("Processing page $page of $totalPages...");

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
                    }
                }
                Cache::put('guardian_api_current_page', $page);
                $page++;
                sleep(1);
            } while ($page <= $totalPages);
        } catch (\Exception $exception) {
            Log::error('Error fetching news: ' . $exception->getMessage());
        }
    }
}
