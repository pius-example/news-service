<?php

use App\Domain\News\Models\News;
use App\Http\ApiV1\Support\Tests\ApiV1ComponentTestCase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(ApiV1ComponentTestCase::class);
uses()->group('component');

test('POST /api/v1/news/news create success', function () {
    $request = [
        'title' => 'Test title',
        'body' => 'test news body',
    ];

    postJson('/api/v1/news/news', $request)
        ->assertStatus(201)
        ->assertJsonPath('data.title', $request['title'])
        ->assertJsonPath('data.body', $request['body'])
        ->assertJsonPath('data.counter', 0);

    assertDatabaseHas(News::class, [
        'title' => $request['title'],
    ]);
});

test('GET /api/v1/news/news/{id} get news success', function () {
    /** @var News $news */
    $news = News::factory()->create();

    getJson("/api/v1/news/news/{$news->id}")
        ->assertStatus(200)
        ->assertJsonPath('data.title', $news->title)
        ->assertJsonPath('data.body', $news->body)
        ->assertJsonPath('data.counter', $news->counter);
});
