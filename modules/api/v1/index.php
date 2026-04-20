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
            'required_scope' => 'profile:read',
        ],
        [
            'method' => 'GET',
            'path' => '/api/v1/users',
            'description' => 'Kullanici listesi (users.view)',
            'required_scope' => 'users:read',
        ],
        [
            'method' => 'POST',
            'path' => '/api/v1/users',
            'description' => 'Kullanici olusturur (users.create)',
            'required_scope' => 'users:create',
        ],
        [
            'method' => 'PATCH',
            'path' => '/api/v1/users/{id}',
            'description' => 'Kullanici gunceller (users.edit)',
            'required_scope' => 'users:update',
        ],
        [
            'method' => 'POST',
            'path' => '/api/v1/users/{id}/status',
            'description' => 'Aktif/pasif durum gunceller (users.status)',
            'required_scope' => 'users:status',
        ],
        [
            'method' => 'GET',
            'path' => '/api/v1/postman-collection',
            'description' => 'Hazir Postman collection dosyasini indirir',
        ],
        [
            'method' => 'GET',
            'path' => '/api/v1/postman',
            'description' => 'Postman collection icin uyumluluk endpointi',
        ],
        [
            'method' => 'GET',
            'path' => '/api/v1/postman-collection.json',
            'description' => 'Postman collection icin uyumluluk endpointi',
        ],
    ],
]);
