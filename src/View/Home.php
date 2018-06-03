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
        <br />
        <?= $data['reviews'] ?>
        <?= $data['categories'] ?>
        <br />
        <a href="<?= $data['reportUrl'] ?>" style="color:#666;font-size:85%;">REPORT this file</a>
    </div>
</div>
