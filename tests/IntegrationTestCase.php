<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class IntegrationTestCase extends TestCase
{
    use DatabaseTransactions;
    use MockServicesApi;
}
