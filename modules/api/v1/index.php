<?php
if (!defined('KIRPI_CORE_ENTRY')) {
    exit;
}

require_action('GET', false);

api_response(200, 'KirpiCore API v1', [
    'enabled' => api_is_enabled(),
    'endpoints' => [
        [
            'method' => 'POST',
            'path' => '/api/v1/auth/token',
            'description' => 'Email+password ile bearer token alir',
        ],
        [
            'method' => 'GET',
            'path' => '/api/v1/me',
            'description' => 'Token sahibinin profil bilgisi',
        ],
        [
            'method' => 'GET',
            'path' => '/api/v1/users',
            'description' => 'Kullanici listesi (users.view izni)',
        ],
    ],
]);

