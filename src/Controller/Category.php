<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Category
 */
class Category extends ControllerBase
{
    protected function display()
    {
        $vars = $this->smt->router->getVars();
        if (empty($vars[0])) {
            $this->smt->fail404('404 Category Not Found');
        }

        $categoryName = Tools::categoryUrldecode($vars[0]);

        $pageLimit = 20; // # of files per page
        $this->smt->title = $categoryName . ' - ' . Config::$siteName;
        $categoryName = 'Category:' . $categoryName;

        $categoryInfo = $this->smt->database->getCategory($categoryName);

        if (!$categoryInfo) {
            $this->smt->fail404(
                '404 Category Not Found',
                $this->smt->displayAdminCategoryFunctions($categoryName)
            );
        }

        $categorySize = $this->smt->database->getCategorySize($categoryName);

        $pager = '';
        $sqlLimit = '';
        if ($categorySize > $pageLimit) {
            $offset = isset($_GET['o']) ? $_GET['o'] : 0;
            $sqlLimit = " LIMIT $pageLimit OFFSET $offset";
            $pageCount = 0;
            $pager = 'pages: ';
            for ($count = 0; $count < $categorySize; $count+=$pageLimit) {
                if ($count == $offset) {
                    $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
                        . '&nbsp;' . ++$pageCount . '&nbsp;</span> ';
                    continue;
                }
                $pager .= '<a href="?o=' . $count . '&amp;c='
                    . Tools::categoryUrlencode(Tools::stripPrefix($categoryName)) . '">'
                    . '&nbsp;' . ++$pageCount . '&nbsp;</a> ';
            }
        }

        $sql = 'SELECT m.*
                FROM category2media AS c2m, category AS c, media AS m
                WHERE c2m.category_id = c.id
                AND m.pageid = c2m.media_pageid
                AND c.name = :category_name';

        if (Config::$siteInfo['curation'] == 1 && !Tools::isAdmin()) {
            $sql .= " AND m.curated ='1'";
        }
        $sql .= " ORDER BY m.pageid ASC $sqlLimit";

        $bind = [':category_name'=>$categoryName];

        $category = $this->smt->database->queryAsArray($sql, $bind);

        if (!$category || !is_array($category)) {
            $this->smt->fail404(
                '404 Category In Curation Que',
                $this->smt->displayAdminCategoryFunctions($categoryName)
            );
        }


        $reviewsPerCategory = $this->smt->getReviewsPerCategory($categoryInfo['id']);
        $categoryNameDisplay = Tools::stripPrefix($categoryName);

        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();

        $view = $this->getView('Category');
        /** @noinspection PhpIncludeInspection */
        include($view);

        $this->smt->includeFooter();
    }
}
