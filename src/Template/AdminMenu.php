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
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>">ADMIN</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/site">SITE</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/tag">TAGS</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/category">CATEGORY</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/media">MEDIA</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/curate">CURATE</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/user">USER</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/database">DATABASE</a>
        <a class="text-white font-italic" href="<?= Tools::url('logout') ?>">logout</a>
    </div>
</div>
