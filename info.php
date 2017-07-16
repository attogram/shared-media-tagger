<?php
// Shared Media Tagger 
// Media info

$f = __DIR__.'/smt.php'; 
if( !file_exists($f) || !is_readable($f) ) { print 'Site down for maintenance'; exit; } require_once($f);

$smt = new smt();

if( !isset($_GET['i']) || !$_GET['i'] || !$smt->is_positive_number($_GET['i']) ) { 
    $smt->fail404('404 Media Not Found');
}

$pageid = (int)$_GET['i'];

$media = $smt->get_media($pageid);
if( !$media || !isset($media[0]) || !is_array($media[0]) ) {
    $smt->fail404('404 Media Not Found');
}
$media = @$media[0];


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$smt->title = 'Info: ' . $smt->strip_prefix($media['title']);
$smt->use_bootstrap = TRUE;
$smt->use_jquery = TRUE;
$smt->include_header();
$smt->include_menu();

?>
<div class="container">
<div class="row">

<div class="col-sm-6 box grey center"><?php /* start left */ ?>
<?php 
print ''
. $smt->display_tags( $pageid )
. $smt->display_image($media)
. $smt->display_categories( $pageid )
. $smt->get_reviews($pageid); ?>
</div><?php /* end left */ ?>

<div class="col-sm-6 box white"><?php /* start right */ ?>

<br />
<h1><a target="commons" href="<?php 
    print $media['url']; ?>"><?php 
    print $smt->strip_prefix($media['title']); ?></a></h1>
<br />
<br />
    
<p><?php print ($media['imagedescription']); ?></p>

<p><em>by:</em> <b><?php print ($media['artist'] ? $media['artist'] : 'unknown'); ?></b>
<?php if( $media['datetimeoriginal']) {
    print ' / ' . $media['datetimeoriginal'];
} ?></p>

<div style="border:1px solid #ccc; display:inline-block; padding:10px; background-color:#eee;">
<em>License:</em>
<?php

$fix = array(
    'Public domain'=>'Public Domain',
    'CC-BY-SA-3.0'=>'CC BY-SA 3.0'
);
while( list($bad,$good) = each($fix) ) {
    if( $media['usageterms'] == $bad ) { $media['usageterms'] = $good; }
    if( $media['licensename'] == $bad ) { $media['licensename'] = $good; }
    if( $media['licenseshortname'] == $bad ) { $media['licenseshortname'] = $good; }
}
$lics = array();
$lics[] = $media['licensename'];
$lics[] = $media['licenseshortname'];
$lics[] = $media['usageterms'];
$lics = array_unique($lics);

if( $media['licenseuri'] && $media['licenseuri'] != 'false' ) {
    print '<br /><b><a target="license" href="' 
    . $media['licenseuri'] . '">' . implode('<br />', $lics)  . '</a></b>';
} else {
    print '<b>' . implode('<br />', $lics) . '</b>';
}
if( $media['attributionrequired'] && $media['attributionrequired'] != 'false' ) {
    print '<br />Attribution Required: <b>' . $media['attributionrequired'] .'</b>';
}
if( $media['restrictions'] && $media['restrictions'] != 'false' ) {
    print '<br />Restrictions: <b>' . $media['restrictions'] .'</b>';
}
?>
</div>
<br />
<br />

<p><em>View this file on:</em>
<ul>
<li><a target="commons" href="<?php print $media['descriptionshorturl']; ?>">commons.wikimedia.org</a></li>
<li><a target="commons" href="//en.wikipedia.org/wiki/<?php 
    print $smt->category_urlencode($media['title']); ?>">en.wikipedia.org</a></li>
<li><a target="commons" href="//wikidata.org/wiki/<?php 
	    print $smt->category_urlencode($media['title']); ?>">wikidata.org</a></li>
</ul>
</p>

<p><em>Media info:</em>
<ul>
<li>width x height: <b><?php print $media['width']; ?> x <?php print $media['height']; ?></b> pixels</li>
<li>mime: <b><?php print $media['mime']; ?></b></li>
<li>size: <b><?php print $media['size']; ?></b> bytes</li>
<?php 
	 $media['duration'] 
		? print '<li>duration: <b>' . $media['duration'] . '</b> seconds</li>'
		: print '';
?>
<li>timestamp: <b><?php print $media['timestamp']; ?></b></li>
<li>uploader: <b><a target="commons" href="https://commons.wikimedia.org/wiki/User:<?php 
    print urlencode($media['user']); ?>">User:<?php print $media['user']; ?></a></b></li>
</ul>
</p>

<p><a href="<?php 
print $smt->url('contact') . '?r=' . $media['pageid'] ?>" style="color:darkred; font-weight:bold;">REPORT this file</a></p>

</div><?php /* end right */ ?>
</div><?php /* end row */ ?>
</div><?php /* end container */ ?>
<br />
<?php
$smt->include_footer();
