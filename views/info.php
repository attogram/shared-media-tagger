<?php
/**
 * Shared Media Tagger
 * Media Info
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

if (!isset($_GET['i']) || !$_GET['i'] || !Tools::isPositiveNumber($_GET['i'])) {
    $smt->fail404('404 Media Not Found');
}

$pageid = (int) $_GET['i'];

$media = $smt->database->getMedia($pageid);
if (!$media || !isset($media[0]) || !is_array($media[0])) {
    $smt->fail404('404 Media Not Found');
}
$media = $media[0];

$smt->useBootstrap = true;

/////////////////////////////////////////////////////////////////////
$smt->title = 'Info: ' . Tools::stripPrefix($media['title']);
$smt->use_bootstrap = true;
$smt->use_jquery = true;
$smt->includeHeader();
$smt->includeMediumMenu();

?>
<div class="container">
<div class="row">
<div class="col-sm-6 box grey center">
<?php
print ''
. $smt->displayTags($pageid)
. $smt->displayMedia($media)
. '<div class="left" style="margin:auto; width:' . Config::$sizeMedium . 'px;">'
. '<br />'
. $smt->displayReviews($smt->database->getReviews($pageid))
. $smt->displayCategories($pageid)
. '</div>';
?>
</div>
<div class="col-sm-6 box white">
<br />
<h1><a target="commons" href="<?php
    print $media['url']; ?>"><?php
    print Tools::stripPrefix($media['title']); ?></a></h1>
<br />
<br />
<p><?php print($media['imagedescription']); ?></p>
<p><em>by:</em> <b><?php print($media['artist'] ? $media['artist'] : 'unknown'); ?></b>
<?php
if ($media['datetimeoriginal']) {
        print ' / ' . $media['datetimeoriginal'];
    }
    ?></p>
<div style="border:1px solid #ccc; display:inline-block; padding:10px; background-color:#eee;">
<em>License:</em>
<?php
$fix = [
    'Public domain'=>'Public Domain',
    'CC-BY-SA-3.0'=>'CC BY-SA 3.0'
];

foreach ($fix as $bad => $good) {
    if ($media['usageterms'] == $bad) {
        $media['usageterms'] = $good;
    }
    if ($media['licensename'] == $bad) {
        $media['licensename'] = $good;
    }
    if ($media['licenseshortname'] == $bad) {
        $media['licenseshortname'] = $good;
    }
}
$lics = [];
$lics[] = $media['licensename'];
$lics[] = $media['licenseshortname'];
$lics[] = $media['usageterms'];
$lics = array_unique($lics);

if ($media['licenseuri'] && $media['licenseuri'] != 'false') {
    print '<br /><b><a target="license" href="'
    . $media['licenseuri'] . '">' . implode('<br />', $lics)  . '</a></b>';
} else {
    print '<b>' . implode('<br />', $lics) . '</b>';
}
if ($media['attributionrequired'] && $media['attributionrequired'] != 'false') {
    print '<br />Attribution Required: <b>' . $media['attributionrequired'] .'</b>';
}
if ($media['restrictions'] && $media['restrictions'] != 'false') {
    print '<br />Restrictions: <b>' . $media['restrictions'] .'</b>';
}
?>
</div>
<br /><br />
<style>
li { margin-bottom:6px; }
</style>
<p><em>View this file on:</em>
<ul>
<li><a target="commons" href="<?php print $media['descriptionshorturl']; ?>">commons.wikimedia.org</a></li>
<li><a target="commons" href="//en.wikipedia.org/wiki/<?php
    print Tools::categoryUrlencode($media['title']); ?>">en.wikipedia.org</a></li>
<li><a target="commons" href="//wikidata.org/wiki/<?php
        print Tools::categoryUrlencode($media['title']); ?>">wikidata.org</a></li>
</ul>
</p>

<p><em>Media info:</em>
<ul>
<li>width x height: <b><?php print number_format((float) $media['width']);
?> x <?php print number_format((float) $media['height']); ?></b> pixels</li>
<li>mime: <b><?php print $media['mime']; ?></b></li>
<li>size: <b><?php print number_format((float) $media['size']); ?></b> bytes</li>
<?php
if ($media['duration'] > 0) {
    //print '<li>duration: <b>' . $media['duration'] . '</b> seconds</li>';
    print '<li>duration: <b>' . Tools::secondsToTime($media['duration']) . '</b></li>';
}
?>
<li>timestamp: <b><?php print $media['timestamp']; ?></b></li>
<li>uploader: <b><a target="commons" href="https://commons.wikimedia.org/wiki/User:<?php
    print urlencode($media['user']); ?>">User:<?php print $media['user']; ?></a></b></li>
</ul>
</p>
<p><em>Media analysis:</em>
<ul>
<?php
if (isset($media['sha1']) && $media['sha1'] != null) {
        print '<li>SHA1 Hash: <small><b>' . $media['sha1'] . ' </b></small></li>';
    }
?>
</ul>
</p>

<br />
<p><a href="<?php
print Tools::url('contact') . '?r=' . $media['pageid'] ?>" style="color:#ff9999;">REPORT this file</a></p>
<?php

if (Tools::isAdmin()) {
    print '<pre>ADMIN: media: ' . print_r($media, true) . '</pre>';
}

?>
</div>
</div>
</div>
<br />
<?php

$smt->includeFooter();
