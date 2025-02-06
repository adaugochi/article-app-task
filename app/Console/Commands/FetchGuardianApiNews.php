<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\GuardianApiService;
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
        try {
            $response = $this->service->fetchArticles();
            DB::transaction(function () use ($response) {
                $chunkSize = 100;
                foreach (array_chunk($response, $chunkSize) as $chunk) {  // Chunk data to avoid memory issues
                    Article::query()->upsert(
                        $chunk,
                        ['url'], // Unique key
                        ['title', 'category', 'source', 'published_at']
                    );
                }
            });
            $this->info('Articles successfully saved to the database.');
        } catch (\Exception $exception) {
            $this->error('Error fetching news: ' . $exception->getMessage());
        }

    }
}
