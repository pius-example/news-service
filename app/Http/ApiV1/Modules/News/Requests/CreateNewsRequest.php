<?php

namespace App\Http\ApiV1\Modules\News\Requests;

use App\Http\ApiV1\Support\Requests\BaseFormRequest;

class CreateNewsRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string'],
            'title' => ['required', 'string'],
        ];
    }
}
