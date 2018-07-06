<?php
/**
 * Shared Media Tagger - Home page
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="container-fluid white">
    <div class="row">
        <div class="col-xs-6 box">
            <h1><?= $data['name'] ?></h1>
            <p><?= $data['about'] ?></p>
            <br />
            <p><a href="<?= Tools::url('browse') ?>">🔎 <b><?=
                $data['countFiles']; ?></b> Files</a></p>
            <p><a href="<?= Tools::url('categories') ?>">📂 <b><?=
                $data['countCategories']; ?></b> Topics</a></p>
            <p><a href="<?= Tools::url('scores') ?>">🗳️ <b><?=
                $data['countReviews']; ?></b> Scores</a></p>
        </div>
        <div class="col-xs-6 box">
            <?php
            foreach ($data['random'] as $media) {
                print $this->smt->displayThumbnailBox($media);
            }
            ?>
        </div>
    </div>
</div>
