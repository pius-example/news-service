<?php

namespace App\Http\ApiV1\Support\Pagination\Tests;

use App\Http\ApiV1\Support\Pagination\CursorPageBuilder;
use Illuminate\Http\Request;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tests\UnitTestCase;
use UnexpectedValueException;

uses(UnitTestCase::class);
uses()->group('unit');

function cpb_make_request(?array $requestBody = null): Request
{
    $request = new Request();
    if ($requestBody) {
        $request->setMethod('POST');
        $request->request->add($requestBody);
    }

    return $request;
}

test('CursorPageBuilder build with positive limit', function () {
    $limit = 10;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('cursorPaginate')->andReturn(new CursorPaginator($expectedItems, $limit));
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "cursor" => null,
        "limit" => $limit,
        "next_cursor" => null,
        "previous_cursor" => null,
        "type" => "cursor",
    ]);
});

test('CursorPageBuilder build throws BadRequestHttpException if cursor pagination failed with exception', function () {
    $limit = 10;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $queryBuilderMock->shouldReceive('cursorPaginate')->andThrow(new UnexpectedValueException("test"));
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $builder->build();
})->throws(BadRequestHttpException::class);

test('CursorPageBuilder build throws BadRequestHttpException if request cursor can not be decoded', function () {
    $limit = 10;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
            'cursor' => 'foo',
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $builder->build();
})->throws(BadRequestHttpException::class);

test('CursorPageBuilder build with 0 as limit returns empty array', function () {
    $limit = 0;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray([]);
    expect($page->pagination)->toMatchArray([
        "cursor" => null,
        "limit" => $limit,
        "next_cursor" => null,
        "previous_cursor" => null,
        "type" => "cursor",
    ]);
});

test('CursorPageBuilder build with negative limit', function () {
    $limit = -1;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "cursor" => null,
        "limit" => $limit,
        "next_cursor" => null,
        "previous_cursor" => null,
        "type" => "cursor",
    ]);
});

test('CursorPageBuilder build with negative limit and forbidToBypassPagination=true', function () {
    $limit = -1;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $page = $builder->forbidToBypassPagination()->build();

    expect($page->items)->toMatchArray([]);
    expect($page->pagination)->toMatchArray([
        "cursor" => null,
        "limit" => $limit,
        "next_cursor" => null,
        "previous_cursor" => null,
        "type" => "cursor",
    ]);
});

test('CursorPageBuilder build with positive limit cannot exceed max limit', function () {
    $limit = 10;
    $maxLimit = 5;
    $request = cpb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('cursorPaginate')->andReturn(new CursorPaginator($expectedItems, $maxLimit));
    $builder = new CursorPageBuilder($queryBuilderMock, $request);

    $page = $builder->maxLimit($maxLimit)->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "cursor" => null,
        "limit" => $maxLimit,
        "next_cursor" => null,
        "previous_cursor" => null,
        "type" => "cursor",
    ]);
});
