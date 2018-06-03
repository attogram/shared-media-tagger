<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Sitemap
 *
 * @var array $data
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<url>
    <loc><?= $data['protocol'] . Tools::url('home') ?></loc>
    <lastmod><?= $data['time'] ?></lastmod>
    <changefreq>always</changefreq>
</url>
<url><loc><?= $data['protocol'] . Tools::url('about') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('categories') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('browse') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('reviews') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('users') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('contact') ?></loc></url>
<?php

foreach ($data['categories'] as $category) {
    print '<url><loc>' . $data['protocol']
        . Tools::url('category') . '?c='
        . Tools::categoryUrlencode(Tools::stripPrefix($category['name']))
        . '</loc></url>';
}

foreach ($data['media'] as $media) {
    print '<url><loc>' . $data['protocol']
        . Tools::url('info') . '?i=' . $media['pageid']
        . '</loc></url>';
}

?>
</urlset>
