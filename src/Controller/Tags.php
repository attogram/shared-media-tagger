<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Tags
 */
class Tags extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Tags');

        $this->smt->title = 'Tags - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();

        $me = Tools::url('tags');

        $tags = $this->smt->database->getTags('DESC');

        $menu = '';
        foreach ($tags as $tag) {
            $menu .= '<span class="reviewbutton tag' . $tag['position'] . '">'
                . '<a href="' . $me . '?o=' . $tag['id'] . '">+'
                . $this->smt->database->getTaggingCount($tag['id'])
                . ' ' . $tag['name'] . '</a></span>';
        }
        $menu .= '<span class="reviewbutton">'
            . '<a href="' . $me . '?o=total.reviews">+'
            . $this->smt->database->getTaggingCount()
            . ' Total</a></span>';

        $order = isset($_GET['o']) ? Tools::categoryUrldecode($_GET['o']) : '';

        // Reviews per tag
        $tagName = null;
        if (!empty($_GET['o']) && Tools::isPositiveNumber($_GET['o'])) {
            $tagId = $_GET['o'];
            $order = 'PER.TAG';
        }

        $limit = 100;  // @TODO TMP DEV

        $sql = $bind = $orderDesc = null;
        switch ($order) {
            case 'PER.TAG':
                $orderDesc = $tagName;
                $sql = '
                    SELECT t.count, t.tag_id, m.*
                    FROM tagging AS t, media AS m
                    WHERE t.media_pageid = m.pageid AND t.tag_id = :tag_id
                    ORDER BY t.count DESC LIMIT ' . $limit;
                $bind = [':tag_id' => $tagId];
                break;

            case 'total.reviews':
                $orderDesc = 'Total # of tags';
                $sql = '
                    SELECT SUM(t.count) AS tcount, t.tag_id, m.*
                    FROM tagging AS t, media AS m
                    WHERE t.media_pageid = m.pageid
                    GROUP BY m.pageid
                    ORDER BY tcount DESC
                    LIMIT ' . $limit;
                $bind = [];
                break;
        }

        $rates = [];
        if ($sql) {
            $rates = $this->smt->database->queryAsArray($sql, $bind);
            if (!is_array($rates)) {
                $rates = [];
            }
        }

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
