<?php
/**
 * Shared Media Tagger
 * Categories
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

$pageLimit = 1000;

$search = false;
if (isset($_GET['s']) && $_GET['s']) {
    $search = $_GET['s'];
}

$hidden = 0;
if (isset($_GET['h']) && $_GET['h']) {
    $hidden = 1;
}

$categorySize = $smt->database->getCategoriesCount(false, $hidden);
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

$categories = $smt->database->queryAsArray($sql, $bind);

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
$smt->title = $pageName . ' - ' . Config::$siteName;

$smt->includeHeader();
$smt->includeMediumMenu();

?><div class="box white">
<div style="padding:10px 0px 10px 0px;float:right;"><form method="GET">
<a href="<?php print Tools::url('categories'); ?>" style="font-size:80%;">Active</a> &nbsp;
<a href="<?php print Tools::url('categories'); ?>?h=1"  style="font-size:80%;">Tech</a> &nbsp;
<?php
if ($hidden) {
    print '<input type="hidden" name="h" value="1">';
} ?>
<input type="text" name="s" value="<?php
    $search ? print htmlentities((string) urldecode($search)) : print '';
?>" size="16">
<input type="submit" value="search">
</form></div>
<?php

print $pager;

print '<div class="cattable">'
. '<div class="catcon">'
. '<div class="catfiles cathead">Files</div>'
. '<div class="catname cathead">Category</div>'
. '</div>';

ob_flush();
flush();

foreach ($categories as $category) {
    $localUrl = Tools::url('category') . '?c='
        . Tools::categoryUrlencode(Tools::stripPrefix(@$category['name']));
    print '<div class="catcon">'
    . '<div class="catfiles">' . number_format((float) @$category['local_files']) . '</div>'
    . '<div class="catname" onclick="window.location=\'' . $localUrl . '\'">'
    . '<a href="' . $localUrl . '">' . Tools::stripPrefix(@$category['name']) . '</a>'
    . '</div>'
    . '</div>';
    ob_flush();
    flush();
}
print '</div>';
print '<br />' . $pager;
print '<br /><br />'
. '<p class="center" style="padding:10px;">'
. '<a href="' . Tools::url('categories') . '">Active Categories</a>'
. '  -  <a href="' . Tools::url('categories') . '?h=1">Technical Categories</a>'
. '</p><br /><br />'
. '</div>';

$smt->includeFooter();
