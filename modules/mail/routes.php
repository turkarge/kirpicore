<?php

return [
    'mail/test' => [
        'file' => 'modules/mail/pages/test.php',
        'layout' => true,
        'permission' => 'mail.view',
        'auth' => true,
        'method' => 'GET',
    ],
    'mail/actions/send-test' => [
        'file' => 'modules/mail/actions/send_test.php',
        'layout' => false,
        'permission' => 'mail.test',
        'auth' => true,
        'method' => 'POST',
    ],
];
