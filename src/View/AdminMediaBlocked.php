<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Blocked Media Admin
 *
 * @var array $data
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="white">
    <p><b><?= sizeof($data['blocks']) ?></b> Blocked Media</p>
<?php

foreach ($data['blocks'] as $block) {
    $url = str_replace('325px', $data['width'] . 'px', $block['thumb']);
    ?>
    <img src="<?= $url ?>" width="<?= $data['width'] ?>" style="vertical-align:middle;">
    <div style="display:inline-block;border:1px solid red;padding:10px;">
        <?= $block['pageid'] ?> :
        <a target="commmons"
           href="https://commons.wikimedia.org/w/index.php?curid=<?= $block['pageid'] ?>">
        <?= Tools::stripPrefix($block['title']) ?>
    </div>
    <br clear="all" />
    <?php
}

?>
</div>
