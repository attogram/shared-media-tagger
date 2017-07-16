<?php
// Shared Media Tagger
// Sitemap

header('Content-type: application/xml');

print '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
';

$init = __DIR__.'/smt.php';
if( !file_exists($init) || !is_readable($init) ){
    print '</urlset>';
    exit;
}
require_once($init);

$smt = new smt();

$cr = ''; // "\n";

$protocol = $smt->get_protocol();
    
print '<url><loc>' . $protocol . $smt->url('home') . '</loc>'
. '<lastmod>' . gmdate('Y-m-d') . '</lastmod>'
. '<changefreq>always</changefreq>'
. '</url>' . $cr;

url( $smt->url('about') );
url( $smt->url('reviews') );
// TODO - all review report pages
url( $smt->url('users') );
// TODO - all users report pages
url( $smt->url('contact') );
url( $smt->url('categories') );

// all categories
$cats = $smt->query_as_array('
    SELECT DISTINCT(c2m.category_id), c.name
    FROM category2media AS c2m, category AS c
    WHERE c2m.category_id = c.id');
foreach( $cats as $cat ) {
    url( $smt->url('category') . '?c=' . $smt->category_urlencode($smt->strip_prefix($cat['name']) ) );
}

// all media files
$media = $smt->query_as_array('SELECT pageid FROM media');
foreach( $media as $pageid ) {
    url( $smt->url('info') . '?i=' . $pageid['pageid'] );
}

print '</urlset>' . $cr;

//////////////////////////////////////////////////////////
function url($loc) {
    global $protocol;
    print '<url><loc>' . $protocol . $loc . '</loc></url>' . "\n";
}
