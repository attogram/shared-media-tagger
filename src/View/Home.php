<?php
/**
 * Shared Media Tagger - Home page
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<style>
    .im {
        font-weight:bolder;
        font-size:132%;
        color:yellow;
        background-color:darkblue;
        padding:3px 4px 4px 4px;
    }
</style>
<div class="container-fluid white">
    <div class="row">
        <div class="col-xs-6 box">
            <h1><?= $data['name'] ?></h1>
            <p><?= $data['about'] ?></p>
            <br />
            <p><a href="<?= Tools::url('random') ?>">
                    <span class="im">≫</span>
                    &nbsp;
                    <i>Random File</i></a></p>
            <p><a href="<?= Tools::url('browse') ?>">
                    <span class="im">⊟</span>
                    &nbsp;
                    <b><?= $data['countFiles']; ?></b> Files</a></p>
            <p><a href="<?= Tools::url('categories') ?>">
                    <span class="im" style="padding:3px 8px 4px 6px;">∑</span>
                    &nbsp;
                    <b><?= $data['countCategories']; ?></b> Topics</a></p>
            <p><a href="<?= Tools::url('scores') ?>">
                    <span class="im">⊜</span>
                    &nbsp;
                    <b><?= $data['countReviews']; ?></b> Scores</a></p>

            <form method="GET" action="<?= Tools::url('search') ?>">
                <input type="text" name="q" size="15" maxlength="256" value="<?=
                !empty($data['query'])
                   ? htmlentities($data['query'])
                   : ''
                ?>" />
                <input type="submit" value=" search " />
            </form>


        </div>
        <div class="col-xs-6 box">
            <?php
            foreach ($data['random'] as $media) {
                $this->smt->includeThumbnailBox($media);
            }
            ?>
        </div>
    </div>
</div>
