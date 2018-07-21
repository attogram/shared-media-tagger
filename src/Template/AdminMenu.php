<?php
/**
 * Shared Media Tagger
 * Html Footer Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-secondary p-1">
    <div class="col">
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>">🔧</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/add">ADD</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/site">SITE</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/tag">TAGS</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/topic">TOPICS</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/media">MEDIA</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/curate">CURATE</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/user">USERS</a>
        <a class="text-white mr-2" href="<?= Tools::url('admin') ?>/database">DATABASE</a>
        <a class="text-white font-italic" href="<?= Tools::url('logout') ?>">logout</a>
    </div>
</div>
