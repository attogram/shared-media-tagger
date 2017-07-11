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

$site_count = $smt->query_as_array('SELECT count(id) AS count FROM site');
if( !$site_count ) {
    print '<p>Welome!  Creating new Shared Media Tagger Database:</p>'
    . $smt->create_tables();
}

$msg_count = 0;
$r = $smt->query_as_array('SELECT count(id) AS count FROM contact');
if( isset($r[0]['count']) ) {
    $msg_count = $r[0]['count'];
}
print '<p><b>' . $msg_count . '</b> '
    . '<a target="sqlite" href="sqladmin.php?table=contact&action=row_view">'
    . 'Messages</a></p>';

if( !$smt->get_categories_count() ) {
    print '<div class="error" style="display:inline-block; font-size:110%;">';
}
print '<p><b>' . $smt->get_categories_count() . '</b> '
    . '<a href="' . $smt->url('admin') . 'category.php">Categories</a></p>';
if( !$smt->get_categories_count() ) {
    print '</div>';
}

print '<p><b>' . $smt->get_image_count() . '</b> Media files</p>';
print '<p><b>' . $smt->get_block_count() . '</b> Blocked files</p>';

print '<p><a target="commons" href="https://github.com/attogram/shared-media-tagger/blob/master/README.md">README</a></p>';
print '<p><a target="commons" href="https://github.com/attogram/shared-media-tagger/blob/master/LICENSE.md">LICENSE</a></p>';

print '</div>';

$smt->include_footer();
