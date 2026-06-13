<?php

$root = dirname(__DIR__);
$nav = file_get_contents($root . '/layouts/nav.php');
$app = file_get_contents($root . '/assets/js/app.js');
$list = file_get_contents($root . '/modules/notifications/scripts/list.js');
$markRead = file_get_contents($root . '/modules/notifications/actions/mark_read.php');
$markAllRead = file_get_contents($root . '/modules/notifications/actions/mark_all_read.php');
$css = file_get_contents($root . '/assets/css/app.css');

$assertions = [
    'numeric unread badge' => str_contains($nav, 'js-notification-count'),
    'single read control' => str_contains($nav, 'js-notification-mark-read'),
    'mark all control' => str_contains($nav, 'js-notification-mark-all'),
    'notification summary' => str_contains($nav, 'js-notification-summary'),
    'responsive shared dropdown' => str_contains($nav, 'nav-item dropdown d-flex me-3')
        && !str_contains($nav, 'nav-item d-md-none me-3'),
    'server unread count' => str_contains($markRead, "'unread_count' => \$unreadCount"),
    'server mark all count' => str_contains($markAllRead, "'unread_count' => 0"),
    'navbar read lifecycle' => str_contains($app, 'markNotificationItemAsRead')
        && str_contains($app, 'js-notification-mark-all'),
    'list direct action' => str_contains($list, 'js-notification-read')
        && !str_contains($list, 'js-kirpi-row-menu'),
    'theme aware notification styles' => str_contains($css, '.kirpi-notification-menu')
        && str_contains($css, 'var(--kirpi-surface-strong)'),
];

$failed = array_keys(array_filter($assertions, static fn (bool $passed): bool => !$passed));
if ($failed !== []) {
    fwrite(STDERR, 'Notification center contract failed: ' . implode(', ', $failed) . PHP_EOL);
    exit(1);
}

echo 'Notification center contract passed (' . count($assertions) . ' assertions).' . PHP_EOL;
