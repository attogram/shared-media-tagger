<?php
/**
 * Shared Media Tagger
 *
 *  Router
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Loader;
use Attogram\SharedMedia\Tagger\Tools;

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_readable($autoload)) {
    Tools::error500('Vendor Autoloader Not Found');
}
/** @noinspection PhpIncludeInspection */
require_once $autoload;

$loader = new Loader();
