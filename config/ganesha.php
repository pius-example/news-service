<?php

return [
    /*
    | Disable ganesha middleware
    */
    'disable_middleware' => env('GANESHA_DISABLE_MIDDLEWARE', false),

    /*
    | The interval in time (seconds) that evaluate the thresholds.
    */
    'time_window' => env('GANESHA_TIME_WINDOW', 30),

    /*
    | The failure rate threshold in percentage that changes CircuitBreaker's state to `OPEN`.
    */
    'failure_rate_threshold' => env('GANESHA_FAILED_RATE_THRESHOLD', 20),

    /*
    | The minimum number of requests to detect failures.
    | Even if `failureRateThreshold` exceeds the threshold,
    | CircuitBreaker remains in `CLOSED` if `minimumRequests` is below this threshold.
    */
    'minimum_requests' => env('GANESHA_MINIMUM_REQUESTS', 10),

    /*
    | The interval (seconds) to change CircuitBreaker's state from `OPEN` to `HALF_OPEN`.
    */
    'interval_to_half_open' => env('GANESHA_INTERVAL_TO_HALF_OPEN', 5),
];
