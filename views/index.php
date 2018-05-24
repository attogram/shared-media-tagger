<?php
/**
 * Shared Media Tagger
 * HOME PAGE
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
*/

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

if (isset($_GET['i']) && Tools::isPositiveNumber($_GET['i'])) {
    $media = $smt->database->getMedia($_GET['i']);
} else {
    $media = $smt->database->getRandomMedia(1);
}
if (!$media || !isset($media[0])) {
    $smt->fail404('404 Media Not Found');
}

$media = $media[0];

$smt->title = Config::$siteName;
$smt->includeHeader(false);
$smt->includeSmallMenu();

print '<div class="box grey center">'
. $smt->displayTags($media['pageid'])
. $smt->displayImage($media)
. '<div class="left" style="margin:auto; width:' . Config::$sizeMedium . 'px;">'
. '<br />'
. $smt->getReviews($media['pageid'])
. $smt->displayCategories($media['pageid'])
. '<br />'
. '<a href="' . Tools::url('contact') . '?r='
. $media['pageid'] . '" style="color:#666; font-size:85%;">REPORT this file</a>'
. '</div></div>';

$smt->includeFooter();
