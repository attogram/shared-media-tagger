<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Topic
 */
class Topic extends ControllerBase
{
    protected function display()
    {
        $topicName = $this->smt->router->getVar(0);
        if (!$topicName) {
            $this->smt->fail404('Topic Not Found');
        }

        if ($this->smt->router->getVar(1)) {
            $topicName .= '/' . $this->smt->router->getVar(1);
        }
        if ($this->smt->router->getVar(2)) {
            $topicName .= '/' . $this->smt->router->getVar(2);
        }
        if ($this->smt->router->getVar(3)) {
            $topicName .= '/' . $this->smt->router->getVar(3);
        }

        $topicName = Tools::topicUrldecode($topicName);
        $this->smt->title = $topicName . ' - ' . Config::$siteName;
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');

        $topicName = 'Category:' . $topicName;

        $pageLimit = 20;

        $topicInfo = $this->smt->database->getTopic($topicName);

        if (!$topicInfo) {
            $this->smt->fail404('Topic Not Found');
        }

        $topicSize = $this->smt->database->getTopicSize($topicName);

        /** @noinspection PhpUnusedLocalVariableInspection */
        $pager = '';
        $sqlLimit = '';
        if ($topicSize > $pageLimit) {
            $offset = isset($_GET['o']) ? $_GET['o'] : 0;
            $sqlLimit = " LIMIT $pageLimit OFFSET $offset";
            $pageCount = 0;
            $pager = 'pages: ';
            for ($count = 0; $count < $topicSize; $count+=$pageLimit) {
                if ($count == $offset) {
                    $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
                        . '&nbsp;' . ++$pageCount . '&nbsp;</span> ';
                    continue;
                }
                $pager .= '<a href="?o=' . $count . '">'
                    . '&nbsp;' . ++$pageCount . '&nbsp;</a> ';
            }
        }

        $sql = 'SELECT m.*
                FROM topic2media AS c2m, topic AS c, media AS m
                WHERE c2m.category_id = c.id
                AND m.pageid = c2m.media_pageid
                AND c.name = :topic_name';

        if (Config::$siteInfo['curation'] == 1) {
            $sql .= " AND m.curated ='1'";
        }
        $sql .= " ORDER BY m.pageid ASC $sqlLimit";

        $bind = [':topic_name' => $topicName];

        $topic = $this->smt->database->queryAsArray($sql, $bind);

        if (!Tools::isAdmin() && (!$topic || !is_array($topic))) {
            $this->smt->fail404('Topic Not Found');
        }

        /** @noinspection PhpUnusedLocalVariableInspection */
        $votesPerTopic = $this->smt->displayVotes(
            $this->smt->database->getDbVotesPerTopic($topicInfo['id'])
        );

        /** @noinspection PhpUnusedLocalVariableInspection */
        $topicNameDisplay = Tools::stripPrefix($topicName);

        /** @noinspection PhpIncludeInspection */
        include($this->getView('Topic'));

        $this->smt->includeFooter();
    }
}
