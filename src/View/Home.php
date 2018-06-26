<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Home View
 *
 * @var array $data
*/
?>
<div class="box grey center">
    <?= $data['tags'] ?>
    <?= $data['media'] ?>
    <div class="left" style="margin:auto;width:<?= $data['width'] ?>px;">
        <?= $data['admin'] ?>
        <?= $data['categories'] ?>
        <?= $data['reviews'] ?>

    </div>
</div>
