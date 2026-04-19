<?php

return [
    'security/view' => [
        'file' => 'modules/security/pages/view.php',
        'layout' => true,
        'permission' => 'security.view',
        'auth' => true,
        'method' => 'GET',
    ],
];
