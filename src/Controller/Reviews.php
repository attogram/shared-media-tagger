<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Reviews
 */
class Reviews extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Reviews');

        $this->smt->title = 'Reviews - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();

        $me = Tools::url('reviews');

        $tags = $this->smt->database->getTags();

        $menu = '';
        foreach ($tags as $tag) {
            $menu .= '<span class="reviewbutton tag' . $tag['position'] . '">'
                . '<a href="' . $me . '?o=reviews.'
                . Tools::categoryUrlencode($tag['name']) . '">+'
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
        if ((preg_match('/^reviews\.(.*)/', $order, $matches)) === 1) {
            $tagName = $matches[1];
            $tagId = $this->smt->database->getTagIdByName($tagName);
            if (!$tagId) {
                Tools::notice('Invalid Review Name');
                $order = '';
            } else {
                $order = 'PER.TAG';
            }
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
                $orderDesc = 'Total # of reviews';
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
