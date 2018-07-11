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
        <div class="col-6 box">
            <h1><?= $data['name'] ?></h1>
            <p><?= $data['about'] ?></p>
            <br />
            <div class="btn_box"><a href="<?= Tools::url('random')
                ?>"><span class="btn">≫</span><i>Random File</i></a>
            </div>
            <br />
            <div class="btn_box"><a href="<?= Tools::url('browse')
                ?>"><span class="btn">⊟</span><b><?= $data['countFiles']; ?></b> Files</a>
            </div>
            <br />
            <div class="btn_box"><a href="<?= Tools::url('categories')
                ?>"><span class="btn">∑</span><b><?= $data['countCategories']; ?></b> Topics</a>
            </div>
            <br />
            <div class="btn_box"><a href="<?= Tools::url('scores')
                ?>"><span class="btn">⊜</span><b><?= $data['countReviews']; ?></b> Scores</a>
            </div>
            <br />
            <br />
            <form method="GET" action="<?= Tools::url('search') ?>">
                <input type="text" name="q" size="15" maxlength="256" value="<?=
                !empty($data['query'])
                   ? htmlentities($data['query'])
                   : ''
                ?>" />
                <input type="submit" value=" search " />
            </form>
        </div>
        <div class="col-6 box">
            <?php
            foreach ($data['random'] as $media) {
                $this->smt->includeThumbnailBox($media);
            }
            ?>
        </div>
    </div>
</div>
