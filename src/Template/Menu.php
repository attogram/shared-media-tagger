<?php
/**
 * Shared Media Tagger
 * Menu Template
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
    &nbsp; &nbsp
    <span class="nobr">
        <a href="<?= Tools::url('browse') ?>">🔎<?=
            number_format((float) $this->database->getImageCount())
        ?>&nbsp;Files</a>
    </span>
    &nbsp; &nbsp
    <span class="nobr">
        <a href="<?= Tools::url('categories') ?>">📂<?=
            number_format((float) $this->database->getCategoriesCount())
        ?>&nbsp;Categories</a>
    </span>
    &nbsp; &nbsp
    <span class="nobr">
        <a href="<?= Tools::url('scores') ?>">🗳️<?=
            number_format((float) $this->database->getTotalReviewCount())
        ?>&nbsp;Scores</a>
    </span>
</div>
