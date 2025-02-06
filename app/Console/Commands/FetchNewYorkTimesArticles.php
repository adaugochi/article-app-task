<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Services\NewYorkTimeApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
            $articles = $this->service->fetchArticles();
            DB::transaction(function () use ($articles) {
                $chunkSize = 100;
                foreach (array_chunk($articles, $chunkSize) as $chunk) {  // Chunk data to avoid memory issues
                    Article::query()->upsert(
                        $chunk,
                        ['url'], // Unique key
                        ['title', 'description', 'category', 'source', 'published_at']
                    );
                }
            });
            $this->info('Articles successfully saved to the database.');

        } catch (\Exception $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }
}
