<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Scores
 */
class Scores extends ControllerBase
{
    /** @var int - Thumbnails per page */
    private $limit = 20;

    /** @var int - Current Page # */
    private $page;

    protected function display()
    {
        $this->page = 1;
        $vars = $this->smt->router->getVars();
        if (!empty($vars[0]) && Tools::isPositiveNumber($vars[0])) {
            $this->page = (int) $vars[0];
        }

        $data = [];
        $data['scored'] = $this->getScoredMediaCount();
        $data['scores'] = $this->getMediasByScore();
        $data['pages'] = ceil($data['scored'] / $this->limit);
        $data['limit'] = $this->limit;

        if (!empty($data['scores']) && ($this->page > $data['pages'])) {
            $this->smt->fail404();
        }
        $data['page'] = $this->page;

        $this->smt->title = 'Scores - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        /** @noinspection PhpIncludeInspection */
        include($this->getView('Scores'));
        $this->smt->includeFooter();
    }

    /**
     * @return int
     */
    private function getScoredMediaCount()
    {
        $scored = 0;
        $scoredCount = $this->smt->database->queryAsArray(
            'SELECT COUNT(DISTINCT(media_pageid)) AS count FROM tagging'
        );
        if ($scoredCount) {
            $scored = $scoredCount[0]['count'];
        }

        return $scored;
    }

    /**
     * @return array
     */
    private function getMediasByScore()
    {
        $scores = $this->smt->database->queryAsArray(
            'SELECT SUM(tag.score) AS total,
                COUNT(tagging.id) AS votes,
                SUM(tag.score)*1.0/COUNT(tagging.id) AS score,
                media.*
            FROM tagging, tag, media
            WHERE tagging.tag_id = tag.id
            AND tagging.media_pageid = media.pageid
            GROUP BY tagging.media_pageid
            ORDER BY score DESC, votes DESC
            LIMIT :limit 
            OFFSET :offset',
            [
                ':limit' => $this->limit,
                ':offset' => ($this->page * $this->limit) - $this->limit,
            ]
        );
        if (empty($scores) || !is_array($scores)) {
            $scores = [];
        }

        return $scores;
    }

    /**
     * @param $data
     */
    public function printPagination($data)
    {
        if ($data['pages'] < 2) {
            return;
        }
        ?>
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
        <?php
    }
}
