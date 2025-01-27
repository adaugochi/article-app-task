<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface BaseRepositoryInterface
{
    /**
     * Find a record by its ID.
     *
     * @param $id
     * @param array $with
     * @return Model|null
     */
    public function findById($id, array $with = []): ?Model;

    /**
     * Find the first record matching the conditions.
     *
     * @param array $conditions
     * @param array $with
     * @return Model|null
     */
    public function findFirst(array $conditions, array $with = []): ?Model;

    /**
     * Retrieve paginated records that match the conditions.
     *
     * @param array $conditions
     * @param array $with
     * @param string|null $search
     * @param array $filters
     * @param string $orderBy
     * @param string $sort
     * @return LengthAwarePaginator
     */
    public function getPaginated(
        array $conditions = [],
        array $with = [],
        string $search = null,
        array $filters = [],
        string $orderBy = 'updated_at',
        string $sort = 'DESC'): LengthAwarePaginator;

    /**
     * Insert a new record
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing record based on specific conditions
     *
     * @param array $attributes
     * @param array $conditions
     * @return bool
     */
    public function update(array $attributes, array $conditions): bool;

}
