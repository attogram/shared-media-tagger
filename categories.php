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
    $sql .= ' AND hidden = 1';
} else {
    $sql .= ' AND hidden != 1';
}
if( $search ) {
    $sql .= ' AND name LIKE %:search%';
    $bind[':search'] = $search;
}
$sql .= ' ORDER BY local_files DESC';
$sql .= ' LIMIT ' . $page_limit;

$categories = $smt->query_as_array($sql, $bind);

$smt->title = number_format(sizeof($categories));
if( $hidden ) { $smt->title .= ' Technical'; }
$smt->title .= ' Categories - ' . $smt->site_name;

$smt->include_header();
$smt->include_menu();
?>

<div class="box white">
<?php

$smt->start_timer('print_category_table');

print '';
?>
<div style="padding:10px 0px 0px 0px; float:right; display:inline-block;"><form method="GET">

<a href="<?php print $smt->url('categories'); ?>" style="font-size:80%;">Active</a>
&nbsp;
<a href="<?php print $smt->url('categories'); ?>?h=1"  style="font-size:80%;">Tech</a>
&nbsp;
&nbsp;


<?php if( $hidden ) { print '<input type="hidden" name="h" value="1">'; } ?>
<input type="text" name="s" value="<?php $search ? print htmlentities(urldecode($search)) : print ''; ?>" size="20">
<input type="submit" value="search">
</form></div>
<p style="display:inline-block;"><b><?php print $smt->title; ?></b></p>
<style>
.catcon {
    width:100%;
    margin:0;
    padding:0;
    border:1px solid #eee;
}
.catfiles {
    display:inline-block;
    min-width:42px;
    padding:0px 25px 0px 10px;
    margin:0;
    text-align:right;
    font-size:90%;
}
.catname {
    display:inline;
    padding:0;
    margin:0;
}
.cathead {
    font-weight:bold;
    font-size:80%;
}
</style>
<?php

print '<div class="catcon"><div class="catfiles cathead"># Files</div><div class="catname cathead">Category</div></div>';
ob_flush(); flush();

foreach( $categories as $category ) {

    $local_url = $smt->url('category') . '?c=' . $smt->category_urlencode($smt->strip_prefix(@$category['name']));

    print '<div class="catcon">'
    . '<div class="catfiles">'
    //. '<a href="' . $local_url . '">'
    . number_format(@$category['local_files'])
    //. '</a>'
    . '</div><div class="catname">'
    . '<a href="' . $local_url . '">' . $smt->strip_prefix(@$category['name']) . '</a>'
    . '</div>'
    . '</div>';
    ;
    ob_flush(); flush();

}

$smt->end_timer('print_category_table');

print '<br /><br />'
. '<p class="center" style="padding:10px;">'
. 'View: <a href="' . $smt->url('categories') . '">Active Categories</a>'
. '  -  '
. '<a href="' . $smt->url('categories') . '?h=1">Technical Categories</a>'
. '</p>';

?>
<br /><br />
</div>
<?php
$smt->include_footer();


