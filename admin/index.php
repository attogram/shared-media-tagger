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
print '<div class="box white">';

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

print '<p>Site:
<ul>
<li><b>' . $msg_count . '</b> <a target="sqlite" href="sqladmin.php?table=contact&action=row_view">Messages</a></li>
<li><b>' . $smt->get_categories_count() . '</b> Categories</li>
<li><b>' . sizeof($smt->get_tags()) . '</b> Tags</li>
<li><b>' . $smt->get_image_count() . '</b> Files</li>
<li><b>' . $smt->get_total_files_reviewed_count() . '</b> Files reviewed</li>
<li><b>' . $smt->get_block_count() . '</b> Blocked Files</li>
<li><b>' . $smt->get_tagging_count() . '</b> Tagging Count</li>
<li><b>' . $smt->get_total_review_count() . '</b> Total Review Count</li>
<li><b>' . $smt->get_user_tag_count() . '</b> User Tag Count</li>
<li><b>' . $smt->get_user_count() . '</b> Users</li>
</ul>
</p>';

print '<p>Installation:
<ul>
<li>Server: ' . $smt->server . '</li>
<li>URL: <a href="' . $smt->url('home') . '">' . $smt->url('home') . '</a></li>
<li>Directory: ' . $smt->install_directory . '</li>
<li><a href="' . $smt->url('home') . 'robots.txt">robots.txt</a>: 
<span style="font-family:monospace;">'
	. $smt->check_robotstxt() 
. '</span></li>
</ul>
</p>';

print '<p>Database:
<ul>
<li>Size: '
. (file_exists($smt->database_name) ? number_format(filesize($smt->database_name)) : 'NULL')
. ' bytes</li>
<li>File: ' . $smt->database_name . '</li>
<li>URL: <a href="' . $smt->url('admin')  . 'db/media.sqlite">' . $smt->url('admin')  . 'db/media.sqlite</a></li>
</ul>
</p>';

print '<p>About Shared Media Tagger:
<ul>
<li> Github: <a target="commons" href="https://github.com/attogram/shared-media-tagger">attogram/shared-media-tagger</a></li>
<li><a target="commons" href="https://github.com/attogram/shared-media-tagger/blob/master/README.md">README</a></li>
<li><a target="commons" href="https://github.com/attogram/shared-media-tagger/blob/master/LICENSE.md">LICENSE</a></li>
</ul>
</p>';

print '</div>';

$smt->include_footer();
