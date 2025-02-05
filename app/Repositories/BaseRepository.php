<?php

namespace App\Repositories;

use App\Contracts\BaseRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    public int $perPage = 10;

    protected $query;

    protected string $orderBy = 'updated_at';
    protected string $sort = 'DESC';

    public abstract function getModel();

    /**
     * @return mixed
     */
    public function getQuery(): mixed
    {
        if(!$this->query) return $this->setQuery();
        return $this->query;
    }

    public function setQuery()
    {
        return $this->getModel()->newQuery();
    }

    public function findById($id, array $with = []): ?Model
    {
        return $this->getModel()->find($id);
    }

    public function findFirst($conditions, $with = []): ?Model
    {
        return $this->getModel()->where($conditions)->with($with)->first();
    }

    public function getPaginated(
        $search = null,
        $filters = []
    ): LengthAwarePaginator
    {
        $this->query = $this->getQuery();
        $this->applyFilters($filters);
        $this->applySearchQuery($search);
        $this->applyOrderBy();
        $page = $filters['page'] ?? null; // Extract the page from filters if provided

        return $this->query->paginate($this->perPage, ['*'], 'page', $page)->withQueryString();
    }

    public function create($attributes): Model
    {
        return $this->getModel()->create($attributes);
    }

    public function update(array $attributes, array $conditions): bool
    {
        return $this->findFirst($conditions)->update($attributes);
    }

    protected function applySearchQuery($search)
    {
        return $this->query;
    }

    protected function applyFilters($options)
    {
        return $this->query;
    }

    protected function applyOrderBy()
    {
        return $this->query->orderBy($this->orderBy, $this->sort);
    }
}
