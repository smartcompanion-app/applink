<?php

require_once __DIR__ . '/../vendor/autoload.php';

use SmartCompanion\Configuration;
use SmartCompanion\AppLink;
use SmartCompanion\Uri;

$configuration = new Configuration();
$appLink = new AppLink();
$uri = new Uri();

try {
    $app = $uri->getApp();

    if ($configuration->hasAppConfiguration($app)) {
        $appConfiguration = $configuration->readAppConfiguration($app);
    }
    if (
        isset($appConfiguration) &&
        !$appLink->tryRedirectToApp($appConfiguration['links'], $_SERVER['HTTP_USER_AGENT'])
    ) {
        $page = $appConfiguration['page'];
        $links = $appConfiguration['links'];
        include('../src/template.html.php');
    }
} catch (\Exception $e) {
}
