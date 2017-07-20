<?php
// Shared Media Tagger
// Category

$page_limit = 25; // # of files per page

$init = __DIR__.'/smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$smt = new smt();

$category_name = isset($_GET['c']) ? $smt->category_urldecode($_GET['c']) : FALSE;

if( !$category_name ) {
    $smt->fail404('404 Category Name Not Found');
}

$smt->title = $category_name . ' - ' . $smt->site_name;

$category_name = 'Category:' . $category_name;

$category_info = $smt->get_category($category_name);

if( !$category_info ) {
    $smt->fail404('404 Category Not Found');
}

$category_size = $smt->get_category_size( $category_name );

$pager = '';
$sql_limit = '';
if( $category_size > $page_limit ) {
    $offset = isset($_GET['o']) ? $_GET['o'] : 0;
    $sql_limit = " LIMIT $page_limit OFFSET $offset";
    $page_count = 0;
    $pager = 'pages: ';
    for( $x = 0; $x < $category_size; $x+=$page_limit ) {
        if( $x == $offset ) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">&nbsp;'
            . ++$page_count . '&nbsp;</span>';
            continue;
        }
        $pager .= '<a href="?o=' . $x . '&amp;c='
        . $smt->category_urlencode($smt->strip_prefix($category_name))
        . '">&nbsp;' . ++$page_count . '&nbsp;</a>';
    }
}

$sql = '
    SELECT m.*
    FROM category2media AS c2m, category AS c, media AS m
    WHERE c2m.category_id = c.id
    AND m.pageid = c2m.media_pageid
    AND c.name = :category_name
    ORDER BY m.pageid ASC
    ' . $sql_limit;
$bind = array(':category_name'=>$category_name);

$category = $smt->query_as_array( $sql, $bind );

if( !$category || !is_array($category) ) {
    $smt->fail404('404 Category Not Found');
}

$smt->include_header();
$smt->include_menu();

print '<div class="box white">'
    . '<p>'
    . '<div style="float:right; padding:0px 20px 4px 0px; font-size:80%;">'
        . $smt->get_reviews_per_category( $category_info['id'] )
    . '</div>'
    . '<h1>'
    . $smt->strip_prefix($category_name)
    . '</h1>'
    . '<br /><b>' . $category_size
    . '</b> files'
    . ($pager ? ', '.$pager : '')
    . '</p><br clear="all" />'
    ;

if( $smt->is_admin() ) {
    print '<form action="' . $smt->url('admin') .'media.php" method="GET">';
}

foreach( $category as $media ) {
    print $smt->display_thumbnail_box( $media );
}


if( $pager ) {
    print '<p>' . $pager . '</p>';
}

if( $smt->is_admin() ) {
	$category = $smt->get_category($category_name);
    print '
<br />
<br />
<p>Admin: 

<br />Local files: ' . $category_size . '
<br />Commons files: ' . @$category['files'] . '

<br />Local ID: ' . @$category['id'] . '
<br />Commons PageID: ' . @$category['pageid'] . '
<br />Commons subcats: ' . @$category['subcats'] . '

<br /><a target="commons" href="https://commons.wikimedia.org/wiki/' . $smt->category_urlencode($category['name']) . '">VIEW ON COMMONS</a>

<br />
<br /><input type="submit" value="Delete selected media">

<br />
<br /><a href="' . $smt->url('admin') . 'category.php/?c=' . $smt->category_urlencode($category['name']) . '">Get Category Info</a>
<br />
<br /><a href="' . $smt->url('admin') . 'category.php/?i=' . $smt->category_urlencode($category['name']) . '">Import Media to Category</a>
<br />
<br /><a href="' . $smt->url('admin') . 'media.php?dc=' . $smt->category_urlencode($category['name']) 
. '" onclick="return confirm(\'Confirm: Clear Media from ' . $category['name']. ' ?\');">Clear Media from Category</a>
<br />
<br /><a href="' . $smt->url('admin') . 'category.php/?d=' . urlencode($category['id']) 
. '" onclick="return confirm(\'Confirm: Delete ' . $category['name']. ' ?\');">Delete Category</a>
</form>
</p>';

}


print '</div>';
$smt->include_footer();
