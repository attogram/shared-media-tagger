<?php
/**
 * Shared Media Tagger
 * Html Footer Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-secondary">
    <div class="col-12">
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>">ADMIN</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/site">SITE</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/tag">TAGS</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/category">CATEGORY</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/media">MEDIA</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/curate">CURATE</a>
        <a class="text-white mr-3" href="<?= Tools::url('admin') ?>/user">USER</a>
        <a class="text-white" href="<?= Tools::url('admin') ?>/database">DATABASE</a>
    </div>
</div>
