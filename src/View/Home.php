<?php
/**
 * Shared Media Tagger - Home page
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$this->smt->includeHeader();

?>
<div class="container white">
    <div class="row">
        <div class="col-sm-6 box white">
            <h1><?= $data['name'] ?></h1>
            <p><?= $data['about'] ?></p>
            <hr />
            <p>- <a href="<?= Tools::url('browse') ?>"><b><?=
                        $data['countFiles']; ?></b> Media Files</a></p>
            <p>- <a href="<?= Tools::url('categories') ?>"><b><?=
                        $data['countCategories']; ?></b> Categories</a></p>
            <p>- <a href="<?= Tools::url('reviews') ?>"><b><?=
                        $data['countReviews']; ?></b> Reviews</a></p>
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
<?php

$this->smt->includeFooter();

