<?php

use Ensi\LaravelInitialEventPropagation\RdKafkaProducerMiddleware;

return [
   'global_middleware' => [
      RdKafkaProducerMiddleware::class,
   ],
];
