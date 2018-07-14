<?php
/**
 * Shared Media Tagger
 * Blocked Media Admin
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col-12">
        <p>
            <b><?= sizeof($data['blocks']) ?></b> Blocked Media
        </p>
    </div>
</div>
<div class="row bg-white">
    <div class="col-12">
        <?php

        $thumbSize = 57;

        foreach ($data['blocks'] as $block) {
            $thumbUrl = preg_replace(
                '/\/(\d+)px-/',
                //Config::$sizeThumb . 'px-',
                '/' . $thumbSize . 'px-',
                $block['thumb']
            );
            $url = 'https://commons.wikimedia.org/w/index.php?curid=' . $block['pageid'];
            ?>
            <div class="mt-2 mb-2 mr-2 ml-2 d-inline-block">
                <a target="commmons" href="<?= $url ?>">
                    <img src="<?= $thumbUrl ?>" width="<?= $thumbSize ?>">
                    <br />
                    <small>
                        <?= $block['pageid'] ?>
                    </small>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
