<?php
// Shared Media Tagger
// Categories

$page_limit = 10000;

$init = __DIR__.'/smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$search = FALSE;
if( isset($_GET['s']) && $_GET['s'] ) {
    $search = $_GET['s'];
}

$hidden = 0;
if( isset($_GET['h']) && $_GET['h'] ) {
    $hidden = 1;
}

$smt = new smt();

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
$sql .= ' ORDER BY local_files DESC';
$sql .= ' LIMIT ' . $page_limit;

$categories = $smt->query_as_array($sql, $bind);

$page_name = number_format(sizeof($categories));
if( $hidden ) { $page_name .= ' Technical'; } else { $page_name .= ' Active'; }
$page_name .= ' Categories';

$smt->title = $page_name . ' - ' . $smt->site_name;

$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );

$smt->start_timer('print_category_table');

?><div class="box white">


<div style="padding:10px 0px 10px 0px; float:right;"><form method="GET">
<a href="<?php print $smt->url('categories'); ?>" style="font-size:80%;">Active</a> &nbsp;
<a href="<?php print $smt->url('categories'); ?>?h=1"  style="font-size:80%;">Tech</a> &nbsp;
<?php if( $hidden ) { print '<input type="hidden" name="h" value="1">'; } ?>
<input type="text" name="s" value="<?php $search ? print htmlentities(urldecode($search)) : print ''; ?>" size="16">
<input type="submit" value="search">
</form></div>
<br />


<?php

print '<div class="cattable">'
. '<div class="catcon">'
. '<div class="catfiles cathead">Files</div>'
. '<div class="catname cathead">' . $page_name . '</div>'
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

$smt->end_timer('print_category_table');

print '<br /><br />'
. '<p class="center" style="padding:10px;">'
. '<a href="' . $smt->url('categories') . '">Active Categories</a>'
. '  -  <a href="' . $smt->url('categories') . '?h=1">Technical Categories</a>'
. '</p><br /><br />'
. '</div>';

$smt->include_footer();
