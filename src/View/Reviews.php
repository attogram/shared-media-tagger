<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Reviews
 *
 * @var string $me
 * @var array $rates
 * @var string $orderDesc
 * @var string $menu
 */
?>
<div class="box white">
    Reviews:
    <br />
    <?= $menu ?>
    <hr />
    <p>
        <b><?= $orderDesc ?></b>: <?= sizeof($rates) ?> files reviewed.
    </p>
    <?php
    foreach ($rates as $media) {
        print $this->smt->displayThumbnailBox($media);
    }
    ?>
</div>
