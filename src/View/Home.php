<?php
/**
 * Shared Media Tagger - Home page
 *
 * @var array $data
 */

declare(strict_types = 1);
?>
<div class="container white">
    <div class="row">
        <div class="col-sm-6 box white">
            <h1><?= $data['name'] ?></h1>
            <p><?= $data['about'] ?></p>
        </div>
        <div class="col-sm-6 box white">
            <em>Random media:</em>
            <br />
            <?php
            foreach ($data['random'] as $media) {
                print $this->smt->displayThumbnailBox($media);
            }
            ?>
        </div>
    </div>
</div>
