<?php
// Shared Media Tagger
// Sitemap

$cr = "\n";

$protocol = @$smt->get_protocol();
if( !$protocol ) { $protocol = 'http:'; }

header('Content-type: application/xml');

print '<?xml version="1.0" encoding="UTF-8"?>' . $cr
. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $cr;

print '<url><loc>' . $protocol . $smt->url('home') . '</loc>'
. '<lastmod>' . gmdate('Y-m-d') . '</lastmod>'
. '<changefreq>always</changefreq>'
. '</url>' . $cr;

url( $smt->url('about') );
url( $smt->url('categories') );
url( $smt->url('browse') );
url( $smt->url('reviews') );
url( $smt->url('users') );
url( $smt->url('contact') );

print $cr;

// all categories
$cats = $smt->query_as_array('
    SELECT DISTINCT(c2m.category_id), c.name
    FROM category2media AS c2m, category AS c
    WHERE c2m.category_id = c.id');
foreach( $cats as $cat ) {
    url( $smt->url('category') . '?c=' . $smt->category_urlencode($smt->strip_prefix($cat['name']) ) );
}

print $cr;

// all media files
$media = $smt->query_as_array('SELECT pageid FROM media');
foreach( $media as $pageid ) {
    url( $smt->url('info') . '?i=' . $pageid['pageid'] );
}

print $cr . '</urlset>' . $cr;

//////////////////////////////////////////////////////////
function url($loc) {
    global $protocol;
    print '<url><loc>' . $protocol . $loc . '</loc></url>';
}
