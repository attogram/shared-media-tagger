<?php
/**
 * Shared Media Tagger
 * Sitemap
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

$cr = "\n";

$protocol = Config::$protocol;
if (!$protocol) {
    $protocol = 'http:';
}

header('Content-type: application/xml');

print '<?xml version="1.0" encoding="UTF-8"?>' . $cr
. '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . $cr;

print '<url><loc>' . $protocol . Tools::url('home') . '</loc>'
. '<lastmod>' . gmdate('Y-m-d') . '</lastmod>'
. '<changefreq>always</changefreq>'
. '</url>' . $cr;

printUrl(Tools::url('about'));
printUrl(Tools::url('categories'));
printUrl(Tools::url('browse'));
printUrl(Tools::url('reviews'));
printUrl(Tools::url('users'));
printUrl(Tools::url('contact'));

print $cr;

// all categories
$cats = $smt->database->queryAsArray('
    SELECT DISTINCT(c2m.category_id), c.name
    FROM category2media AS c2m, category AS c
    WHERE c2m.category_id = c.id');
foreach ($cats as $cat) {
    printUrl(Tools::url('category') . '?c=' . Tools::categoryUrlencode(Tools::stripPrefix($cat['name'])));
}

print $cr;

// all media files
$media = $smt->database->queryAsArray('SELECT pageid FROM media');
foreach ($media as $pageid) {
    printUrl(Tools::url('info') . '?i=' . $pageid['pageid']);
}

print $cr . '</urlset>' . $cr;

/**
 * @param string $loc
 */
function printUrl($loc)
{
    global $protocol;
    print '<url><loc>' . $protocol . $loc . '</loc></url>';
}
