<?php
/**
 * Shared Media Tagger
 * Category
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

use Attogram\SharedMedia\Tagger\Config;

$pageLimit = 20; // # of files per page

$categoryName = isset($_GET['c']) ? Tools::categoryUrldecode($_GET['c']) : false;

if (!$categoryName) {
    $smt->fail404('404 Category Name Not Found');
}

$smt->title = $categoryName . ' - ' . Config::$siteName;

$categoryName = 'Category:' . $categoryName;

$categoryInfo = $smt->database->getCategory($categoryName);

if (!$categoryInfo) {
    $smt->fail404(
        '404 Category Not Found',
        $smt->displayAdminCategoryFunctions($categoryName)
    );
}

$categorySize = $smt->database->getCategorySize($categoryName);

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

$sql = '
    SELECT m.*
    FROM category2media AS c2m, category AS c, media AS m
    WHERE c2m.category_id = c.id
    AND m.pageid = c2m.media_pageid
    AND c.name = :category_name';

if (Config::$siteInfo['curation'] == 1 && !Tools::isAdmin()) {
    $sql .= " AND m.curated ='1'";
}
$sql .= " ORDER BY m.pageid ASC $sqlLimit";

$bind = [':category_name'=>$categoryName];

$category = $smt->database->queryAsArray($sql, $bind);

if (!$category || !is_array($category)) {
    $smt->fail404(
        '404 Category In Curation Que',
        $smt->displayAdminCategoryFunctions($categoryName)
    );
}

$smt->includeHeader();
$smt->includeMediumMenu();

print '<div class="box white">'
    . '<div style="float:right; padding:0px 20px 4px 0px; font-size:80%;">'
        . $smt->getReviewsPerCategory($categoryInfo['id'])
    . '</div>'
    . '<h1>' . Tools::stripPrefix($categoryName) . '</h1>'
    . '<br /><b>' . $categorySize . '</b> files'
    . ($pager ? ', '.$pager : '')
    . '<br clear="all" />'
    ;

if (Tools::isAdmin()) {
    print '<form action="' . Tools::url('admin') .'media.php" method="GET" name="media">';
}

foreach ($category as $media) {
    print $smt->displayThumbnailBox($media);
}

if ($pager) {
    print '<p>' . $pager . '</p>';
}

if (Tools::isAdmin()) {
    print $smt->displayAdminCategoryFunctions($categoryName);
}

print '</div>';
$smt->includeFooter();
