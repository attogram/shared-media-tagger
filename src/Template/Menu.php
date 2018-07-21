<?php
/**
 * Shared Media Tagger
 * Menu Small Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row text-white bg-dark small pb-2 pt-2">
    <div class="col-7 col-sm-5 text-left">
        <a class="text-white" href="<?= Tools::url('home') ?>"><?= Config::$siteName ?></a>
        <span class="text-info ml-2 font-bold" title="Percentage Completed"><?=
            $this->getUserScore()
            ?>%</span>
        <div class="d-none d-md-inline mr-2 text-info">completed</div>
        <?php if (Tools::isAdmin()) { ?>
            <a class="text-white ml-3" href="<?= Tools::url('admin') ?>" title="Admin">🔧</a>
        <?php } ?>
    </div>
    <div class="col-5 col-sm-7 text-right">
        <a class="text-white pr-1 pl-1" href="<?= Tools::url('random') ?>" title="Random File">
            ▷ <div class="d-none d-sm-inline mr-2">Random</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="<?= Tools::url('search') ?>" title="Search">
            ⧂
            <div class="d-none d-sm-inline mr-2">Search</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="<?= Tools::url('browse') ?>" title="All Files">
            ⊞
            <div class="d-none d-md-inline"><?= $this->database->getFileCount() ?></div>
            <div class="d-none d-sm-inline mr-2">Files</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="<?= Tools::url('topics') ?>" title="Topics">
            ⋈
            <div class="d-none d-md-inline"><?= $this->database->getTopicsCount() ?></div>
            <div class="d-none d-sm-inline mr-2">Topics</div>
        </a>
        <a class="text-white text-nowrap pr-1 pl-1" href="<?= Tools::url('scores') ?>" title="Scores">
            ⊜
            <div class="d-none d-md-inline"><?= $this->database->getTotalVotesCount() ?></div>
            <div class="d-none d-sm-inline">Votes</div>
        </a>
    </div>
</div>
