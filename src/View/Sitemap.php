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
<url><loc><?= $data['protocol'] . Tools::url('topics') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('browse') ?></loc></url>
<url><loc><?= $data['protocol'] . Tools::url('scores') ?></loc></url>
<?php

foreach ($data['topics'] as $topic) {
    print '<url><loc>' . $data['protocol']
        . Tools::url('topic') . '/'
        . Tools::topicUrlencode(Tools::stripPrefix($topic['name']))
        . '</loc></url>';
}

foreach ($data['media'] as $media) {
    print '<url><loc>' . $data['protocol']
        . Tools::url('info') . '/' . $media['pageid']
        . '</loc></url>';
}

?>
</urlset>
