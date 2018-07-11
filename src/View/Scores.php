<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Scores View
 *
 * @var array $data
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="container-fluid white">
    <h1>
        <?= $data['scored'] ?> Scores
        <small class="text-muted">
            on a scale of 1 to 5
            / # of votes
        </small>
    </h1>
    <?php if ($data['pages'] > 1) { ?>
    <nav aria-label="Scores Pagination">
        <ul class="pagination pagination-sm justify-content-center flex-wrap">
            <?php if ($data['page'] > 1) { ?>
            <li class="page-item">
                <a class="page-link" href="<?=
                Tools::url('scores') . '/' . ($data['page'] - 1)
                ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                    <span class="sr-only">Previous</span>
                </a>
            </li>
            <?php } ?>
            <?php for ($page = 1; $page <= $data['pages']; $page++) {
                $classActive = '';
                if ($page === $data['page']) {
                    $classActive = ' active';
                }
                ?>
                <li class="page-item<?= $classActive ?>"><a class="page-link" href="<?=
                       Tools::url('scores') . ($page > 1 ? '/' . $page : '')
                    ?>"><?= $page ?></a></li>
            <?php } ?>
            <?php if ($data['page'] < $data['pages']) { ?>
            <li class="page-item">
                <a class="page-link" href="<?=
                Tools::url('scores') . '/' . ($data['page'] + 1)
                ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                    <span class="sr-only">Next</span>
                </a>
            </li>
            <?php } ?>
        </ul>
    </nav>
    <?php } ?>
    <p>
        <em></em>
        <span class="text-secondary"></span>
    </p>
    <?php foreach ($data['scores'] as $media) { ?>
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
