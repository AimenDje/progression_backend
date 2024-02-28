<?
return [
    'secret' => env('JWT_SECRET', null),
    'ttl' => env('JWT_TTL', 300),
    'expiration' => env('JWT_EXPIRATION', 15),
];
