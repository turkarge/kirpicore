<?php

return [
    'profile/view' => [
        'file' => 'modules/profile/pages/view.php',
        'layout' => true,
        'permission' => 'profile.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'profile/actions/update' => [
        'file' => 'modules/profile/actions/update.php',
        'layout' => false,
        'permission' => 'profile.edit',
        'auth' => true,
        'method' => 'POST',
    ],
];
