<?php
// Shared Media Tagger
// Admin Home

$f = __DIR__.'/../smt.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$f = __DIR__.'/smt-admin.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$smt = new smt_admin('Admin');
$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>Admin</p>';

$r = $smt->query_as_array('SELECT count(id) AS count FROM site');
if( !$r ) {
	print '<p>Welome!  Creating new Shared Media Tagger Database:</p>'
	. $smt->create_tables();
	
}

$r = $smt->query_as_array('SELECT count(id) AS count FROM contact');
if( !$r || !isset($r[0]['count']) ) {
	$msg_count = 0;
	
} else {
	$msg_count = $r[0]['count'];
}
print '<p><b>' . $msg_count . '</b> <a target="sqlite" href="sqladmin.php?table=contact&action=row_view">messages</a></p>';
print '</div>';

$smt->include_footer();
exit;
