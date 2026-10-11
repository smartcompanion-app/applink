<?php

/**
 * Router for PHP's built-in web server (`composer serve`), mirroring the Caddyfile of the Docker image:
 * static files from `public/`, icons from the storage directory, everything else to `public/index.php`.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SmartCompanion\Configuration;

$path = parse_url(is_string($_SERVER['REQUEST_URI'] ?? null) ? $_SERVER['REQUEST_URI'] : '/', PHP_URL_PATH);
$path = is_string($path) ? rawurldecode($path) : '/';

if ($path !== '/' && is_file(__DIR__ . '/../public' . $path)) {
    return false;
}

if (str_starts_with($path, '/icons/')) {
    $iconDirectory = realpath(Configuration::defaultStoragePath() . '/icons');
    $icon = realpath(Configuration::defaultStoragePath() . $path);
    if ($iconDirectory !== false && $icon !== false && str_starts_with($icon, $iconDirectory . '/') && is_file($icon)) {
        header('Content-Type: ' . (mime_content_type($icon) ?: 'application/octet-stream'));
        readfile($icon);
        return true;
    }
}

require __DIR__ . '/../public/index.php';
