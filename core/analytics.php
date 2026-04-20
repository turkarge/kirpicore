<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

/**
 * GOMULU GA4 ID
 * Buraya kendi GA4 Measurement ID degerinizi girin.
 * Ornek: G-ABC123XYZ9
 */
const KIRPI_GA4_MEASUREMENT_ID = 'G-S204QY6L4V';

function kirpi_analytics_enabled(): bool
{
    $id = trim((string) KIRPI_GA4_MEASUREMENT_ID);

    if ($id === '' || $id === 'G-S204QY6L4V') {
        return false;
    }

    return true;
}

function kirpi_analytics_snippet(): string
{
    if (!kirpi_analytics_enabled()) {
        return '';
    }

    $id = htmlspecialchars((string) KIRPI_GA4_MEASUREMENT_ID, ENT_QUOTES, 'UTF-8');

    return '<script async src="https://www.googletagmanager.com/gtag/js?id=' . $id . '"></script>'
        . '<script>'
        . 'window.dataLayer=window.dataLayer||[];'
        . 'function gtag(){dataLayer.push(arguments);}'
        . 'gtag("js",new Date());'
        . 'gtag("config","' . $id . '",{send_page_view:true});'
        . '</script>';
}
