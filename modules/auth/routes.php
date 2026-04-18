<?php

return [
    'auth/login' => [
        'file' => 'modules/auth/pages/login.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'GET',
    ],
    'auth/forgot-password' => [
        'file' => 'modules/auth/pages/forgot_password.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'GET',
    ],
    'auth/terms' => [
        'file' => 'modules/auth/pages/terms.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'GET',
    ],
    'auth/lock' => [
        'file' => 'modules/auth/pages/lock.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'GET',
    ],
    'auth/actions/login' => [
        'file' => 'modules/auth/actions/login.php',
        'layout' => false,
        'permission' => null,
        'auth' => false,
        'method' => 'POST',
    ],
    'auth/actions/logout' => [
        'file' => 'modules/auth/actions/logout.php',
        'layout' => false,
        'permission' => null,
        'auth' => true,
        'method' => 'POST',
    ],
];