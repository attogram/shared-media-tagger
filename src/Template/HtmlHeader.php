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
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css"
integrity="sha384-Smlep5jCw/wG7hdkwQ/Z5nLIefveQRIY9nfy6xoR1uRYBtpZgI6339F5dgvm/e9B" crossorigin="anonymous">
<style>
body { background-color:#343a40; }
a { text-decoration:none !important; color:darkblue; }
a:hover { background-color:yellow; color:black !important; }
video { border:1px solid black; background-color:white; background-clip:content-box; }
audio { border:1px solid black; background-color:white; background-clip:content-box; }
.debug { color:black; background-color:yellow; font-size:90%; }
.notice { color:black; background-color:lightsalmon; font-size:90%; }
.error { color:black; background-color:yellow; font-size:90%; }
.fail { color:black; background-color:yellow; font-size:110%; }
.mediatitle { font-size:80%; }
.attribution { font-size:65%; }
</style>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js"
integrity="sha384-o+RDsa0aLu++PJvFqy8fFScvbHFLtbvScb8AjopnFD+iEQ7wo/CG0xlczd+2O/em" crossorigin="anonymous"></script>
<title><?= $this->title ?></title>
<link rel="icon" type="image/png" href="<?= Tools::url('home') ?>favicon.ico" />
</head>
<body>
<div class="container-fluid">
    <?php if (!empty($this->customSiteHeader)) { ?>
    <div class="row">
        <div class="col-12">
            <?= $this->customSiteHeader ?>
        </div>
    </div>
    <?php } ?>

