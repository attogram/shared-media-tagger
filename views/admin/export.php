<?php
/**
 * Shared Media Tagger
 * Export Admin
 *
 * @var \Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Export Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white"><p>' . $smt->title . '</p>';
/////////////////////////////////////////////////////////

print '<ul>'
. '<li><a href="?r=network">Network Export</a></li>';

foreach ($smt->database->getTags() as $tag) {
    print '<li>MediaWiki Format: Tag Report: <a href="?r=tag&amp;i=' . $tag['id'] . '">' . $tag['name'] . '</a></li>';
}
print '</ul><hr />';

if (!isset($_GET['r'])) {
    print '</div>';
    $smt->includeFooter();
    exit;
}

switch ($_GET['r']) {
    default:
        break;
    case 'network':
        networkExport($smt);
        break;
    case 'tag':
        if (!empty($_GET['i'])) {
            tagReport($smt, $_GET['i']);
        }
        break;
}

print '</div>';
$smt->includeFooter();

/**
 * @param TaggerAdmin $smt
 */
function networkExport(TaggerAdmin $smt)
{
    $cr = "\n";
    $tab = "\t";
    $site = Config::$protocol . Config::$siteUrl;

    $export = 'SMT_NETWORK_SITE: ' . $site . $cr
    . 'SMT_DATETIME: ' . Tools::timeNow() . $cr
    . 'SMT_VERSION: ' . SHARED_MEDIA_TAGGER . $cr;

    $cats = $smt->database->queryAsArray('
        SELECT pageid, name
        FROM category
        WHERE local_files > 0
        ORDER BY name');
    foreach ($cats as $cat) {
        if (!$cat['pageid']) {
            $cat['pageid'] = 'NULL';
        }
        if (!$cat['name']) {
            $cat['name'] = 'NULL';
        }
        $export .= $cat['pageid'] . $tab . '14' . $tab . Tools::stripPrefix($cat['name']) . $cr;
    }
    unset($cats);

    $medias = $smt->database->queryAsArray('
        SELECT pageid, title
        FROM media
        ORDER BY title');
    foreach ($medias as $media) {
        if (!$media['pageid']) {
            $media['pageid'] = 'NULL';
        }
        if (!$media['title']) {
            $media['title'] = 'NULL';
        }
        $export .= $media['pageid'] . $tab . '6' . $tab . Tools::stripPrefix($media['title']) . $cr;
    }
    unset($medias);

    print '<textarea cols="90" rows="20">' . $export . '</textarea>';
}

/**
 * @param TaggerAdmin $smt
 * @param string $tagId
 * @return bool
 */
function tagReport(TaggerAdmin $smt, $tagId = '')
{
    if (!$tagId || !Tools::isPositiveNumber($tagId)) {
        Tools::error('Tag Report: Tag ID NOT FOUND');
        return false;
    }

    $tagName = $smt->database->getTagNameById($tagId);

    $sql = '
    SELECT m.title, t.count
    FROM media AS m, tagging AS t
    WHERE m.pageid = t.media_pageid
    AND t.tagId = :tagId
    LIMIT 200';
    $medias = $smt->database->queryAsArray($sql, [':tagId' => $tagId]);
    $cr = "\n";
    $reportName = 'Tag Report: ' . $tagName . ' - Top ' . sizeof($medias) . ' Files';

    print '<textarea cols="90" rows="20">'
    . '== ' . $reportName . ' ==' . $cr
    . '* Collection ID: <code>' . md5(Config::$siteName) . '</code>' . $cr
    . '* Collection Size: ' . number_format($smt->database->getImageCount()) . $cr
    . '* Created on: ' . Tools::timeNow() . ' UTC' . $cr
    . '* Created with: Shared Media Tagger v' . SHARED_MEDIA_TAGGER . $cr
    . '<gallery caption="' . $reportName . '" widths="100px" heights="100px" perrow="6">' . $cr;

    foreach ($medias as $media) {
        print $media['title'] . '|+' . $media['count'] . $cr;
    }
    print '</textarea>';
    return true;
}
