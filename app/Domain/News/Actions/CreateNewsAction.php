<?php

namespace App\Domain\News\Actions;

use App\Domain\News\Models\News;

class CreateNewsAction
{
    public function execute(array $fields): News
    {
        $news = new News();
        $news->fill($fields);
        $news->save();

        return $news;
    }
}
