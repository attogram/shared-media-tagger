<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Tags
 *
 * @var string $me
 * @var array $rates
 * @var string $orderDesc
 * @var string $menu
 */
?>
<div class="box white">
    Tags:
    <br />
    <?= $menu ?>
    <hr />
    <p>
        <b><?= $orderDesc ?></b>: <?= sizeof($rates) ?> files tagged.
    </p>
    <?php
    foreach ($rates as $media) {
        print $this->smt->displayThumbnailBox($media);
    }
    ?>
</div>
