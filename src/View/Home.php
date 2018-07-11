<?php
/**
* Shared Media Tagger - Home page
*
* @var array $data
*/
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row white">
    <div class="col-6">
        <p>
            <?= $data['about'] ?>
        </p>
        <p>
            <a href="<?= Tools::url('random') ?>" class="btn btn-dark text-white font-weight-bold"
               role="button">▶</a>
            <a href="<?= Tools::url('random') ?>" class="font-italic">Random File</a>
        </p>
        <p>
            <a href="<?= Tools::url('scores') ?>" class="btn btn-dark text-white font-weight-bold"
               role="button">⊜</a>
            <a href="<?= Tools::url('scores') ?>"><?= $data['countReviews']; ?> Scores</a>
        </p>
        <p>
            <a href="<?= Tools::url('browse') ?>" class="btn btn-dark text-white font-weight-bold"
               role="button">⊟</a>
            <a href="<?= Tools::url('browse') ?>"><?= $data['countFiles']; ?> Files</a>
        </p>
        <p>
            <a href="<?= Tools::url('categories') ?>" class="btn btn-dark text-white font-weight-bold"
               role="button">∑</a>
            <a href="<?= Tools::url('categories') ?>"><?= $data['countCategories']; ?> Topics</a>
        </p>
        <p>
            <form method="GET" action="<?= Tools::url('search') ?>">
                <input type="text" name="q" size="15" maxlength="256" value="<?=
                !empty($data['query'])
                   ? htmlentities($data['query'])
                   : ''
                ?>" />
                <input type="submit" value=" search " />
            </form>
        </p>

    </div>
    <div class="col-6">
        <?php
        foreach ($data['random'] as $media) {
            $this->smt->includeThumbnailBox($media);
        }
        ?>
    </div>
</div>
