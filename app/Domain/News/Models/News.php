<?php

namespace App\Domain\News\Models;

use App\Domain\News\Models\Tests\Factories\NewsFactory;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 *
 * @property string $title Заголовок новости
 * @property string $body Текст новости
 * @property int $counter Счётчик просмотров
 *
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 */
class News extends Model
{
    protected $table = 'news';

    // Поля, которые можно заполнять методом fill
    protected $fillable = ['title', 'body'];

    // Значение по умолчанию
    protected $attributes = [
        'counter' => 0,
    ];

    public static function factory(): NewsFactory
    {
        return NewsFactory::new();
    }
}
