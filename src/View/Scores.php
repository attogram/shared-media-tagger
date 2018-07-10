<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Scores View
 *
 * @var array $scores
 */
?>
<div class="box white">
    <h1>Top <?= count($scores) ?> Scores</h1>
    <p>
        <em>Scored on a scale of 1 to 5</em>
        <span style="color:darkgrey;font-size:90%;">/ and # of votes</span>
    </p>
    <?php foreach ($scores as $media) { ?>
    <div class="scorebox center">
        <div class="score">
            <?= round($media['score'], 1) ?>
            <span class="votes"> /<?= $media['votes'] ?></span>
        </div>
        <br />
        <?php $this->smt->includeThumbnailBox($media); ?>
    </div>
    <?php } ?>
</div>
