<?php

return [
    'ignore_commands' => [
        'kafka:consume',
        'queue:work',
    ],
    'ignore_routes' => [
        'prometheus.*',
        'serve-stoplight.*',
        'serve-swagger.*',
        'ignition.*',
    ],
    'http_requests_stats_groups' => [
        'default' => [
            'type' => 'summary',
            'route_names' => ['*'],
            'time_window' => 30,
            'quantiles' => [0.5, 0.75, 0.95],
        ],
    ],
    'watch_queues' => [
        'default',
    ],
];
