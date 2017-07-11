<?php
// Shared Media Tagger
// User

$init = __DIR__.'/smt.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt();

$smt->include_header();
$smt->include_menu();

print '<div class="box white"><p>User:</p>';

print '<p>ID: ' . $smt->user_id . '</p>';

print '<p>Tag Count: ' . $smt->get_user_tag_count( $smt->user_id ) . '</p>';

print '</div>';
$smt->include_footer();
