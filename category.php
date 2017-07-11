<?php
// Shared Media Tagger
// Category

$page_limit = 25; // # of files per page

$f = __DIR__.'/smt.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$smt = new smt('Category');

$category_name = isset($_GET['c']) ? $smt->category_urldecode($_GET['c']) : FALSE;

if( !$category_name ) {
    $smt->fail404('404 Category Not Found');
}

$smt->title = $category_name;

$category_name = 'Category:' . $category_name;

$category_info = $smt->get_category($category_name);

if( !$category_info ) {
    $smt->fail404('404 Category Not Found');
}

$category_size = $smt->get_category_size( $category_name );
$pager = '';

$sql = '
    SELECT m.*
    FROM category2media AS c2m, category AS c, media AS m
    WHERE c2m.category_id = c.id
    AND m.pageid = c2m.media_pageid
    AND c.name = :category_name
    ORDER BY m.pageid ASC
';

if( $category_size > $page_limit ) {
    $offset = isset($_GET['o']) ? $_GET['o'] : 0;
    $sql .= " LIMIT $page_limit OFFSET $offset";
    $pager = 'Show files: ';
    for( $x = 0; $x < $category_size; $x+=$page_limit ) {
        $end = $x + $page_limit;
        if ($end > $category_size) {
            $end = $category_size;
        }
        $pager .= '<a href="?o=' . $x 
        . '&amp;c=' . $smt->category_urlencode( $smt->strip_prefix($category_name) ) . '">' 
        . ($x+1) . '-' . $end . '</a>, ';
    }
} 

$bind = array(':category_name'=>$category_name);

$i = $smt->query_as_array( $sql, $bind );

if( !$i || !is_array($i) ) {
    $smt->fail404('404 Category Not Found');
}

$smt->include_header();
$smt->include_menu();

print '<div class="box white">'
    . '<p>'
    . '<div style="float:left; padding:0px 20px 4px 0px; font-size:80%;">' 
        . $smt->get_reviews_per_category( $category_info['id'] )
    . '</div>'
    . '<h1>' 
    . str_replace( 
        'Category:',
        '<a href="' . $smt->url('categories') . '">Category:</a> ',
        $category_name)
    . '</h1>'
    . '<br /><b>' . $category_size 
    . '</b> files under review, out of '
    . $category_info['files']
    . ' files on '
    . '<a target="commons" href="https://commons.wikimedia.org/wiki/'
    . $smt->category_urlencode($category_name) . '">Commons</a>'
    . '<br />'
    . $pager
    . '</p><br clear="all" />'
    ;

if( $smt->is_admin() ) {
    print '<form action="' . $smt->url('admin') .'media.php" method="GET">';
}

foreach( $i as $media ) {
    print $smt->display_thumbnail_box( $media );
}
 
if( $smt->is_admin() ) {
    print '<p><input type="submit" value="Delete selected media"></p></form>';
}

print '<p>' . $pager . '</p>';

print '</div>';
$smt->include_footer();
