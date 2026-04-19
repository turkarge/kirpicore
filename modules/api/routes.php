<?php

return [
    'api/v1/auth/token' => [
        'file' => 'modules/api/v1/auth/token.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'POST',
    ],
    'api/v1/me' => [
        'file' => 'modules/api/v1/me.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'GET',
    ],
    'api/v1/users' => [
        'file' => 'modules/api/v1/users/index.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'GET',
    ],
];

