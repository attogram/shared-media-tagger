<?php
/**
 * Shared Media Tagger
 * Scores View
 *
 * @var array $data
 */
declare(strict_types = 1);

?>
<div class="row bg-white">
    <div class="col mb-3">
        <h2 class="d-inline">Scores</h2>
        <small class="text-muted">
            &nbsp; <?= $data['scored'] ?> files
            with <?= $this->smt->database->getTotalVotesCount() ?> scores total,
            on a scale of 1 to 5 / # of votes
        </small>
        <?php $this->smt->includeTemplate('Pagination', $data) ?>
    </div>
</div>
<div class="row bg-white">
    <div class="col">
        <?php foreach ($data['scores'] as $media) { ?>
        <div class="bg-light d-inline-block text-center mb-2">
            <div class="h2 d-inline">
                <?= round($media['score'], 1) ?></div><span
                    class="h6 text-black-50">/<?= $media['votes'] ?></span>
            <br />
            <?php $this->smt->includeTemplate('Thumbnail', $media); ?>
        </div>
        <?php } ?>
    </div>
</div>
<div class="row bg-white">
    <div class="col mb-3">
        <?php $this->smt->includeTemplate('Pagination', $data) ?>
    </div>
</div>
