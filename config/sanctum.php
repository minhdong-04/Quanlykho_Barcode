<?php

return [
   

    'guard' => env('SANCTUM_GUARD', 'web'),

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),

    'expiration' => env('SANCTUM_EXPIRATION', null),
];
