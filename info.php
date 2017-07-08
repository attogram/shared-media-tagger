<?php
// Shared Media Tagger 
// Media info

$f = __DIR__.'/smt.php'; 
if( !file_exists($f) || !is_readable($f) ) { print 'Site down for maintenance'; exit; } require_once($f);

$smt = new smt('Media Info');

if( !isset($_GET['i']) || !$_GET['i'] || !$smt->is_positive_number($_GET['i']) ) { 
	$smt->fail404('404 Media Not Found');
}

$pageid = (int)$_GET['i'];

$media = $smt->get_image_from_db($pageid);
if( !$media || !isset($media[0]) || !is_array($media[0]) ) {
	$smt->fail404('404 Media Not Found');
}
$media = @$media[0];


/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$smt->title = 'Info: ' . $smt->strip_prefix($media['title']);
$smt->include_header();
$smt->include_menu();

?>
<table style="border:0px !important;">
 <tr>
  <td class="box grey center"><?php 

	print $smt->display_image($media)
	. $smt->display_categories( $pageid )
	. '<br />'
	. '<a href="./?i=' . $pageid . '">Review this file:</a>' 
	. $smt->display_tags( $pageid )
	;

?>
  </td>
  <td class="box white">
<h1 style="padding-top:5px;"><a target="commons" href="<?php 
	print $media['url']; ?>"><?php 
	print $smt->strip_prefix($media['title']); ?></a></h1>

<p><?php print $smt->get_reviews($pageid);?></p>
	
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

<p><a target="commons" href="<?php print $media['descriptionshorturl']; ?>">View on commons.wikimedia.org</a>
<br /><a target="commons" href="//wikidata.org/wiki/<?php 
	print $smt->category_urlencode($media['title']); ?>">View on wikidata.org</a>
<br /><a target="commons" href="//en.wikipedia.org/wiki/<?php 
	print $smt->category_urlencode($media['title']); ?>">View on en.wikipedia.org</a>
</p>

<small>
<em>Media info:</em>
<br />
 width: <b><?php print $media['width']; ?></b>,
 height: <b><?php print $media['height']; ?></b>
<br />
 mime: <b><?php print $media['mime']; ?></b>,
 size: <b><?php print $media['size']; ?></b>, 
 duration: <b><?php print ( $media['duration'] ? $media['duration'] : '0'); ?></b>
 <br />

timestamp: <b><?php print $media['timestamp']; ?></b>
<br />
uploaded by: <b><a target="commons" href="https://commons.wikimedia.org/wiki/User:<?php 
	print $media['user']; ?>">User:<?php 
	print $media['user']; ?></a></b> 

<br />
<br />

<a href="<?php 
print $smt->url('contact') . '?r=' . $media['pageid'] ?>" style="color:darkred; font-weight:bold;">REPORT this file</a>

  </td>
 </tr>
</table>
<?php


$smt->include_footer();
exit;

