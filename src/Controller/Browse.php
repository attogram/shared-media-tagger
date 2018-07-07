<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class About
 */
class Browse extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Browse');

        $pageLimit = 20; // # of files per page

        $sort = 'random';
        if (isset($_GET['s'])) {
            $sort = $_GET['s'];
        }
        $where = '';
        switch ($sort) {
            default:
            case '':
            case 'random':
                $orderby = ' ORDER BY RANDOM()';
                break;
            case 'pageid':
                $orderby = ' ORDER BY pageid';
                $extra = 'pageid';
                break;
            case 'size':
                $orderby = ' ORDER BY size';
                $extra = 'size';
                $extraNumberformat = 1;
                break;
            case 'title':
                $orderby = ' ORDER BY title';
                break;
            case 'mime':
                $orderby = ' ORDER BY mime';
                $extra = 'mime';
                break;
            case 'width':
                $orderby = ' ORDER BY width';
                $extra = 'width';
                $extraNumberformat = 1;
                break;
            case 'height':
                $orderby = ' ORDER BY height';
                $extra = 'height';
                $extraNumberformat = 1;
                break;
            case 'datetimeoriginal':
                $orderby = ' ORDER BY datetimeoriginal';
                break;
            case 'timestamp':
                $orderby = ' ORDER BY timestamp';
                $extra = 'timestamp';
                break;
            case 'updated':
                $orderby = ' ORDER BY updated';
                $extra = 'updated';
                break;
            case 'licenseuri':
                $orderby = ' ORDER BY licenseuri';
                break;
            case 'licensename':
                $orderby = ' ORDER BY licensename';
                break;
            case 'licenseshortname':
                $orderby = ' ORDER BY licenseshortname';
                $extra = 'licenseshortname';
                break;
            case 'usageterms':
                $orderby = ' ORDER BY usageterms';
                $extra = 'usageterms';
                break;
            case 'attributionrequired':
                $orderby = ' ORDER BY attributionrequired';
                $extra = 'attributionrequired';
                break;
            case 'restrictions':
                $orderby = ' ORDER BY restrictions';
                $extra = 'restrictions';
                break;
            case 'user':
                $orderby = ' ORDER BY user';
                $extra = 'user';
                break;
            case 'duration':
                $orderby = ' ORDER BY duration';
                $extra = 'duration';
                break;
            case 'sha1':
                $orderby = ' ORDER BY sha1';
                break;
        }

        if (Config::$siteInfo['curation'] == 1) {
            if ($where) {
                $where .= " AND curated = '1'";
            } else {
                $where = " WHERE curated = '1'";
            }
        }

        $dir = 'd';
        $sqlDir = ' DESC';
        if (isset($_GET['d'])) {
            switch ($_GET['d']) {
                case 'a':
                    $dir = 'a';
                    $sqlDir = ' ASC';
                    break;
                case 'd':
                    $dir = 'd';
                    $sqlDir = ' DESC';
                    break;
            }
        }

        switch ($sort) {
            default:
                $sqlCount = 'SELECT count(pageid) AS count FROM media' . $where;
                $rawCount = $this->smt->database->queryAsArray($sqlCount);
                $resultSize = 0;
                if ($rawCount) {
                    $resultSize = $rawCount[0]['count'];
                }
                break;

            case 'random':
                $resultSize = $pageLimit;
                break;
        }

        $pager = '';
        $sqlOffset = '';

        $offset = isset($_GET['o']) ? $_GET['o'] : 0;

        $currentPage = ($offset / $pageLimit) + 1;
        $numberOfPages = ceil($resultSize / $pageLimit);

        if ($sort != 'random' && ($resultSize > $pageLimit)) {
            $sqlOffset = " OFFSET $offset";
            $pageCount = 0;
            $pager = '<small>page: ';
            for ($count = 0; $count < $resultSize; $count += $pageLimit) {
                $pageCount++;

                if ($currentPage == $pageCount) {
                    $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
                        . $this->pagerLink($count) . '&nbsp;' . $pageCount . '&nbsp;</a> </span>';
                    continue;
                }

                $edgeBuffer = 3; // always show first and last pages
                $buffer = 5; // always show pages before/after current page
                if ($pageCount <= $edgeBuffer
                    || $pageCount > ($numberOfPages-$edgeBuffer)
                    || (($pageCount > ($currentPage-$buffer)) && ($pageCount < ($currentPage+$buffer)))
                ) {
                    $pager .= $this->pagerLink($count) . '&nbsp;' . $pageCount . ' </a>';
                    continue;
                }

                if ($pageCount % 50 == 0) {
                    $pager .= $this->pagerLink($count) . '. </a>';
                }
            }
            $pager .= '</small>';
        }

        $sql = 'SELECT * FROM media';
        $sql .= $where . $orderby . $sqlDir . ' LIMIT ' . $pageLimit . $sqlOffset;

        $medias = $this->smt->database->queryAsArray($sql);

        $this->smt->title = 'Browse ' . number_format((float) $resultSize)
            . ' Files, sorted by ' . $sort . ' ' . $sqlDir
            . ', page #' . $currentPage . ' - ' . Config::$siteName;

        $this->smt->includeHeader();
        $this->smt->includeTemplate('MenuSmall');
        /** @noinspection PhpIncludeInspection */
        include($view);
        $this->smt->includeFooter();
    }

    /**
     * @param $offset
     * @return string
     */
    private function pagerLink($offset)
    {
        global $sort, $dir;
        $link = '<a href="?o=' . $offset;
        if ($sort) {
            $link .= '&amp;s=' . $sort;
        }
        if ($dir) {
            $link .= '&amp;d=' . $dir;
        }
        $link .= '">';

        return $link;
    }
}
