<?php

return [
    'backup/view' => [
        'file' => 'modules/backup/pages/view.php',
        'layout' => true,
        'permission' => 'backup.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'backup/actions/create' => [
        'file' => 'modules/backup/actions/create.php',
        'layout' => false,
        'permission' => 'backup.create',
        'auth' => true,
        'method' => 'POST',
    ],
    'backup/actions/restore' => [
        'file' => 'modules/backup/actions/restore.php',
        'layout' => false,
        'permission' => 'backup.restore',
        'auth' => true,
        'method' => 'POST',
    ],
];
