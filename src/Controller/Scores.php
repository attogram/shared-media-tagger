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
    protected function display()
    {
        $view = $this->getView('Scores');

        $this->smt->title = 'Scores - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');

        $limit = 100;  // @TODO TMP DEV

        $sql = '
            SELECT SUM(t.count) AS tcount, t.tag_id, m.*
            FROM tagging AS t, media AS m
            WHERE t.media_pageid = m.pageid
            GROUP BY m.pageid
            ORDER BY tcount DESC, t.tag_id DESC
            LIMIT :limit';
        $bind = [':limit' => $limit];

        $rates = $this->smt->database->queryAsArray($sql, $bind);
        if (empty($rates) || !is_array($rates)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $rates = [];
        }

        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
