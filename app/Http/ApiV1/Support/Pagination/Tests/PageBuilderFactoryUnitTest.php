<?php

namespace App\Http\ApiV1\Support\Pagination\Tests;

use App\Http\ApiV1\OpenApiGenerated\Enums\PaginationTypeEnum;
use App\Http\ApiV1\Support\Pagination\CursorPageBuilder;
use App\Http\ApiV1\Support\Pagination\OffsetPageBuilder;
use App\Http\ApiV1\Support\Pagination\PageBuilderFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Tests\UnitTestCase;

uses(UnitTestCase::class);
uses()->group('unit');

function builder_from_query(?array $requestBody = null)
{
    $request = new Request();
    if ($requestBody) {
        $request->setMethod('POST');
        $request->request->add($requestBody);
    }

    return (new PageBuilderFactory())->fromQuery(resolve(Builder::class), $request);
}

test('PageBuilderFactory uses default type from config by default', function (PaginationTypeEnum $type, $expectedBuilderClassName) {
    config(['pagination.default_type' => $type->value]);

    $pageBuilder = builder_from_query();

    expect($pageBuilder)->toBeInstanceOf($expectedBuilderClassName);
})->with([
    [PaginationTypeEnum::CURSOR, CursorPageBuilder::class],
    [PaginationTypeEnum::OFFSET, OffsetPageBuilder::class],
]);

test('PageBuilderFactory uses type from request instead of default', function () {
    config(['pagination.default_type' => PaginationTypeEnum::CURSOR]);

    $pageBuilder = builder_from_query(['pagination' => ['type' => PaginationTypeEnum::OFFSET->value]]);

    expect($pageBuilder)->toBeInstanceOf(OffsetPageBuilder::class);
});
