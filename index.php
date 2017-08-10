<?php
// Shared Media Tagger
// HOME PAGE

//////////////////////////////////////////////////////////////////
$init = __DIR__.'/admin/src/smt.php'; // Shared Media Tagger Class
if( !is_readable($init) ) { exit('Site down for maintenance'); }
require_once($init);
$smt = new smt(); // Shared Media Tagger Object
//////////////////////////////////////////////////////////////////

if( isset($_GET['i']) && $smt->is_positive_number($_GET['i']) ) {
    $media = $smt->get_media($_GET['i']);
} else {
    $media =  $smt->get_random_media(1);
}
if( !$media || !isset($media[0]) ) {
    $smt->fail404('404 Media Not Found');
}

$media = $media[0];

$smt->title = $smt->site_name;
$smt->include_header( /*show_site_header*/FALSE );
$smt->include_small_menu();

print '<div class="box grey center">'
. $smt->display_tags($media['pageid'])
. $smt->display_image($media)
. '<div class="left" style="margin:auto; width:' . $smt->size_medium . 'px;">'
. '<br />'
. $smt->get_reviews( $media['pageid'] )
. $smt->display_categories( $media['pageid'] )
. '<br />'
. '<a href="' . $smt->url('contact') . '?r='
. $media['pageid'] . '" style="color:#666; font-size:85%;">REPORT this file</a>'
. '</div></div>';

$smt->include_footer();
