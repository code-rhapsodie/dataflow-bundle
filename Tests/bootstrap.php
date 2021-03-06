<?php

// Skip autoloading if already done by phpunit alias (including from meta repo if this is vendor)
if (defined('PHPUNIT_COMPOSER_INSTALL')) {
    return;
}
$autoloadFile = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}
require_once $autoloadFile;
