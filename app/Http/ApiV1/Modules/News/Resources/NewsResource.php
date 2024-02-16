<?php

namespace App\Http\ApiV1\Modules\News\Resources;

use App\Domain\News\Models\News;
use App\Http\ApiV1\Support\Resources\BaseJsonResource;

/**
 * @mixin News
 */
class NewsResource extends BaseJsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'counter' => $this->counter,
            'updated_at' => $this->updated_at,
            'created_at' => $this->created_at,
        ];
    }
}
