<?php

namespace App\Repositories;

use App\Models\Article;

class ArticleRepository extends BaseRepository
{

    public function getModel(): Article
    {
        return new Article();
    }

    protected function applySearchQuery($query, $search)
    {
        return $query->when($search, function ($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%")
                ->orWhere('category', 'LIKE', "%{$search}%")
                ->orWhere('source', 'LIKE', "%{$search}%");
        });
    }

    protected function applyFilters($query, $options)
    {
        foreach ($options as $filterKey => $filterValue) {
            switch ($filterKey) {
            case 'category':
            case 'source':
                $query->where($filterKey, $filterValue);
                break;

            case 'dates':
                $dates = explode(',', $filterValue);
                if (count($dates) === 2) {
                    $startDate = trim($dates[0]);
                    $endDate = trim($dates[1]);
                    $query->whereBetween('published_at', [$startDate, $endDate]);
                }
                break;

            default:
                break;
            }
        }
        return $query;
    }
}
