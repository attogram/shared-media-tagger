<?php
// Shared Media Tagger
// Categories

$page_limit = 1000;

//////////////////////////////////////////////////////////////////
$init = __DIR__.'/admin/src/smt.php'; // Shared Media Tagger Class
if( !is_readable($init) ) { exit('Site down for maintenance'); }
require_once($init);
$smt = new smt(); // Shared Media Tagger Object
//////////////////////////////////////////////////////////////////

$search = FALSE;
if( isset($_GET['s']) && $_GET['s'] ) {
    $search = $_GET['s'];
}

$hidden = 0;
if( isset($_GET['h']) && $_GET['h'] ) {
    $hidden = 1;
}

$smt = new smt();

$category_size = $smt->get_categories_count(/*redo*/FALSE, $hidden);
// todo - get real selection size, not full category count

$pager = '';
$sql_limit = '';
if( $category_size > $page_limit ) {
    $offset = isset($_GET['o']) ? $_GET['o'] : 0;
    $sql_limit = " LIMIT $page_limit OFFSET $offset";
    $page_count = 0;
    $pager = ': ';
    for( $x = 0; $x < $category_size; $x+=$page_limit ) {
        if( $x == $offset ) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . '&nbsp;' . ++$page_count . '&nbsp;</span> ';
            $pager_count = $page_count;
            continue;
        }
        $pager .= '<a href="?o=' . $x
        . ($hidden ? '&amp;h=1' : '')
        //. ($search ? '&amp;s=' . urlencode($search) : '')
        . '">&nbsp;' . ++$page_count . '&nbsp;</a> ';
    }
}
$pager = '<b>' . number_format($category_size) . '</b> '
. ($hidden ? 'Technical' : 'Active') . ' Categories' . $pager;

$bind = array();
$sql = 'SELECT id, name, local_files, hidden
        FROM category
        WHERE local_files > 0';
if( $hidden ) {
    $sql .= ' AND hidden > 0';
} else {
    $sql .= ' AND hidden < 1';
}
if( $search ) {
    $sql .= ' AND name LIKE :search';
    $bind[':search'] = '%' . $search . '%';
}
$sql .= ' ORDER BY local_files DESC, name ';
$sql .= $sql_limit;

$categories = $smt->query_as_array($sql, $bind);

$page_name = number_format($category_size);
if( $hidden ) {
    $page_name .= ' Technical';
} else {
    $page_name .= ' Active';
}
$page_name .= ' Categories';
if( isset($pager_count) ) {
    $page_name .= ', page #' . $pager_count;
}
$smt->title = $page_name . ' - ' . $smt->site_name;

$smt->include_header();
$smt->include_medium_menu();

$smt->start_timer('print_category_table');
?><div class="box white">
<div style="padding:10px 0px 10px 0px; float:right;"><form method="GET">
<a href="<?php print $smt->url('categories'); ?>" style="font-size:80%;">Active</a> &nbsp;
<a href="<?php print $smt->url('categories'); ?>?h=1"  style="font-size:80%;">Tech</a> &nbsp;
<?php if( $hidden ) { print '<input type="hidden" name="h" value="1">'; } ?>
<input type="text" name="s" value="<?php $search ? print htmlentities(urldecode($search)) : print ''; ?>" size="16">
<input type="submit" value="search">
</form></div>
<?php

print $pager;

print '<div class="cattable">'
. '<div class="catcon">'
. '<div class="catfiles cathead">Files</div>'
. '<div class="catname cathead">Category</div>'
. '</div>';

ob_flush(); flush();

foreach( $categories as $category ) {
    $local_url = $smt->url('category') . '?c=' . $smt->category_urlencode($smt->strip_prefix(@$category['name']));
    print '<div class="catcon">'
    . '<div class="catfiles">' . number_format(@$category['local_files']) . '</div>'
    . '<div class="catname" onclick="window.location=\'' . $local_url . '\'">'
    . '<a href="' . $local_url . '">' . $smt->strip_prefix(@$category['name']) . '</a>'
    . '</div>'
    . '</div>';
    ob_flush(); flush();
}
print '</div>';

print '<br />' . $pager;

$smt->end_timer('print_category_table');

print '<br /><br />'
. '<p class="center" style="padding:10px;">'
. '<a href="' . $smt->url('categories') . '">Active Categories</a>'
. '  -  <a href="' . $smt->url('categories') . '?h=1">Technical Categories</a>'
. '</p><br /><br />'
. '</div>';

$smt->include_footer();
