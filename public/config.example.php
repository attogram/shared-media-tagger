<?php
declare(strict_types = 1);

/** @var array $config - Site Configuration Overrides */
$config = [];

/** Path to Composer autoload file */
$config['autoload'] = '../vendor/autoload.php';

/** Path to the public directory, with NO tailing slash */
$config['publicDirectory'] = '../public';

/** Path to source directory, with NO trailing slash */
$config['sourceDirectory'] = '../src';

/** Path to database directory, with NO trailing slash  */
$config['databaseDirectory'] = '../db';

/** Default Medium width for media, in pixels */
$config['sizeMedium'] = 325;

/** Default Thumb width for media, in pixels */
$config['sizeThumb'] = 100;

/** Backend Admin configuration file - contains passwords, put in a safe place */
$config['adminConfigFile'] = './config.admin.php';
