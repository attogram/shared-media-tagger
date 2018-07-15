<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class Categories
 */
class Categories extends ControllerBase
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

        $categorySize = $this->smt->database->getCategoriesCount(false, $hidden);
        // @TODO get real selection size, not full category count

        $pager = '';
        $sqlLimit = '';
        if ($categorySize > $pageLimit) {
            $offset = isset($_GET['o']) ? $_GET['o'] : 0;
            $sqlLimit = " LIMIT $pageLimit OFFSET $offset";
            $pageCount = 0;
            $pager = ': ';
            for ($count = 0; $count < $categorySize; $count += $pageLimit) {
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
        $pager = '<b>' . number_format((float) $categorySize) . '</b> '
            . ($hidden ? 'Technical' : 'Active') . ' Categories' . $pager;

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

        $categories = $this->smt->database->queryAsArray($sql, $bind);

        $pageName = number_format((float) $categorySize);
        if ($hidden) {
            $pageName .= ' Technical';
        } else {
            $pageName .= ' Active';
        }
        $pageName .= ' Categories';
        if (isset($pagerCount)) {
            $pageName .= ', page #' . $pagerCount;
        }
        $this->smt->title = $pageName . ' - ' . Config::$siteName;

        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');

        /** @noinspection PhpIncludeInspection */
        include($this->getView('Categories'));

        $this->smt->includeFooter();
    }
}
