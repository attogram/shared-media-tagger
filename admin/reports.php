<?php
// Shared Media Tagger
// Admin Reports

$init = __DIR__.'/../smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$init = __DIR__.'/smt-admin.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt_admin();

$smt->title = 'Admin Reports';
$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white"><p><a href="' . $smt->url('admin') .'reports.php">' . $smt->title . '</a></p>
<ul>
<li><a href="' . $smt->url('admin') .'reports.php?r=cat0local">Categories with 0 Local files</a></li>
<li><a href="' . $smt->url('admin') .'reports.php?r=cat0commons">Categories with 0 Commons files</a></li>
</ul>
<hr />';

if( !isset($_GET['r']) || !$_GET['r'] ) {
	print '</div>';
	$smt->include_footer();
	return;
}

switch( $_GET['r'] ) { 
	default: print '<p>Unknown Report</p>'; break;
	case 'cat0local': cat0local(); break;
	case 'cat0commons': cat0commons(); break;
} // end switch

print '</div>';
$smt->include_footer();


////////////////////////////////
function cat0local() {
	global $smt;
	$sql = '
		SELECT count(c2m.media_pageid) AS local_count,  c.id, c.name
		FROM category AS c
		LEFT OUTER JOIN category2media AS c2m ON c2m.category_id = c.id
		GROUP BY c.id';
	$cats = $smt->query_as_array($sql);
	if( !$cats ) {
		print '<p>FAILED to get category list</p>';
		return;
	}
	
	$zcats = array();
	foreach( $cats as $cat ) {
		if( $cat['local_count'] == 0 ) {
			$zcats[] = $cat['id'];
			print '<a href="' . $smt->url('category') . '?c=' 
			. $smt->category_urlencode($smt->strip_prefix($cat['name'])) . '">' 
			. $cat['name'] . '</a><br />';		
		}
	}
	if( !$zcats ) {
		print '<p>No results found.</p>';
		return;
	}
	
	print '<p>Delete SQL:<br /><br />'
	. 'DELETE FROM category WHERE id IN ( ' . implode($zcats, ', ') . ' );'
	. '</p>';


} // end function cat0local()

////////////////////////////////
function cat0commons() {
	print '<p>cat0commons</p>';
}