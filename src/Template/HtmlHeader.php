<?php
/**
 * Shared Media Tagger
 * Html Header Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?><!doctype html>
<html>
<head>
<title><?= $this->title ?></title>
<meta charset="utf-8" />
<meta name="viewport" content="initial-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?php if ($this->useBootstrap) { ?>
<link rel="stylesheet" href="<?=  Tools::url('bootstrap_css') ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
<?php } ?>
<?php if ($this->useBootstrap || $this->useJquery) { ?>
<script src="<?= Tools::url('jquery') ?>"></script>
<?php } ?>
<?php if ($this->useBootstrap) { ?>
<script src="<?= Tools::url('bootstrap_js') ?>"></script>
<?php } ?>
<link rel="stylesheet" type="text/css" href="<?= Tools::url('css') ?>" />
<link rel="icon" type="image/png" href="<?= Tools::url('home') ?>favicon.ico" />
</head>
<body>
<?= $this->customSiteHeader ?>
