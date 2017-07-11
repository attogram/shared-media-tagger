<?php
// Shared Media Tagger
// User

$init = __DIR__.'/smt.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt();

$smt->include_header();
$smt->include_menu();

print '<div class="box white">';

print '<p>User ID: ' . $smt->user_id . '</p>';

print '<p>' . $smt->get_user_tag_count( $smt->user_id ) . ' reviews</p>';

foreach( $smt->get_user_tagging($smt->user_id) as $media ) {
	
	print '<div style="display:inline-block;">'
	. '+' . $media['count'] . ' ' . $smt->get_tag_name_by_id($media['tag_id'])
	. '<br />'
	. $smt->display_thumbnail_box($media)
	. '</div>'
	;
}

print '</div>';
$smt->include_footer();
