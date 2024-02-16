<?php

namespace App\Http\ApiV1\Modules\Foos\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class FoosController
{
    public function get()
    {
        throw new ModelNotFoundException('Foo');
    }
}
