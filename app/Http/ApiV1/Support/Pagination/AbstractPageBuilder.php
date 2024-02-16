<?php

namespace App\Http\ApiV1\Support\Pagination;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder as SpatieQueryBuilder;

abstract class AbstractPageBuilder
{
    protected bool $forbidToBypassPagination = false;

    protected ?int $maxLimit = null;

    public function __construct(protected Builder|SpatieQueryBuilder $query, protected Request $request)
    {
    }

    abstract public function build(): Page;

    public function forbidToBypassPagination(bool $value = true): static
    {
        $this->forbidToBypassPagination = $value;

        return $this;
    }

    public function maxLimit(?int $maxLimit): static
    {
        $this->maxLimit = $maxLimit;

        return $this;
    }

    protected function applyMaxLimit(int $limit): int
    {
        return $this->maxLimit !== null && $this->maxLimit > 0 ? min($limit, $this->maxLimit) : $limit;
    }

    protected function getDefaultLimit(): int
    {
        return config('pagination.default_limit');
    }

    protected function count(Builder|SpatieQueryBuilder $query): int
    {
        $queryClone = $query->clone();
        $queryClone->getQuery()->orders = null;

        if ($queryClone->getQuery()->groups) {
            $emptyQuery = new EloquentBuilder($queryClone->getConnection()->query());
            $emptyQuery->setBindings($queryClone->getBindings());

            return $emptyQuery->fromRaw("({$queryClone->toSql()}) as count")->count();
        };

        return $queryClone->count();
    }
}
