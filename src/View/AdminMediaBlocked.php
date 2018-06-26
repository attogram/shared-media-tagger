<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Blocked Media Admin
 *
 * @var array $blocks
 * @var int|string $width
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="box white">
    <p>Blocked Media Admin:</p>
    * <a target="sqlite" href="<?=
        Tools::url('admin');
    ?>sqladmin?table=block&action=row_view">Database: View/Edit Blocked Media</a>
    <hr />
    <p><b><?= sizeof($blocks) ?></b> Blocked Media</p>
<?php

foreach ($blocks as $block) {
    $url = $block['thumb'];
    $url = str_replace('325px', $width . 'px', $url);

    ?>
    <img src="<?= $url ?>" width="<?= $width ?>" style="vertical-align:middle;">
    <div style="display:inline-block;border:1px solid red;padding:10px;">
        <?= $block['pageid'] ?> :
        <a target="commmons"
           href="https://commons.wikimedia.org/w/Home.php?curid=<?= $block['pageid'] ?>">
        <?= Tools::stripPrefix($block['title']) ?>
        <br /><br />
        <a target="sqlite"
           href="./sqladmin?table=block&action=row_editordelete&pk=%5B<?=
            $block['pageid'] ?>%5D&type=delete">* Remove from Block List</a>
    </div>
    <br clear="all" />
    <br clear="all" />
    <?php
}

?>
</div>
