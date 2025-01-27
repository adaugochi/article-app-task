<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public int $perPage = 10;

    public abstract function getModel();


    public function findById($id, array $with = []): ?Model
    {
        return $this->getModel()->find($id);
    }

    public function findFirst($conditions, $with = []): ?Model
    {
        return $this->getModel()->where($conditions)->with($with)->first();
    }

    public function getPaginated(
        $conditions = [],
        $with = [],
        $search = null,
        $filters = [],
        string $orderBy = 'updated_at',
        string $sort = 'DESC'
    ): LengthAwarePaginator
    {
        $query = $this->getModel()->where($conditions)->with($with);
        if ($filters) {
            $query = $this->applyFilters($query, $filters); // Apply dynamic filters
        }

        $query = $this->applySearchQuery($query, $search);  // Apply search query if search is provided
        $query = $this->applyOrderBy($query, $orderBy, $sort);

        // Handle dynamic pagination per page
        $perPage = $this->perPage;
        // Extract the page from filters if provided
        $page = $filters['page'] ?? null;

        return $query->paginate($perPage, ['*'], 'page', $page)->withQueryString();
    }

    public function create($attributes): Model
    {
        return $this->getModel()->create($attributes);
    }

    public function update(array $attributes, array $conditions): bool
    {
        return $this->findFirst($conditions)->update($attributes);
    }

    protected function applySearchQuery($query, $search)
    {
        return $query;
    }

    protected function applyFilters($query, $options)
    {
        return $query;
    }

    protected function applyOrderBy($query, $orderBy, $sort)
    {
        return $query->orderBy($orderBy, $sort);
    }
}
