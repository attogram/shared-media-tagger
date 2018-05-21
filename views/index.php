<?php
/**
 * Shared Media Tagger
 * HOME PAGE
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTagger $smt
*/

if (isset($_GET['i']) && $smt->isPositiveNumber($_GET['i'])) {
    $media = $smt->getMedia($_GET['i']);
} else {
    $media = $smt->getRandomMedia(1);
}
if (!$media || !isset($media[0])) {
    $smt->fail404('404 Media Not Found');
}

$media = $media[0];

$smt->title = $smt->siteName;
$smt->includeHeader(/*show_site_header*/false);
$smt->includeSmallMenu();

print '<div class="box grey center">'
. $smt->displayTags($media['pageid'])
. $smt->displayImage($media)
. '<div class="left" style="margin:auto; width:' . $smt->sizeMedium . 'px;">'
. '<br />'
. $smt->getReviews($media['pageid'])
. $smt->displayCategories($media['pageid'])
. '<br />'
. '<a href="' . $smt->url('contact') . '?r='
. $media['pageid'] . '" style="color:#666; font-size:85%;">REPORT this file</a>'
. '</div></div>';

$smt->includeFooter();
