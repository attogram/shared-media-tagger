<?php
/**
 * Shared Media Tagger
 * Html Header Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="<?= Tools::url('css') ?>" />
<title><?= $this->title ?></title>
<link rel="icon" type="image/png" href="<?= Tools::url('home') ?>favicon.ico" />
</head>
<body>
<div class="container-fluid black">
<?= $this->customSiteHeader ?>

