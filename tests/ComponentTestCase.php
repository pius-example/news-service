<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;

class ComponentTestCase extends TestCase
{
    use DatabaseTransactions;
    use MockServicesApi;
}
