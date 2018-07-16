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
        $data['urlName'] = 'scores';

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
}
