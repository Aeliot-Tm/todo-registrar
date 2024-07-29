<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Load the PHPUnit autoloader from the Phar archive
Phar::loadPhar(__DIR__.'/../tools/phpunit.phar', 'phpunit-11.2.8.phar');

require_once 'phar://phpunit-11.2.8.phar/vendor/autoload.php';
