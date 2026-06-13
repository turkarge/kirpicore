<?php

$root = dirname(__DIR__);
$header = file_get_contents($root . '/layouts/header.php');
$footer = file_get_contents($root . '/layouts/footer.php');
$script = file_get_contents($root . '/assets/js/kirpi-table.js');

foreach ([
    'vendor/datatables/css/dataTables.bootstrap5.min.css',
    'css/kirpi-table.css',
] as $asset) {
    if (!str_contains((string) $header, $asset)) {
        fwrite(STDERR, "Global KirpiTable style is missing: {$asset}.\n");
        exit(1);
    }
}

foreach ([
    'vendor/datatables/js/dataTables.min.js',
    'vendor/datatables/js/dataTables.responsive.min.js',
    'js/kirpi-table.js',
] as $asset) {
    if (!str_contains((string) $footer, $asset)) {
        fwrite(STDERR, "Global KirpiTable script is missing: {$asset}.\n");
        exit(1);
    }
}

foreach (['standard', 'report', 'compact', 'matrix'] as $profile) {
    if (!str_contains((string) $script, "profile === \"{$profile}\"") && $profile !== 'standard') {
        fwrite(STDERR, "KirpiTable profile is missing: {$profile}.\n");
        exit(1);
    }
}

foreach (['MutationObserver', 'data-kirpi-table', 'tbody td[colspan]', 'serverSide: false'] as $token) {
    if (!str_contains((string) $script, $token)) {
        fwrite(STDERR, "KirpiTable standard contract is missing {$token}.\n");
        exit(1);
    }
}

$pageFiles = glob($root . '/modules/*/pages/*.php') ?: [];
$unmarked = [];
foreach ($pageFiles as $file) {
    $source = file_get_contents($file);
    if (!str_contains((string) $source, '<table')) {
        continue;
    }
    if (str_ends_with(str_replace('\\', '/', $file), '/modules/users/pages/view.php')) {
        continue;
    }
    if (preg_match_all('/<table\b[^>]*>/', (string) $source, $matches) === false) {
        continue;
    }
    foreach ($matches[0] as $tableTag) {
        if (!str_contains($tableTag, 'data-kirpi-table=')) {
            $unmarked[] = str_replace($root . DIRECTORY_SEPARATOR, '', $file);
        }
    }
}

if ($unmarked) {
    fwrite(STDERR, "Unmarked Core page tables: " . implode(', ', array_unique($unmarked)) . "\n");
    exit(1);
}

fwrite(STDOUT, "KirpiTable standard tests passed.\n");
