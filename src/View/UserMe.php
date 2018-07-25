<?php
/**
 * Shared Media Tagger
 * UserMe View
 *
 * @var array $data
 */
declare(strict_types = 1);

?>
<div class="row bg-white text-black pt-2 pb-2">
    <div class="col">
        <kbd><?= $data['numberMediaVotes'] ?></kbd> Media Votes
    </div>
    <div class="col">
        <?php $this->smt->includeTemplate('Pagination', $data) ?>
    </div>
</div>
<div class="row bg-white">
    <div class="col">
        <?php
        $seen = [];
        foreach ($data['media'] as $media) {
            if (empty($seen[$media['score']])) {
                ?>
                <div class="border-top border-dark mt-2">
                    Score: <b><?= $media['score'] ?></b>
                </div>
                <?php
            }
            $this->smt->includeTemplate('Thumbnail', $media);
            $seen[$media['score']] = true;
        }
        ?>
        <?php $this->smt->includeTemplate('Pagination', $data) ?>
    </div>
</div>
