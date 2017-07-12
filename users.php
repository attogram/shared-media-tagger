<?php
// Shared Media Tagger
// Users

$init = __DIR__.'/smt.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt();

$smt->include_header();
$smt->include_menu();
print '<div class="box white">';

$users = $smt->get_users();
if( !$users ) {
	print '<p>No users have reviewed files yet.</p>';
}
foreach( $users as $user ) {
	$tag_count = $smt->get_user_tag_count( $user['id'] );
	if( !$tag_count ) {
		continue;
	}
	print '<div style="display:inline-block; border:1px solid grey; padding:4px; margin:4px; ">'
	. '<a href="' . $smt->url('users') . '?i=' . $user['id'] . '">'
	. '+' . $tag_count . ' reviews</a> '
	. '<small>by User:' . $user['id'] . '</small>'
	. '</div>';
}


$user_id = ( isset($_GET['i']) && $smt->is_positive_number($_GET['i']) ) ? $_GET['i'] : 0;

if( !$user_id ) {
	goto footer;
}


print '<p>User ID: ' . $user_id . '</p>';
print '<p>' . $smt->get_user_tag_count( $user_id ) . ' reviews</p>';
foreach( $smt->get_user_tagging($user_id) as $media ) {
	print '<div style="display:inline-block;">'
	. '+' . $media['count'] . ' ' . $smt->get_tag_name_by_id($media['tag_id'])
	. '<br />'
	. $smt->display_thumbnail_box($media)
	. '</div>';
}


footer:
print '</div>';
$smt->include_footer();
