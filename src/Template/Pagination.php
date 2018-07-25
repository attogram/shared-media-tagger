<?php
/**
 * Shared Media Tagger
 * Pagination Template
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

if (empty($data['pages']) || empty($data['page']) || $data['pages'] < 2) {
    return;
}

?>
<nav aria-label="Scores Pagination">
    <ul class="pagination pagination-sm justify-content-center flex-wrap">
        <?php if ($data['page'] != 1) { ?>
        <li class="page-item">
            <a class="page-link" href="<?=
                Tools::url($data['urlName']) . '/' . ($data['page'] - 1)
            ?>" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
                <span class="sr-only">Previous</span>
            </a>
        </li>
        <?php } ?>
        <li class="page-item<?= ($data['page'] == 1 ? ' active' : '') ?>"><a class="page-link" href="<?=
            Tools::url($data['urlName'])
        ?>">1</a></li>
        <li class="page-item<?= ($data['page'] == 2 ? ' active' : '') ?>"><a class="page-link" href="<?=
            Tools::url($data['urlName']) . '/2'
            ?>">2</a></li>
        <?php for ($page = 3; $page <= $data['pages']; $page++) {
            if ($data['pages'] > 10) {
                $diff = abs($data['page'] - $page);
                if ($diff > 9) {
                    continue;
                }
            }
            $classActive = '';
            if ($page === $data['page']) {
                $classActive = ' active';
            }
            ?>
            <li class="page-item<?= $classActive ?>"><a class="page-link" href="<?=
                Tools::url($data['urlName']) . ($page > 1 ? '/' . $page : '')
            ?>"><?= $page ?></a></li>
        <?php } ?>
        <?php if ($data['page'] < $data['pages']) { ?>
        <li class="page-item">
            <a class="page-link" href="<?=
                Tools::url($data['urlName']) . '/' . ($data['page'] + 1)
            ?>" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
                <span class="sr-only">Next</span>
            </a>
        </li>
        <?php } ?>
    </ul>
</nav>
