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


print '<url><loc>' . $smt->url('home') . '</loc>'
. '<lastmod>' . gmdate('Y-m-d') . '</lastmod>'
. '<changefreq>always</changefreq>'
. '</url>';

print '<url><loc>' . $smt->url('about') . '</loc></url>';
print '<url><loc>' . $smt->url('reviews') . '</loc></url>';
print '<url><loc>' . $smt->url('users') . '</loc></url>';
print '<url><loc>' . $smt->url('contact') . '</loc></url>';

print '<url><loc>' . $smt->url('categories') . '</loc></url>';
// all categories
$cats = $smt->query_as_array('
    SELECT DISTINCT(c2m.category_id), c.name
    FROM category2media AS c2m, category AS c
    WHERE c2m.category_id = c.id');
foreach( $cats as $cat ) {
    print '<url><loc>'
    . $smt->url('category') . '?c=' . $smt->category_urlencode($smt->strip_prefix($cat['name']))
    . '</loc></url>';
}

// all media files
$media = $smt->query_as_array('SELECT pageid FROM media');
foreach( $media as $pageid ) {
    print '<url><loc>'
    . $smt->url('info') . '?i=' . $pageid['pageid']
    . '</loc></url>';
}

print '
</urlset>';
