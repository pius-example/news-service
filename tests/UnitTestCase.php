<?php

namespace Tests;

use Illuminate\Database\Query\Builder;
use Mockery\MockInterface;

class UnitTestCase extends TestCase
{
    protected function mockQueryBuilder(): MockInterface|Builder
    {
        return $this->mock(Builder::class);
    }
}
