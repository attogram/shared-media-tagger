<?php
// Shared Media Tagger
// Export Admin

$init = __DIR__.'/../smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$init = __DIR__.'/smt-admin.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$smt = new smt_admin();

$smt->title = 'Export Admin';
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
$smt->include_admin_menu();
print '<div class="box white"><p>' . $smt->title . '</p>';
/////////////////////////////////////////////////////////

print '<p>Exports - MediaWiki Format</p>';
print '<ul>';
foreach( $smt->get_tags() as $tag ) {
	print '<li>Tag Report: <a href="?r=tag&amp;i=' . $tag['id'] . '">' . $tag['name'] . '</a></li>';
}
print '<li><a href="?r=skin">Skin Percentage Report</li>';
print '</ul><hr />';

switch( @$_GET['r'] ) {
	
	default: break;
	case 'skin': skin_report(); break;
	case 'tag': tag_report(@$_GET['i']); break;
}



print '</div>';
$smt->include_footer();


//////////////////////////////////////////////
function tag_report( $tag_id='' ) {
	global $smt;
	if( !$tag_id || !$smt->is_positive_number($tag_id) ) {
		$smt->error('Tag Report: Tag ID NOT FOUND');
		return FALSE;
	}

	$tags = $smt->get_tags();
	
	$sql = '
	SELECT m.title, t.count
	FROM media AS m, tagging AS t
	WHERE m.pageid = t.media_pageid
	AND t.tag_id = :tag_id
	LIMIT 200';
	$medias = $smt->query_as_array($sql, array(':tag_id'=>$tag_id));
	$cr = "\n";
	$report_name = 'Tag Report: ' . $tags[$tag_id]['name'] . ' - Top ' . sizeof($medias) . ' Files';
	
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
