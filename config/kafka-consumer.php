<?php

use Ensi\LaravelInitialEventPropagation\RdKafkaConsumerMiddleware;
use Ensi\LaravelMetrics\Kafka\KafkaMetricsMiddleware;

return [
    /*
    | Optional, defaults to empty array.
    | Array of global middleware fully qualified class names.
    */
    'global_middleware' => [ RdKafkaConsumerMiddleware::class, KafkaMetricsMiddleware::class ],
    'stop_signals' => [SIGTERM, SIGINT],

    'processors' => [
        // [
        //     'topic' => 'foobars',
        //     'consumer' => 'default',
        //     'type' => 'action',
        //     'class' => \App\Domain\Kafka\Actions\Listen\FoobarsListenAction::class,
        //     'queue' => false,
        //     'consume_timeout' => 5000,
        // ],
    ],

    'consumer_options' => [
        /** options for consumer with name `default` */
        'default' => [
            /*
            | Optional, defaults to 20000.
            | Kafka consume timeout in milliseconds.
            */
            'consume_timeout' => 20000,

            /*
            | Optional, defaults to empty array.
            | Array of middleware fully qualified class names for this specific consumer.
            */
            'middleware' => [],
        ],
    ],
];
