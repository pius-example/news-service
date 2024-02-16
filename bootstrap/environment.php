<?php

use Illuminate\Support\Env;

// load extra base_path('../.env') file but do not override anything from main .env.
$app->afterLoadingEnvironment(function () use ($app) {
    $path = __DIR__ . '/../../';
    if (file_exists($path . '.env') && !$app->configurationIsCached()) {
        Dotenv\Dotenv::create(Env::getRepository(), $path, '.env')->safeLoad();
    }
});
