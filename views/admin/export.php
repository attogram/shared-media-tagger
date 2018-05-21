<?php
/**
 * Shared Media Tagger
 * Export Admin
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin;

$smt->title = 'Export Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>' . $smt->title . '</p>';
/////////////////////////////////////////////////////////

print '<ul>'
. '<li><a href="?r=network">Network Export</a></li>';

foreach ($smt->get_tags() as $tag) {
    print '<li>MediaWiki Format: Tag Report: <a href="?r=tag&amp;i=' . $tag['id'] . '">' . $tag['name'] . '</a></li>';
}
print '<li><a href="?r=skin">MediaWiki Format: Skin Percentage Report</a></li>';
print '</ul><hr />';

switch (@$_GET['r']) {
    default:
        break;
    case 'network':
        networkExport($smt);
        break;
    case 'skin':
        skinReport($smt);
        break;
    case 'tag':
        if (!empty($_GET['i'])) {
            tagReport($smt, $_GET['i']);
        }
        break;
}

print '</div>';
$smt->include_footer();

/**
 * @param SharedMediaTaggerAdmin $smt
 */
function networkExport(SharedMediaTaggerAdmin $smt)
{
    $cr = "\n";
    $tab = "\t";
    $site = $smt->get_protocol() . $smt->site_url;

    $export = 'SMT_NETWORK_SITE: ' . $site . $cr
    . 'SMT_DATETIME: ' . $smt->time_now() . $cr
    . 'SMT_VERSION: ' . __SMT__ . $cr;

    $cats = $smt->query_as_array('
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
        $export .= $cat['pageid'] . $tab . '14' . $tab . $smt->strip_prefix($cat['name']) . $cr;
    }
    unset($cats);

    $medias = $smt->query_as_array('
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
        $export .= $media['pageid'] . $tab . '6' . $tab . $smt->strip_prefix($media['title']) . $cr;
    }
    unset($medias);

    print '<textarea cols="90" rows="20">' . $export . '</textarea>';
}

/**
 * @param SharedMediaTaggerAdmin $smt
 * @param string $tagId
 * @return bool
 */
function tagReport(SharedMediaTaggerAdmin $smt, $tagId = '')
{
    if (!$tagId || !$smt->is_positive_number($tagId)) {
        $smt->error('Tag Report: Tag ID NOT FOUND');
        return false;
    }

    $tagName = $smt->get_tag_name_by_id($tagId);

    $sql = '
    SELECT m.title, t.count
    FROM media AS m, tagging AS t
    WHERE m.pageid = t.media_pageid
    AND t.tagId = :tagId
    LIMIT 200';
    $medias = $smt->query_as_array($sql, [':tagId' => $tagId]);
    $cr = "\n";
    $reportName = 'Tag Report: ' . $tagName . ' - Top ' . sizeof($medias) . ' Files';

    print '<textarea cols="90" rows="20">'
    . '== ' . $reportName . ' ==' . $cr
    . '* Collection ID: <code>' . md5($smt->site_name) . '</code>' . $cr
    . '* Collection Size: ' . number_format($smt->get_image_count()) . $cr
    . '* Created on: ' . $smt->time_now() . ' UTC' . $cr
    . '* Created with: Shared Media Tagger v' . __SMT__ . $cr
    . '<gallery caption="' . $reportName . '" widths="100px" heights="100px" perrow="6">' . $cr;

    foreach ($medias as $media) {
        print $media['title'] . '|+' . $media['count'] . $cr;
    }
    print '</textarea>';
    return true;
}

/**
 * @param SharedMediaTaggerAdmin $smt
 */
function skinReport(SharedMediaTaggerAdmin $smt)
{
    $sql = 'SELECT title, skin FROM media ORDER BY skin DESC LIMIT 200';
    $medias = $smt->query_as_array($sql);
    $cr = "\n";
    print '<textarea cols="90" rows="20">'
    . '== Skin Percentage Report ==' . $cr
    . '* Collection ID: <code>' . md5($smt->site_name) . '</code>' . $cr
    . '* Collection Size: ' . number_format($smt->get_image_count()) . $cr
    . '* Algorithm: Image_FleshSkinQuantifier / YCbCr Space Color Model / J. Marcial-Basilio et al. (2011) ' . $cr
    . '* Created on: ' . $smt->time_now() . ' UTC' . $cr
    . '* Created with: Shared Media Tagger v' . __SMT__ . $cr
    . '<gallery caption="Skin Percentage Report - Top ' . sizeof($medias)
        . ' Files" widths="100px" heights="100px" perrow="6">' . $cr;

    foreach ($medias as $media) {
        print $media['title'] . '|' . $media['skin'] . ' %' . $cr;
    }
    print '</gallery></textarea>';
}
