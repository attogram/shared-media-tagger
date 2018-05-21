<?php
// Shared Media Tagger
// Category

$page_limit = 20; // # of files per page

$category_name = isset($_GET['c']) ? $smt->category_urldecode($_GET['c']) : FALSE;

if( !$category_name ) {
    $smt->fail404('404 Category Name Not Found');
}

$smt->title = $category_name . ' - ' . $smt->site_name;

$category_name = 'Category:' . $category_name;

$category_info = $smt->get_category($category_name);

if( !$category_info ) {
    $smt->fail404(
        '404 Category Not Found',
        $smt->display_admin_category_functions($category_name)
    );
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
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . '&nbsp;' . ++$page_count . '&nbsp;</span> ';
            continue;
        }
        $pager .= '<a href="?o=' . $x . '&amp;c=' . $smt->category_urlencode($smt->strip_prefix($category_name)) . '">'
        . '&nbsp;' . ++$page_count . '&nbsp;</a> ';
    }
}

$sql = '
    SELECT m.*
    FROM category2media AS c2m, category AS c, media AS m
    WHERE c2m.category_id = c.id
    AND m.pageid = c2m.media_pageid
    AND c.name = :category_name';

if( $smt->site_info['curation'] == 1 && !$smt->is_admin() ) {
    $sql .= " AND m.curated ='1'";
}
$sql .= " ORDER BY m.pageid ASC $sql_limit";


$bind = array(':category_name'=>$category_name);

$category = $smt->query_as_array( $sql, $bind );

if( !$category || !is_array($category) ) {
    $smt->fail404(
        '404 Category In Curation Que',
        $smt->display_admin_category_functions($category_name)
    );
}

$smt->include_header();
$smt->include_medium_menu();


print '<div class="box white">'
    . '<div style="float:right; padding:0px 20px 4px 0px; font-size:80%;">'
        . $smt->get_reviews_per_category( $category_info['id'] )
    . '</div>'
    . '<h1>' . $smt->strip_prefix($category_name) . '</h1>'
    . '<br /><b>' . $category_size . '</b> files'
    . ($pager ? ', '.$pager : '')
    . '<br clear="all" />'
    ;

if( $smt->is_admin() ) {
    print '<form action="' . $smt->url('admin') .'media.php" method="GET" name="media">';
}

foreach( $category as $media ) {
    print $smt->display_thumbnail_box( $media );
}

if( $pager ) {
    print '<p>' . $pager . '</p>';
}

if( $smt->is_admin() ) {
    print $smt->display_admin_category_functions($category_name);
}

print '</div>';
$smt->include_footer();
