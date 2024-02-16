<?php

namespace App\Http\ApiV1\Support\Pagination;

use App\Http\ApiV1\OpenApiGenerated\Enums\PaginationTypeEnum;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder as SpatieQueryBuilder;

class PageBuilderFactory
{
    public function fromQuery(Builder|SpatieQueryBuilder $query, ?Request $request = null): AbstractPageBuilder
    {
        $request = $request ?: resolve(Request::class);

        return $request->input('pagination.type', config('pagination.default_type')) === PaginationTypeEnum::CURSOR->value
            ? new CursorPageBuilder($query, $request)
            : new OffsetPageBuilder($query, $request);
    }
}
