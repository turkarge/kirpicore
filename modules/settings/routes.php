<?php

return [
    'settings/view' => [
        'file' => 'modules/settings/pages/view.php',
        'layout' => true,
        'permission' => 'settings.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'settings/actions/update' => [
        'file' => 'modules/settings/actions/update.php',
        'layout' => false,
        'permission' => 'settings.update',
        'auth' => true,
        'method' => 'POST',
    ],
];
