<?php
// Shared Media Tagger
// HOME PAGE

$f = __DIR__.'/smt.php'; 
if(!file_exists($f)||!is_readable($f)){print 'Site down for maintenance';exit;} require_once($f);
$smt = new smt('Home');
$smt->title = $smt->site_name;

if( isset($_GET['i']) && $smt->is_positive_number($_GET['i']) ) {
	$image = $smt->get_image_from_db($_GET['i']);
} else {
	$image =  $smt->get_random_media(1);
}
if( !$image || !isset($image[0]) ) { 
	$smt->fail404('404 Media Not Found');
} 
$image = $image[0]; 

$smt->include_header();
$smt->include_small_menu();

print '<div class="box grey center">'
. $smt->display_tags( $image['pageid']) 
. $smt->display_image($image)
. '<br />'
. $smt->display_categories( $image['pageid'] )
. '<br />'
. '<div class="left" style="margin:auto; width:' . $smt->size_medium . 'px;">' 
. $smt->get_reviews( $image['pageid'] ) 
. '<br />'
. '<br />'
. '<a href="' . $smt->url('contact') . '?r=' . $image['pageid'] . '" style="color:#666; font-size:85%;">'
. 'REPORT this file</a>'
. '</div>'
;

if( isset($_COOKIE['admin']) && $_COOKIE['admin'] == 1 ) {
	print '<br /><br /><div style="text-align:left; display:inline-block; background-color:black;color:white; font-size:80%; border:1px solid red;">Admin:<br />'
	. '<a href="' . $smt->url('admin') . '/media.php?dm=' . $image['pageid'] 
	. '" onclick="return confirm(\'Confirm: Delete Media #' . $image['pageid'] . ' ?\');"'
	. '>Delete Media #' . $image['pageid'] . '</a>'
	. '</div>';
}

print '</div>';
$smt->include_footer();
