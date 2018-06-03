<?php
/**
 * Shared Media Tagger
 * Create Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$numberOfImages = 4;
$thumbWidth = 50;
$montageWidth = 100;
$montageHeight = 100;
$montageImagesPerRow = 2;
$montageIndexStep = $thumbWidth;

$showFooter = false;
$footerHeight = 0;

$mimetypes[] = 'image/jpeg';
$mimetypes[] = 'image/gif';
$mimetypes[] = 'image/png';

$tagId = (!empty($_GET['t']) && Tools::isPositiveNumber($_GET['t']))
    ? (int)$_GET['t']
    : 'R';

$smt->title = 'Create';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white"><p><a href="create.php">Create</a></p>';
print '<ul>'
. '<li>Montage 100x100, 2x2: <a href="create.php?montage=1&amp;t=R">Random Images</a></li>';

foreach ($smt->database->getTags() as $tag) {
    print '<li>Montage 100x100, 2x2: <a href="create.php?montage=1&amp;t='
    . $tag['id'] . '">Images tagged: ' . $tag['name'] . '</a></li>';
}
print '</ul>';

if (empty($_GET['montage'])) {
    print '</div>';
    $smt->includeFooter();

    return;
}

if (!function_exists('imagecreatetruecolor')) {
    Tools::error('PHP GD Library NOT FOUND');
    print '</div>';
    $smt->includeFooter();

    return;
}

//if (!class_exists("Imagick")) {
//    Tools::error('Imagick NOT FOUND');
//} else {
//    Tools::notice('Imagick FOUND OK');
//}

switch ($tagId) {
    default:
        $sql = '
        SELECT m.*, t.count
        FROM  media AS m, tagging AS t, tag as tg
        WHERE t.media_pageid = m.pageid
        AND   t.tag_id = :tag_id
        AND   tg.id = t.tag_id
        AND   m.thumbmime IN ("' . implode($mimetypes, '", "') . '")
        AND   m.thumburl LIKE "%325px%"
        ORDER BY RANDOM()
        LIMIT ' . $numberOfImages;
        $bind = [':tag_id' => $tagId];
        break;
    case 'R':
        $sql = '
        SELECT m.*
        FROM media AS m
        ORDER BY RANDOM()
        LIMIT ' . $numberOfImages;
        $bind = [];
        break;
}

$images = $smt->database->queryAsArray($sql, $bind);
if (!$images) {
    Tools::error('No images found in criteria');
    print '</div>';
    $smt->includeFooter();
    return;
}

$montage = imagecreatetruecolor($montageWidth, $montageHeight + $footerHeight);
$xIndex = $yIndex = 0;
foreach ($images as $image) {
    $url = str_replace('325px', $thumbWidth.'px', $image['thumburl']);

    switch ($image['thumbmime']) {
        case 'image/gif':
            $currentImage = @imagecreatefromgif($url);
            break;
        case 'image/jpeg':
            $currentImage = @imagecreatefromjpeg($url);
            break;
        case 'image/png':
            $currentImage = @imagecreatefrompng($url);
            // no break
        default:
            //print '<P>ERROR: unknown mime type</P>';
            continue;
    }
    if (!$currentImage) {
        print '<p>ERROR: cannot get image: ' . $url . '</p>';
        continue;
    }
    if (imagesx($currentImage) < $thumbWidth) {
        $currentImage = imagescale(
            $currentImage,
            $thumbWidth,
            imagesy($currentImage)
        );
    }
    if (imagesy($currentImage) < $thumbWidth) {
        $currentImage = imagescale(
            $currentImage,
            imagesx($currentImage),
            $thumbWidth
        );
    }

    imagecopy(
        $montage, // Destination image link resource
        $currentImage, // Source image link resource
        $xIndex * $montageIndexStep, // x-coordinate of destination point
        $yIndex * $montageIndexStep, // y-coordinate of destination point
        0, // x-coordinate of source point
        0, // y-coordinate of source point
        $montageIndexStep, // Source width
        $montageIndexStep  // Source height
    );
    imagedestroy($currentImage);
    $xIndex++;
    if ($xIndex > ($montageImagesPerRow - 1)) {
        $xIndex = 0;
        $yIndex++;
    }
}

if ($showFooter) {
    $yellow = imagecolorallocate($montage, 255, 255, 0);

    imagestring(
        $montage,
        4, // font 1-5
        5, // x
        $montageHeight + 6, // y
        $smt->site_name,
        $yellow
    );
    imagestring(
        $montage,
        2, // font 1-5
        5, // x
        $montageHeight + 24, // y
        str_replace('//', '', $smt->site_url),
        $yellow
    );
}

ob_start();
imagepng($montage);
$imageData = ob_get_contents();
ob_end_clean();

imagedestroy($montage);

$dataUrl = 'data:image/png;base64,' . base64_encode($imageData);

print '<p>'
. '<img src="' . $dataUrl . '"'
. ' width="' . $montageWidth . '"'
. ' height="' . ($montageHeight + $footerHeight) . '"'
. ' usemap="#montage"'
. '>'
. '</p>';

print '<p><b>' . sizeof($images) . '</b> images used in this montage:<br />';

$count = 0;
$areas = [];
$descs = [];
foreach ($images as $image) {
    $count++;
    $areas[$count] = Tools::url('info') . '?i=' . $image['pageid'];
    $descs[$count] = htmlspecialchars(Tools::stripPrefix($image['title']))
        . "\n" . $smt->displayLicensing($image);
    print '<br />#' . $count . ': '
    . '<a href="' . Tools::url('info') . '?i=' . $image['pageid'] . '">'
    . htmlspecialchars(Tools::stripPrefix($image['title'])) . '</a>'
    . ' - ' . $smt->displayLicensing($image)
    ;
}

print ''
. '<map name="montage">'
. '<area shape="rect" coords="0,0,50,50" href="' . $areas[1] . '" title="#1: ' . $descs[1] . '">'
. '<area shape="rect" coords="50,0,100,50" href="' . $areas[2] . '" title="#2: ' . $descs[2] . '">'
. '<area shape="rect" coords="0,50,50,100" href="' . $areas[3] . '" title="#3: ' . $descs[3] . '">'
. '<area shape="rect" coords="50,50,100,100" href="' . $areas[4] . '" title="#4: ' . $descs[4] . '">'
. '</map>';

print '</p>';
print '<p>Data URL: ' . number_format((float) strlen($dataUrl))
. ' characters<br /><textarea cols="60" rows="20">' . $dataUrl . '</textarea><br /></p>';
print '</div>';

$smt->includeFooter();
