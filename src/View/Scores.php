<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Scores View
 *
 * @var array $scores
 */
?>
<style>
    .vt {
        font-size:50%;
        color:darkgrey;
    }
</style>
<div class="box white">
    <h1>Top <?= count($scores) ?> Scores</h1>
    <p>
        <em>rating media on a scale of 1 to 5</em>
    </p>
    <?php foreach ($scores as $media) { ?>
        <div class="center" style="display:inline-block; background-color:#ddd; vertical-align:top;">
            <div style="font-size:165%; font-weight:bold; display:inline;">
                <?=
                    round($media['score'], 1)
                ?><span class="vt"> /<?=
                    $media['votes']
                ?></span>

            </div>
            <br />
            <?php $this->smt->includeThumbnailBox($media); ?>
        </div>
    <?php } ?>
</div>
