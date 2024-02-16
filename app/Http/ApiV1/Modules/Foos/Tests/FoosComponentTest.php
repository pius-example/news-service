<?php

use App\Http\ApiV1\Support\Tests\ApiV1ComponentTestCase;

use function Pest\Laravel\getJson;

uses(ApiV1ComponentTestCase::class);
uses()->group('component');

test('GET /api/v1/examples/{id} 404', function () {
    getJson('/api/v1/examples/1')
        ->assertStatus(404);
});
