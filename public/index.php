<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use SmartCompanion\Application;

(new Application())->handle($_SERVER)->send();
