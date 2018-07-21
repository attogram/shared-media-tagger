<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class Topics
 */
class Topics extends ControllerBase
{
    protected function display()
    {
        $pageLimit = 1000;

        $search = false;
        if (isset($_GET['s']) && $_GET['s']) {
            $search = $_GET['s'];
        }

        $hidden = 0;
        if (isset($_GET['h']) && $_GET['h']) {
            $hidden = 1;
        }

        $topicSize = $this->smt->database->getTopicsCount(false, $hidden);
        // @TODO get real selection size, not full topic count

        $pager = '';
        $sqlLimit = '';
        if ($topicSize > $pageLimit) {
            $offset = isset($_GET['o']) ? $_GET['o'] : 0;
            $sqlLimit = " LIMIT $pageLimit OFFSET $offset";
            $pageCount = 0;
            $pager = ': ';
            for ($count = 0; $count < $topicSize; $count += $pageLimit) {
                if ($count == $offset) {
                    $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
                        . '&nbsp;' . ++$pageCount . '&nbsp;</span> ';
                    $pagerCount = $pageCount;
                    continue;
                }
                $pager .= '<a href="?o=' . $count
                    . ($hidden ? '&amp;h=1' : '')
                    . '">&nbsp;' . ++$pageCount . '&nbsp;</a> ';
            }
        }
        $pager = '<b>' . number_format((float) $topicSize) . '</b> '
            . ($hidden ? 'Technical' : 'Active') . ' Topics' . $pager;

        $bind = [];
        $sql = 'SELECT id, name, local_files, hidden
        FROM category
        WHERE local_files > 0';
        if ($hidden) {
            $sql .= ' AND hidden > 0';
        } else {
            $sql .= ' AND hidden < 1';
        }
        if ($search) {
            $sql .= ' AND name LIKE :search';
            $bind[':search'] = '%' . $search . '%';
        }
        $sql .= ' ORDER BY local_files DESC, name ';
        $sql .= $sqlLimit;

        $topics = $this->smt->database->queryAsArray($sql, $bind);

        $pageName = number_format((float) $topicSize);
        if ($hidden) {
            $pageName .= ' Technical';
        } else {
            $pageName .= ' Active';
        }
        $pageName .= ' Topics';
        if (isset($pagerCount)) {
            $pageName .= ', page #' . $pagerCount;
        }
        $this->smt->title = $pageName . ' - ' . Config::$siteName;

        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');

        /** @noinspection PhpIncludeInspection */
        include($this->getView('Topics'));

        $this->smt->includeFooter();
    }
}
