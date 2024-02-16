<?php

namespace App\Http\ApiV1\Support\Pagination\Tests;

use App\Http\ApiV1\Support\Pagination\OffsetPageBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Tests\UnitTestCase;

uses(UnitTestCase::class);
uses()->group('unit');

function ofb_make_request(?array $requestBody = null): Request
{
    $request = new Request();
    if ($requestBody) {
        $request->setMethod('POST');
        $request->request->add($requestBody);
    }

    return $request;
}

test('OffsetPageBuilder build with positive limit', function () {
    $limit = 10;
    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('skip')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('limit')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "offset" => 0,
        "limit" => $limit,
        "total" => 4,
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build needs another count query if there is more items possibly', function () {
    $limit = 4;
    $total = 12;
    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('skip')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('limit')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $queryBuilderMock->shouldReceive('getQuery')->andReturn((new Builder($queryBuilderMock))->getQuery());
    $queryBuilderMock->shouldReceive('count')->andReturn($total);
    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "offset" => 0,
        "limit" => $limit,
        "total" => $total,
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build with 0 as limit returns empty array', function () {
    $limit = 0;
    $total = 12;
    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('getQuery')->andReturn((new Builder($queryBuilderMock))->getQuery());
    $queryBuilderMock->shouldReceive('count')->andReturn($total);
    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray([]);
    expect($page->pagination)->toMatchArray([
        "offset" => 0,
        "limit" => $limit,
        "total" => $total,
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build with negative limit', function () {
    $limit = -1;
    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "offset" => 0,
        "limit" => $limit,
        "total" => count($expectedItems),
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build with negative limit and forbidToBypassPagination=true', function () {
    $limit = -1;
    $total = 12;
    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('getQuery')->andReturn((new Builder($queryBuilderMock))->getQuery());
    $queryBuilderMock->shouldReceive('count')->andReturn($total);
    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->forbidToBypassPagination()->build();

    expect($page->items)->toMatchArray([]);
    expect($page->pagination)->toMatchArray([
        "offset" => 0,
        "limit" => $limit,
        "total" => $total,
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build with positive limit cannot exceed max limit', function () {
    $limit = 10;
    $maxLimit = 5;
    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3, 4];
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('skip')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('limit')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));

    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->maxLimit($maxLimit)->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "offset" => 0,
        "limit" => $maxLimit,
        "total" => count($expectedItems),
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build with offset cannot affects total', function () {
    $limit = 10;
    $offset = 5;
    $realTotal = 3;

    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [];
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('getQuery')->andReturn((new Builder($queryBuilderMock))->getQuery());
    $queryBuilderMock->shouldReceive('skip')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('limit')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $queryBuilderMock->shouldReceive('count')->once()->andReturn($realTotal);

    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "limit" => $limit,
        "offset" => $offset,
        "total" => $realTotal,
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build count is not execute on empty output', function () {
    $limit = 10;
    $offset = 0;

    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [];
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('skip')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('limit')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $queryBuilderMock->shouldReceive('count')->never();

    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "limit" => $limit,
        "offset" => $offset,
        "total" => count($expectedItems),
        "type" => "offset",
    ]);
});

test('OffsetPageBuilder build count is not execute for last page', function () {
    $limit = 10;
    $offset = 0;

    $request = ofb_make_request([
        'pagination' => [
            'limit' => $limit,
            'offset' => $offset,
        ],
    ]);
    $queryBuilderMock = $this->mockQueryBuilder();
    $expectedItems = [1, 2, 3];
    $queryBuilderMock->shouldReceive('clone')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('skip')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('limit')->andReturn($queryBuilderMock);
    $queryBuilderMock->shouldReceive('get')->andReturn(new Collection($expectedItems));
    $queryBuilderMock->shouldReceive('count')->never();

    $builder = new OffsetPageBuilder($queryBuilderMock, $request);

    $page = $builder->build();

    expect($page->items)->toMatchArray($expectedItems);
    expect($page->pagination)->toMatchArray([
        "limit" => $limit,
        "offset" => $offset,
        "total" => count($expectedItems),
        "type" => "offset",
    ]);
});
