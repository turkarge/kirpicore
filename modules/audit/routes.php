<?php

return [
    'audit/list' => [
        'file' => 'modules/audit/pages/list.php',
        'layout' => true,
        'permission' => 'audit.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'ajax/audit/table' => [
        'file' => 'modules/audit/partials/table.php',
        'layout' => false,
        'permission' => 'audit.view',
        'auth' => true,
        'method' => 'GET',
    ],
];
