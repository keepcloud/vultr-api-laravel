<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Vultr API V2 endpoint
    |--------------------------------------------------------------------------
    */
    'endpoint' => env('VULTR_ENDPOINT', 'https://api.vultr.com/v2/'),

    /*
    |--------------------------------------------------------------------------
    | Vultr API V2 personal token
    |--------------------------------------------------------------------------
    */
    'token' => env('VULTR_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Timeouts (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('VULTR_TIMEOUT', 30),

    'connect_timeout' => (int) env('VULTR_CONNECT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | Retries
    |--------------------------------------------------------------------------
    |
    | Number of retries after the first attempt and the delay between them
    | (milliseconds). Rate limited requests (429) are retried for every
    | method; server and connection errors only for GET, PUT and DELETE.
    |
    */
    'retries' => (int) env('VULTR_RETRIES', 2),

    'retry_delay' => (int) env('VULTR_RETRY_DELAY', 500),

];
