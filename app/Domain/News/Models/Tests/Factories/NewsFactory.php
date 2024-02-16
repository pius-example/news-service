<?php

namespace App\Domain\News\Models\Tests\Factories;

use App\Domain\News\Models\News;
use Ensi\LaravelTestFactories\BaseModelFactory;

class NewsFactory extends BaseModelFactory
{
    protected $model = News::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->title(),
            'body' => $this->faker->text(),
            'counter' => $this->faker->numberBetween(0, 100_000),
        ];
    }
}
