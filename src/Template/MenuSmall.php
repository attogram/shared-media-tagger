<?php
/**
 * Shared Media Tagger
 * Menu Small Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="menu" style="text-align:left;">
    <span class="nobr">
        <b><a href="<?= Tools::url('home') ?>"><?= Config::$siteName ?></a></b>
    </span>
    &nbsp; &nbsp; &nbsp;
    <span class="nobr" style="font-weight:bolder;">
        <a href="<?= Tools::url('random') ?>" title="Goto a Random File">▶</a>
    </span>
    &nbsp; &nbsp;
    <span class="nobr" style="font-weight:bolder;">
        <a href="<?= Tools::url('search') ?>" title="Search">🔎</a>
    </span>
    &nbsp; &nbsp;
    <span class="nobr" style="font-weight:bolder;">
        <a href="<?= Tools::url('browse') ?>" title="All Files">⊟</a>
    </span>
    &nbsp; &nbsp;
    <span class="nobr" style="font-weight:bolder;">
        <a href="<?= Tools::url('categories') ?>" title="Topics">∑</a>
    </span>
    &nbsp; &nbsp;
    <span class="nobr" style="font-weight:bolder;">
        <a href="<?= Tools::url('scores') ?>" title="Scores">⊜</a>
    </span>
    &nbsp; &nbsp;
    &nbsp; &nbsp;
    <span class="uscorebox nobr">
        <?= $this->getUserScore() ?>%
    </span>
</div>
