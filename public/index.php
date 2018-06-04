<?php
/**
 * Shared Media Tagger
 *  Public Index File
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Loader;

if (is_readable(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    new Loader();

    return;
}

header('HTTP/1.0 500 Internal Server Error');
print 'Error: Vendor Autoloader Not Found';
