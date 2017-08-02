<?php
// Shared Media Tagger
// HOME PAGE

$f = __DIR__.'/smt.php';
if(!file_exists($f)||!is_readable($f)){print 'Site down for maintenance';exit;} require_once($f);
$smt = new smt();

if( isset($_GET['i']) && $smt->is_positive_number($_GET['i']) ) {
    $image = $smt->get_media($_GET['i']);
} else {
    $image =  $smt->get_random_media(1);
}
if( !$image || !isset($image[0]) ) {
    $smt->fail404('404 Media Not Found');
}
$image = $image[0];

$smt->title = $smt->site_name;
$smt->include_header( /*show_site_header*/FALSE );
$smt->include_small_menu();

print ''
. '<div class="box grey center">'
. $smt->display_tags($image['pageid'])
. $smt->display_image($image)
. '<div class="left" style="margin:auto; width:' . $smt->size_medium . 'px;">'
. '<br />'
. $smt->get_reviews( $image['pageid'] )
. $smt->display_categories( $image['pageid'] )
. '<br />'
. '<a href="' . $smt->url('contact') . '?r='
. $image['pageid'] . '" style="color:#666; font-size:85%;">REPORT this file</a>'
. '</div>'
. '</div>'
;

$smt->include_footer();
