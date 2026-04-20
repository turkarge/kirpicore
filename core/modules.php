<?php

function kirpi_module_manifest_defaults(string $moduleKey): array
{
    $moduleKey = trim($moduleKey);

    return [
        'key' => $moduleKey,
        'name' => ucfirst($moduleKey),
        'description' => '',
        'version' => '1.0.0',
        'enabled' => true,
        'core' => true,
        'load_order' => 100,
        'requires' => [],
        'author' => 'Kirpi Core',
    ];
}

function kirpi_load_module_manifest(string $moduleDir): array
{
    $moduleKey = basename($moduleDir);
    $manifest = kirpi_module_manifest_defaults($moduleKey);
    $manifestPath = rtrim($moduleDir, '/\\') . '/module.json';

    if (!is_file($manifestPath)) {
        $manifest['_source'] = 'defaults';
        return $manifest;
    }

    $raw = (string) file_get_contents($manifestPath);
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $manifest['_source'] = 'defaults_invalid_json';
        return $manifest;
    }

    $merged = array_merge($manifest, $decoded);
    $merged['key'] = trim((string) ($merged['key'] ?? $moduleKey)) ?: $moduleKey;
    $merged['name'] = trim((string) ($merged['name'] ?? ucfirst($moduleKey))) ?: ucfirst($moduleKey);
    $merged['version'] = trim((string) ($merged['version'] ?? '1.0.0')) ?: '1.0.0';
    $merged['description'] = trim((string) ($merged['description'] ?? ''));
    $merged['enabled'] = filter_var($merged['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($merged['enabled'] === null) {
        $merged['enabled'] = true;
    }
    $merged['core'] = filter_var($merged['core'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    if ($merged['core'] === null) {
        $merged['core'] = true;
    }
    $merged['load_order'] = max(0, (int) ($merged['load_order'] ?? 100));
    $merged['requires'] = is_array($merged['requires'] ?? null) ? array_values($merged['requires']) : [];
    $merged['author'] = trim((string) ($merged['author'] ?? 'Kirpi Core')) ?: 'Kirpi Core';
    $merged['_source'] = 'module.json';

    return $merged;
}

function kirpi_list_modules(): array
{
    $modulesPath = BASE_PATH . '/modules';
    $moduleDirs = glob($modulesPath . '/*', GLOB_ONLYDIR) ?: [];

    $modules = [];
    foreach ($moduleDirs as $moduleDir) {
        $manifest = kirpi_load_module_manifest($moduleDir);
        $manifest['_dir'] = $moduleDir;
        $modules[] = $manifest;
    }

    usort($modules, static function (array $a, array $b): int {
        $orderCompare = ((int) ($a['load_order'] ?? 100)) <=> ((int) ($b['load_order'] ?? 100));
        if ($orderCompare !== 0) {
            return $orderCompare;
        }

        return strcmp((string) ($a['key'] ?? ''), (string) ($b['key'] ?? ''));
    });

    return $modules;
}
