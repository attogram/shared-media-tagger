<?php
/**
 * Shared Media Tagger
 *
 *  Router
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Loader;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_readable($autoload)) {
    header('HTTP/1.0 500 Internal Server Error');
    print 'Error: Vendor Autoloader Not Found';

    return;
}
/** @noinspection PhpIncludeInspection */
require_once $autoload;

$loader = new Loader();
