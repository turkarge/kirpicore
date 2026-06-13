<?php

$root = dirname(__DIR__);
$compose = file_get_contents($root . '/docker-compose.yml');
$localCompose = file_get_contents($root . '/docker-compose.local.yml');
$config = file_get_contents($root . '/core/config.php');
$validator = file_get_contents($root . '/scripts/validate-deployment.ps1');

$assertions = [
    'compose project prefix' => str_contains($compose, 'name: ${KIRPI_APP_PREFIX:-kirpicore}'),
    'network prefix' => str_contains($compose, 'KIRPI_NETWORK_NAME'),
    'database volume override' => str_contains($compose, 'KIRPI_DB_VOLUME_NAME'),
    'uploads volume override' => str_contains($compose, 'KIRPI_UPLOADS_VOLUME_NAME'),
    'logs volume override' => str_contains($compose, 'KIRPI_LOGS_VOLUME_NAME'),
    'no fixed container name' => !str_contains($compose, 'container_name:'),
    'session cookie env' => str_contains($compose, 'SESSION_COOKIE_NAME'),
    'session prefix isolation' => str_contains($config, "env('KIRPI_APP_PREFIX', 'kirpicore')"),
    'session cookie override' => str_contains($config, "env('SESSION_COOKIE_NAME', \$defaultSessionCookieName)"),
    'production compose has no host ports' => !preg_match('/^\s+ports:/m', $compose),
    'local http port override' => str_contains($localCompose, 'KIRPI_APP_HTTP_PORT'),
    'local database port override' => str_contains($localCompose, 'KIRPI_DB_HOST_PORT'),
    'dual instance validator' => str_contains($validator, 'Compose proje adlari ayrismadi')
        && str_contains($validator, 'Volume adlari ayrismadi'),
];

$failed = array_keys(array_filter($assertions, static fn (bool $passed): bool => !$passed));
if ($failed !== []) {
    fwrite(STDERR, 'Deployment standard contract failed: ' . implode(', ', $failed) . PHP_EOL);
    exit(1);
}

echo 'Deployment standard contract passed (' . count($assertions) . ' assertions).' . PHP_EOL;
