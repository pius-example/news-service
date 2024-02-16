<?php

use App\Http\ApiV1\Support\Tests\ApiV1ComponentTestCase;

uses(ApiV1ComponentTestCase::class);

test('GET /api/v1/not-existing-resource returns correct error response', function () {
    $this
        ->skipNextOpenApiValidation()
        ->getJson('/api/v1/not-existing-resource')
        ->assertStatus(404)
        ->assertJsonPath('data', null)
        ->assertJsonPath('errors.0.code', "NotFoundHttpException");
});
