<?php

return [
    'default_bag' => 'default',
    'enabled' => env('PROMETHEUS_ENABLED', false),
    'bags' => [
        'default' => [
            'namespace' => env('PROMETHEUS_NAMESPACE', 'app'),
            'route' => 'metrics',
            'basic_auth' => [
                'login' => env('PROMETHEUS_AUTH_LOGIN'),
                'password' => env('PROMETHEUS_AUTH_PASSWORD'),
            ],
            'connection' => [
                'connection' => 'metrics',
                'bag' => 'default',
            ],
            'label_middlewares' => [
                \Ensi\LaravelPrometheus\LabelMiddlewares\AppNameLabelMiddleware::class,
            ],
            'on_demand_metrics' => [

            ],
        ],
    ],
];
