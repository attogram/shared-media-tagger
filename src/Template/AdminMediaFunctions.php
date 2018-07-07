<?php
/**
 * Shared Media Tagger
 * Admin Media Functions Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="attribution" style="display:block;">
    <a style="font-size:130%;"
       href="<?= Tools::url('admin') ?>media?dm=<?= $this->mediaId ?>"
       title="Delete" target="admin"
       onclick="return confirm('Confirm: Delete Media # <?= $this->mediaId ?> ?');"
    >❌</a> &nbsp;
    <input type="checkbox"
           name="media[]" value="<?= $this->mediaId ?>"
    />
    &nbsp;
    <a style="font-size:170%;"
       href="<?= Tools::url('admin') ?>media?am=<?= $this->mediaId ?>"
       title="Refresh"
       target="admin"
       onclick="return confirm('Confirm: Refresh Media # <?= $this->mediaId ?> ?');"
    >♻</a>
    &nbsp;
    <a style="font-size:140%;"
       href="<?= Tools::url('admin') ?>curate?i=<?= $this->mediaId ?>"
    >C</a>
</div>
