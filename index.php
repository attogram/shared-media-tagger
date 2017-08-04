<?php
// Shared Media Tagger
// HOME PAGE

$init = __DIR__.'/smt.php'; // Shared Media Tagger Main Class
if( !is_readable($init) ) {
    print 'Site down for maintenance';
    return;
}
require_once($init);
$smt = new smt(); // The Shared Media Tagger Object
/////////////////////////////////////////////////////////////

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

print '<div class="box grey center">'
. $smt->display_tags($image['pageid'])
. $smt->display_image($image)
. '<div class="left" style="margin:auto; width:' . $smt->size_medium . 'px;">'
. '<br />'
. $smt->get_reviews( $image['pageid'] )
. $smt->display_categories( $image['pageid'] )
. '<br />'
. '<a href="' . $smt->url('contact') . '?r='
. $image['pageid'] . '" style="color:#666; font-size:85%;">REPORT this file</a>'
. '</div></div>';

$smt->include_footer();
