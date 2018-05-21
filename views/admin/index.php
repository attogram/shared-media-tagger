<?php
// Shared Media Tagger
// Admin Home

$smt->title = 'Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white">';

$site_count = $smt->query_as_array('SELECT count(id) AS count FROM site');
if( !$site_count ) {
    print '<p>Welome!  Creating new Shared Media Tagger Database:</p><pre>'
    . $smt->create_tables() . '</pre>';
}

$msg_count = 0;
$r = $smt->query_as_array('SELECT count(id) AS count FROM contact');
if( isset($r[0]['count']) ) {
    $msg_count = $r[0]['count'];
}

print '<p>Site: <b><a href="./site.php">' . $smt->site_name . '</a></b>
<ul>
<li><b>' . $msg_count . '</b> <a target="sqlite" href="sqladmin.php?table=contact&action=row_view">Messages</a></li>
<li><b>' . sizeof($smt->get_tags()) . '</b> <a href="./site.php">Tags</a></li>
<li><b>' . number_format($smt->get_image_count()) . '</b> Files</li>
<li><b>' . number_format($smt->get_block_count()) . '</b> Blocked Files</li>
<li><b>' . number_format($smt->get_total_files_reviewed_count()) . '</b> Files reviewed</li>
<li><b>' . number_format($smt->get_tagging_count()) . '</b> Tagging Count</li>
<li><b>' . number_format($smt->get_total_review_count()) . '</b> Total Review Count</li>
<li><b>' . number_format($smt->get_user_tag_count()) . '</b> User Tag Count</li>
<li><b>' . number_format($smt->get_user_count()) . '</b> Users</li>
</ul>
</p>';

print '<p>Installation:
<ul>
<li>Server: ' . $smt->server . '</li>
<li>URL: <a href="' . $smt->url('home') . '">' . $smt->url('home') . '</a></li>
<li>Protocol: ' . $smt->get_protocol() . '</li>
<li>Directory: ' . $smt->install_directory . '</li>
<li>Setup: ' . ($smt->setup ? print_r($smt->setup,1) : 'none') . '</li>
</ul>
</p>';


print '<p>Discovery / Restrictions:
<ul>
<li>./admin/.htaccess: '
. ( is_readable($smt->install_directory.'/admin/.htaccess') ? '✔ACTIVE: ' : '❌MISSING' )
. '</li>
<li>./admin/.htpasswd: '
. ( is_readable($smt->install_directory.'/admin/.htpasswd') ? '✔ACTIVE: ' : '❌MISSING' )
. '</li>
<li><a href="' . $smt->url('home') . 'sitemap.php">sitemap.php</a></li>
<li><a href="' . $smt->url('home') . 'robots.txt">robots.txt</a>:
<span style="font-family:monospace;">'
    . $smt->check_robotstxt()
. '</span></li>

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
