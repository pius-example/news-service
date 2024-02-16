<?php

namespace App\Http\ApiV1\Modules\News\Controllers;

use App\Domain\News\Actions\CreateNewsAction;
use App\Http\ApiV1\Modules\News\Queries\NewsQuery;
use App\Http\ApiV1\Modules\News\Requests\CreateNewsRequest;
use App\Http\ApiV1\Modules\News\Resources\NewsResource;

class NewsController
{
    public function create(CreateNewsRequest $request, CreateNewsAction $action): NewsResource
    {
        return new NewsResource($action->execute($request->validated()));
    }

    public function get(int $id, NewsQuery $query): NewsResource
    {
        return new NewsResource($query->findOrFail($id));
    }
}
