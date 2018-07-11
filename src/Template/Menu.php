<?php
/**
 * Shared Media Tagger
 * Menu Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="menu" style="text-align:left;">
    <span class="nobr">
        <b><a href="<?= Tools::url('home') ?>"><?= Config::$siteName ?></a></b>
    </span>
    &nbsp;
    &nbsp;
    &nbsp;
    <span class="nobr">
        <a href="<?= Tools::url('random') ?>">▶ Random</a>
    </span>
    &nbsp;
    &nbsp;
    <span class="nobr">
        <a href="<?= Tools::url('search') ?>">🔎 Search</a>
    </span>
    &nbsp;
    &nbsp;
    <span class="nobr">
        <a href="<?= Tools::url('browse') ?>">⊟ <?=
            number_format((float) $this->database->getImageCount())
        ?>&nbsp;Files</a>
    </span>
    &nbsp;
    &nbsp;
    <span class="nobr">
        <a href="<?= Tools::url('categories') ?>">∑ <?=
            number_format((float) $this->database->getCategoriesCount())
        ?>&nbsp;Topics</a>
    </span>
    &nbsp;
    &nbsp;
    <span class="nobr">
        <a href="<?= Tools::url('scores') ?>">⊜ ️<?=
            number_format((float) $this->database->getTotalReviewCount())
        ?>&nbsp;Scores</a>
    </span>
    <span class="uscorebox nobr">
        <?= $this->getUserScore() ?>%
    </span>
</div>
