<?php

$root = dirname(__DIR__);
$requiredAssets = [
    'assets/vendor/datatables/js/dataTables.min.js',
    'assets/vendor/datatables/js/dataTables.bootstrap5.min.js',
    'assets/vendor/datatables/js/dataTables.buttons.min.js',
    'assets/vendor/datatables/js/dataTables.responsive.min.js',
    'assets/vendor/datatables/js/dataTables.select.min.js',
    'assets/vendor/datatables/js/dataTables.colReorder.min.js',
    'assets/vendor/datatables/js/dataTables.fixedHeader.min.js',
    'assets/vendor/datatables/js/dataTables.keyTable.min.js',
    'assets/js/kirpi-table.js',
    'assets/css/kirpi-table.css',
];

foreach ($requiredAssets as $asset) {
    $path = $root . '/' . $asset;
    if (!is_file($path) || filesize($path) === 0) {
        fwrite(STDERR, "Missing DataTables asset: {$asset}\n");
        exit(1);
    }
}

$routeSource = file_get_contents($root . '/modules/users/routes.php');
$endpointSource = file_get_contents($root . '/modules/users/actions/datatable.php');
$scriptSource = file_get_contents($root . '/modules/users/scripts/view.js');

if (!str_contains((string) $routeSource, "'ajax/users/datatable'")) {
    fwrite(STDERR, "Users DataTables route is missing.\n");
    exit(1);
}

foreach (['recordsTotal', 'recordsFiltered', "'data' =>", ':global_name', ':global_email', ':global_role'] as $token) {
    if (!str_contains((string) $endpointSource, $token)) {
        fwrite(STDERR, "Users DataTables response contract is missing {$token}.\n");
        exit(1);
    }
}

foreach (['DataTable.render.select()', 'columnFilters', 'serverExport', 'stateKey'] as $token) {
    if (!str_contains((string) $scriptSource, $token)) {
        fwrite(STDERR, "Users table configuration is missing {$token}.\n");
        exit(1);
    }
}

fwrite(STDOUT, "Users DataTables contract tests passed.\n");
