<?php

/**
 * Fallback page, shown when the device could not be matched to an app market.
 *
 * @var \SmartCompanion\AppConfiguration $app
 * @var string|null $baseUrl
 * @var string $nonce
 * @var \Closure(string|null): string $e
 */

$iconPath = $app->appIcon !== null ? 'icons/' . rawurlencode($app->appIcon) : null;
$bodyStyle = $app->backgroundColor !== null ? ' style="background: ' . $e($app->backgroundColor) . '"' : '';
?>
<!DOCTYPE html>
<html lang="en" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($app->title) ?></title>
    <meta property="og:title" content="<?= $e($app->title) ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary">
    <?php if ($app->description !== null) : ?>
    <meta name="description" content="<?= $e($app->description) ?>">
    <meta property="og:description" content="<?= $e($app->description) ?>">
    <?php endif; ?>
    <?php if ($baseUrl !== null) : ?>
    <meta property="og:url" content="<?= $e($baseUrl . '/' . $app->identifier) ?>">
        <?php if ($iconPath !== null) : ?>
    <meta property="og:image" content="<?= $e($baseUrl . '/' . $iconPath) ?>">
        <?php endif; ?>
    <?php endif; ?>
    <link rel="stylesheet" href="./style.css">
    <?php if ($iconPath !== null) : ?>
    <link rel="icon" href="./<?= $e($iconPath) ?>">
    <link rel="apple-touch-icon" href="./<?= $e($iconPath) ?>">
    <?php endif; ?>
</head>
<body<?= $bodyStyle ?>>
    <main class="card">
        <?php if ($iconPath !== null) : ?>
        <img class="app-icon" src="./<?= $e($iconPath) ?>" alt="<?= $e($app->title) ?> App Icon">
        <?php endif; ?>
        <h1><?= $e($app->title) ?></h1>
        <?php if ($app->description !== null) : ?>
        <p class="description"><?= $e($app->description) ?></p>
        <?php endif; ?>
        <nav class="badges">
            <?php if (isset($app->links['ios'])) : ?>
            <a class="badge" id="ios-link" href="<?= $e($app->links['ios']) ?>">
                <img src="./assets/app-store-badge.svg" alt="Download on the App Store">
            </a>
            <?php endif; ?>
            <?php if (isset($app->links['android'])) : ?>
            <a class="badge" id="android-link" href="<?= $e($app->links['android']) ?>">
                <img src="./assets/google-play-badge.svg" alt="Get it on Google Play">
            </a>
            <?php endif; ?>
        </nav>
    </main>
    <script nonce="<?= $e($nonce) ?>">
        // iPadOS 13+ identifies as a Mac in the user agent, only touch support gives it away
        var iosLink = document.getElementById('ios-link');
        if (iosLink && /Macintosh/.test(navigator.userAgent) && navigator.maxTouchPoints > 1) {
            window.location.replace(iosLink.href);
        }
    </script>
</body>
</html>
