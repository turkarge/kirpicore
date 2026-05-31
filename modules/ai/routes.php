<?php

return [
    'ai/view' => [
        'file' => 'modules/ai/pages/view.php',
        'layout' => true,
        'permission' => 'ai.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'ai/audit' => [
        'file' => 'modules/ai/pages/audit.php',
        'layout' => true,
        'permission' => 'ai.audit.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'ai/actions/sync-schema' => [
        'file' => 'modules/ai/actions/sync_schema.php',
        'layout' => false,
        'permission' => 'ai.schema.manage',
        'auth' => true,
        'method' => 'POST',
    ],
];
