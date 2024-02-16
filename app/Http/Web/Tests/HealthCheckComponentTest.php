<?php

use function Pest\Laravel\get;

use Tests\ComponentTestCase;

uses(ComponentTestCase::class);

test('GET /health 200', function () {
    get('/health')
        ->assertStatus(200)
        ->assertHeader('content-type', 'text/html; charset=UTF-8');
});
