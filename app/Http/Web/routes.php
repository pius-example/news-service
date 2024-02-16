<?php

use App\Http\Web\Controllers\HealthCheck;
use App\Http\Web\Controllers\OasController;
use Illuminate\Support\Facades\Route;

Route::get('health', HealthCheck::class);

Route::get('/', [OasController::class, 'list']);
