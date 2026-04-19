<?php

return [
    'audit/list' => [
        'file' => 'modules/audit/pages/list.php',
        'layout' => true,
        'permission' => 'audit.view',
        'auth' => true,
        'method' => 'GET',
    ],
];
