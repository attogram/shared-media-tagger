<?php
/**
 * Shared Media Tagger
 * Menu Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;

$space = ' &nbsp; &nbsp; ';
$countFiles = number_format((float) $this->database->getImageCount());
$countCategories = number_format((float) $this->database->getCategoriesCount());
$countScores = number_format((float) $this->database->getTotalReviewCount());

?>
<div class="menu">
    <?= $this->getUserScoreBox() ?>
    <span class="nobr">
        <b><a href="' . Tools::url('home') . '"><?= Config::$siteName ?></a></b>
    </span>
    &nbsp; &nbsp;
    <span class="nobr">
        <a href="' . Tools::url('browse') . '">🔎<?= $countFiles ?>&nbsp;Files</a>
    </span>
    &nbsp; &nbsp;
    <span class="nobr">
        <a href="' . Tools::url('categories') . '">📂<?= $countCategories ?>&nbsp;Categories</a>
    </span>
    &nbsp; &nbsp;
    <span class="nobr">
        <a href="' . Tools::url('scores') . '">🗳️<?= $countScores ?>&nbsp;Scores</a>
    </span>
    &nbsp; &nbsp;
</div>
