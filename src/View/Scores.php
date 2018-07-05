<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Scores View
 *
 * @var string $me
 * @var array $rates
 * @var string $orderDesc
 * @var string $menu
 */
?>
<div class="box white">
    Scores:
    <br />
    <?php
    foreach ($rates as $media) {
        print $this->smt->displayThumbnailBox($media);
    }
    ?>
</div>
