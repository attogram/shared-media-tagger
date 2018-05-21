<?php
/**
 * Shared Media Tagger
 * Categories
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTagger $smt
 */

$pageLimit = 1000;

$search = false;
if (isset($_GET['s']) && $_GET['s']) {
    $search = $_GET['s'];
}

$hidden = 0;
if (isset($_GET['h']) && $_GET['h']) {
    $hidden = 1;
}

$categorySize = $smt->getCategoriesCount(false, $hidden); // @TODO get real selection size, not full category count

$pager = '';
$sqlLimit = '';
if ($categorySize > $pageLimit) {
    $offset = isset($_GET['o']) ? $_GET['o'] : 0;
    $sqlLimit = " LIMIT $pageLimit OFFSET $offset";
    $pageCount = 0;
    $pager = ': ';
    for ($x = 0; $x < $categorySize; $x += $pageLimit) {
        if ($x == $offset) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . '&nbsp;' . ++$pageCount . '&nbsp;</span> ';
            $pagerCount = $pageCount;
            continue;
        }
        $pager .= '<a href="?o=' . $x
        . ($hidden ? '&amp;h=1' : '')
        . '">&nbsp;' . ++$pageCount . '&nbsp;</a> ';
    }
}
$pager = '<b>' . number_format($categorySize) . '</b> '
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

$categories = $smt->queryAsArray($sql, $bind);

$pageName = number_format($categorySize);
if ($hidden) {
    $pageName .= ' Technical';
} else {
    $pageName .= ' Active';
}
$pageName .= ' Categories';
if (isset($pagerCount)) {
    $pageName .= ', page #' . $pagerCount;
}
$smt->title = $pageName . ' - ' . $smt->siteName;

$smt->includeHeader();
$smt->includeMediumMenu();

?><div class="box white">
<div style="padding:10px 0px 10px 0px; float:right;"><form method="GET">
<a href="<?php print $smt->url('categories'); ?>" style="font-size:80%;">Active</a> &nbsp;
<a href="<?php print $smt->url('categories'); ?>?h=1"  style="font-size:80%;">Tech</a> &nbsp;
<?php
if ($hidden) {
    print '<input type="hidden" name="h" value="1">';
} ?>
<input type="text" name="s" value="<?php
    $search ? print htmlentities(urldecode($search)) : print '';
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
    $localUrl = $smt->url('category') . '?c='
        . $smt->categoryUrlencode($smt->stripPrefix(@$category['name']));
    print '<div class="catcon">'
    . '<div class="catfiles">' . number_format(@$category['local_files']) . '</div>'
    . '<div class="catname" onclick="window.location=\'' . $localUrl . '\'">'
    . '<a href="' . $localUrl . '">' . $smt->stripPrefix(@$category['name']) . '</a>'
    . '</div>'
    . '</div>';
    ob_flush();
    flush();
}
print '</div>';
print '<br />' . $pager;
print '<br /><br />'
. '<p class="center" style="padding:10px;">'
. '<a href="' . $smt->url('categories') . '">Active Categories</a>'
. '  -  <a href="' . $smt->url('categories') . '?h=1">Technical Categories</a>'
. '</p><br /><br />'
. '</div>';

$smt->includeFooter();
