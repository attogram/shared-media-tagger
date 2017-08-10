<?php
// Shared Media Tagger
// Export Admin

////////////////////////////////////////////////////////////////
$init = __DIR__.'/src/smt.php'; // Shared Media Tagger Class
if( !is_readable($init) ) { exit('Site down for maintenance'); }
require_once($init);
$init = __DIR__.'/src/smt-admin.php'; // SMT ADMIN Class
if( !is_readable($init) ) { exit('Site down for maintenance'); }
require_once($init);
$smt = new smt_admin(); // Shared Media Tagger Admin Object
///////////////////////////////////////////////////////////////


$smt->title = 'Export Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>' . $smt->title . '</p>';
/////////////////////////////////////////////////////////

print '<ul>'
. '<li><a href="?r=network">Network Export</a></li>';

foreach( $smt->get_tags() as $tag ) {
    print '<li>MediaWiki Format: Tag Report: <a href="?r=tag&amp;i=' . $tag['id'] . '">' . $tag['name'] . '</a></li>';
}
print '<li><a href="?r=skin">MediaWiki Format: Skin Percentage Report</a></li>';
print '</ul><hr />';

switch( @$_GET['r'] ) {
    default: break;
    case 'network': network_export(); break;
    case 'skin': skin_report(); break;
    case 'tag': tag_report(@$_GET['i']); break;
}



print '</div>';
$smt->include_footer();


//////////////////////////////////////////////
function network_export() {
    global $smt;

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
    foreach( $cats as $cat ) {
        if( !$cat['pageid'] ) { $cat['pageid'] = 'NULL'; }
        if( !$cat['name'] ) { $cat['name'] = 'NULL'; }
        $export .= $cat['pageid'] . $tab . '14' . $tab . $smt->strip_prefix($cat['name']) . $cr;
    }
    unset($cats);

    $medias = $smt->query_as_array('
        SELECT pageid, title
        FROM media
        ORDER BY title');
    foreach( $medias as $media ) {
        if( !$media['pageid'] ) { $media['pageid'] = 'NULL'; }
        if( !$media['title'] ) { $media['title'] = 'NULL'; }
        $export .= $media['pageid'] . $tab . '6' . $tab . $smt->strip_prefix($media['title']) . $cr;
    }
    unset($medias);

    print '<textarea cols="90" rows="20">' . $export . '</textarea>';
}

//////////////////////////////////////////////
function tag_report( $tag_id='' ) {
    global $smt;
    if( !$tag_id || !$smt->is_positive_number($tag_id) ) {
        $smt->error('Tag Report: Tag ID NOT FOUND');
        return FALSE;
    }

    $tag_name = $smt->get_tag_name_by_id( $tag_id );

    $sql = '
    SELECT m.title, t.count
    FROM media AS m, tagging AS t
    WHERE m.pageid = t.media_pageid
    AND t.tag_id = :tag_id
    LIMIT 200';
    $medias = $smt->query_as_array($sql, array(':tag_id'=>$tag_id));
    $cr = "\n";
    $report_name = 'Tag Report: ' . $tag_name . ' - Top ' . sizeof($medias) . ' Files';

    print '<textarea cols="90" rows="20">'
    . '== ' . $report_name . ' ==' . $cr
    . '* Collection ID: <code>' . md5($smt->site_name) . '</code>' . $cr
    . '* Collection Size: ' . number_format($smt->get_image_count()) . $cr
    . '* Created on: ' . $smt->time_now() . ' UTC' . $cr
    . '* Created with: Shared Media Tagger v' . __SMT__ . $cr
    . '<gallery caption="' . $report_name . '" widths="100px" heights="100px" perrow="6">' . $cr;

    foreach( $medias as $media ) {
        print $media['title'] . '|+' . $media['count'] . $cr;
    }
    print '</textarea>';
}

//////////////////////////////////////////////
function skin_report() {
    global $smt;
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
    . '<gallery caption="Skin Percentage Report - Top ' . sizeof($medias) . ' Files" widths="100px" heights="100px" perrow="6">' . $cr;

    foreach( $medias as $media ) {
        print $media['title'] . '|' . $media['skin'] . ' %' . $cr;
    }
    print '</gallery></textarea>';
}
