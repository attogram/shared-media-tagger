<?php
/**
 * Shared Media Tagger - Public Index File
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Loader;

if (is_readable('config.php')) {
    include('config.php');
}

if (empty($config) || !is_array($config)) {
    /** @var array $config */
    $config = [];
}

$config['publicDirectory'] = realpath(__DIR__);

if (empty($config['autoload'])) {
    $config['autoload'] = '../vendor/autoload.php';
}

if (!is_readable($config['autoload'])) {
    header('HTTP/1.0 500 Internal Server Error');
    print 'Internal Server Error: Vendor Autoload File Not Found';

    return;
}

/** @noinspection PhpIncludeInspection */
require_once $config['autoload'];

new Loader($config);
