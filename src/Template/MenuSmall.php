<?php
/**
 * Shared Media Tagger
 * Menu Small Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="menu">
    <?= $this->getUserScoreBox() ?>
    <span class="nobr">
        <b><a href="<?= Tools::url('home') ?>"><?= Config::$siteName ?></a></b>
    </span>
    &nbsp; &nbsp;
    <a href="<?= Tools::url('browse') ?>">🔎Files</a>
    &nbsp; &nbsp;
    <a href="<?= Tools::url('categories') ?>">📂Topics</a>
    &nbsp; &nbsp;
    <a href="<?= Tools::url('scores') ?>">🗳️Scores</a>
</div>
