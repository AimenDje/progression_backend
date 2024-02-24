<?
return [
    'local' => env('AUTH_LOCAL', true),
    'ldap' => env('AUTH_LDAP', false),
    'key' => [
        'ttl' => env("AUTHKEY_TTL", 2592000)
    ]
];
