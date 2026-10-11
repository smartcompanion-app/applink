<?php

/**
 * Error page for unknown apps and failures.
 *
 * @var int $status
 * @var string $title
 * @var string $message
 * @var \Closure(string|null): string $e
 */

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title><?= $e($title) ?></title>
    <link rel="stylesheet" href="./style.css">
</head>
<body>
    <main class="card">
        <p class="status"><?= $status ?></p>
        <h1><?= $e($title) ?></h1>
        <p class="description"><?= $e($message) ?></p>
    </main>
</body>
</html>
