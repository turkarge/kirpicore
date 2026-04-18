<?php

$routes = require BASE_PATH . '/core/system_routes.php';

$modulesPath = BASE_PATH . '/modules';
$moduleDirs = glob($modulesPath . '/*', GLOB_ONLYDIR) ?: [];

foreach ($moduleDirs as $moduleDir) {
    $moduleRouteFile = $moduleDir . '/routes.php';

    if (!is_file($moduleRouteFile)) {
        continue;
    }

    $moduleRoutes = require $moduleRouteFile;

    if (!is_array($moduleRoutes)) {
        continue;
    }

    foreach ($moduleRoutes as $routeKey => $routeDefinition) {
        if (isset($routes[$routeKey])) {
            throw new RuntimeException("Çakışan rota bulundu: {$routeKey}");
        }

        $routes[$routeKey] = $routeDefinition;
    }
}