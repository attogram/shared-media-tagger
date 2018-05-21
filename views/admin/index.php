<?php
/**
 * Shared Media Tagger
 * Admin Home
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin $smt
 */

$smt->title = 'Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white">';

$site_count = $smt->queryAsArray('SELECT count(id) AS count FROM site');
if (!$site_count) {
    print '<p>Welome!  Creating new Shared Media Tagger Database:</p><pre>'
    . $smt->createTables() . '</pre>';
}

$msg_count = 0;
$r = $smt->queryAsArray('SELECT count(id) AS count FROM contact');
if (isset($r[0]['count'])) {
    $msg_count = $r[0]['count'];
}

print '<p>Site: <b><a href="./site.php">' . $smt->siteName . '</a></b>
<ul>
<li><b>' . $msg_count . '</b> <a target="sqlite" href="sqladmin.php?table=contact&action=row_view">Messages</a></li>
<li><b>' . sizeof($smt->getTags()) . '</b> <a href="./site.php">Tags</a></li>
<li><b>' . number_format($smt->getImageCount()) . '</b> Files</li>
<li><b>' . number_format($smt->getBlockCount()) . '</b> Blocked Files</li>
<li><b>' . number_format($smt->getTotalFilesReviewedCount()) . '</b> Files reviewed</li>
<li><b>' . number_format($smt->getTaggingCount()) . '</b> Tagging Count</li>
<li><b>' . number_format($smt->getTotalReviewCount()) . '</b> Total Review Count</li>
<li><b>' . number_format($smt->getUserTagCount()) . '</b> User Tag Count</li>
<li><b>' . number_format($smt->getUserCount()) . '</b> Users</li>
</ul>
</p>';

print '<p>Installation:
<ul>
<li>Server: ' . $smt->server . '</li>
<li>URL: <a href="' . $smt->url('home') . '">' . $smt->url('home') . '</a></li>
<li>Protocol: ' . $smt->getProtocol() . '</li>
<li>Directory: ' . $smt->installDirectory . '</li>
<li>Setup: ' . ($smt->setup ? print_r($smt->setup, true) : 'none') . '</li>
</ul>
</p>';


print '<p>Discovery / Restrictions:
<ul>
<li>./admin/.htaccess: '
. (is_readable($smt->installDirectory.'/admin/.htaccess') ? '✔ACTIVE: ' : '❌MISSING')
. '</li>
<li>./admin/.htpasswd: '
. (is_readable($smt->installDirectory.'/admin/.htpasswd') ? '✔ACTIVE: ' : '❌MISSING')
. '</li>
<li><a href="' . $smt->url('home') . 'sitemap.php">sitemap.php</a></li>
<li><a href="' . $smt->url('home') . 'robots.txt">robots.txt</a>:
<span style="font-family:monospace;">'
    . $smt->checkRobotstxt()
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

$smt->includeFooter();
