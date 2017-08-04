<?php
// Shared Media Tagger
// User Admin

$number_of_images = 4;
$thumb_width = 50;
$montage_width = 100;
$montage_height = 100;
$montage_images_per_row = 2;
$montage_index_step = $thumb_width;

$show_footer = FALSE;
$footer_height = 0;

$mimetypes[] = 'image/jpeg';
$mimetypes[] = 'image/gif';
$mimetypes[] = 'image/png';

////////////////////////////////////////////////////////////////////
$init = __DIR__.'/../smt.php'; // Shared Media Tagger Main Class
if( !is_readable($init) ) {
    print 'ERROR: not readable: ' . $init;
    return;
}
require_once($init);
$init = __DIR__.'/smt-admin.php'; // Shared Media Tagger Admin Class
if( !is_readable($init) ) {
    print 'ERROR: not readable: ' . $init;
    return;
}
require_once($init);
$smt = new smt_admin(); // The Shared Media Tagger Admin Object
/////////////////////////////////////////////////////////////////////

$tag_id = (!empty($_GET['t']) && $smt->is_positive_number($_GET['t']))
    ? (int)$_GET['t']
    : 'R';

$smt->title = 'Create';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white"><p><a href="create.php">Create</a></p>';
print '<ul>'
. '<li>Montage 100x100, 2x2: <a href="create.php?montage=1&amp;t=R">Random Images</a></li>';

foreach( $smt->get_tags() as $tag ) {
    print '<li>Montage 100x100, 2x2: <a href="create.php?montage=1&amp;t='
    . $tag['id'] . '">Images tagged: ' . $tag['name'] . '</a></li>';
}
print '</ul>';

//print '<p>gd_info: ' . print_r(@gd_info(),1) . '</p>';
if( empty($_GET['montage']) ) {
    print '</div>'; $smt->include_footer(); return;
}

if( !function_exists('imagecreatetruecolor') ) {
    $smt->error('PHP GD Library NOT FOUND');
    print '</div>'; $smt->include_footer(); return;
}
//$smt->notice('PHP GD Library FOUND OK');

if( !class_exists("Imagick") ) {
    //$smt->error('Imagick NOT FOUND');
} else {
    //$smt->notice('Imagick FOUND OK');
}

switch( $tag_id ) {

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
        LIMIT ' . $number_of_images;
        $bind = array(':tag_id'=>$tag_id);
        break;

    case 'R':
        $sql = '
        SELECT m.*
        FROM media AS m
        ORDER BY RANDOM()
        LIMIT ' . $number_of_images;
        $bind = array();
        break;
}

$images = $smt->query_as_array($sql, $bind);
if( !$images ) {
    $smt->error('No images found in criteria');
    print '</div>';
    $smt->include_footer();
    return;
}

$smt->start_timer('imagecreate');
$montage = imagecreatetruecolor($montage_width, $montage_height + $footer_height);
$x_index = $y_index = 0;
foreach($images as $image) {

    $url = str_replace('325px', $thumb_width.'px', $image['thumburl']);

    switch( $image['thumbmime'] ) {
        case 'image/gif': $current_image = @imagecreatefromgif($url); break;
        case 'image/jpeg': $current_image = @imagecreatefromjpeg($url); break;
        case 'image/png': $current_image = @imagecreatefrompng($url);
        default:
            //print '<P>ERROR: unknown mime type</P>';
            continue;
    }
    if( !$current_image ) {
        print '<p>ERROR: cannot get image: ' . $url . '</p>';
        continue;
    }
    if( imagesx($current_image) < $thumb_width ) {
        $current_image = imagescale(
            $current_image,
            $thumb_width,
            imagesy($current_image)
        );
    }
    if( imagesy($current_image) < $thumb_width ) {
        $current_image = imagescale(
            $current_image,
            imagesx($current_image),
            $thumb_width
        );
    }

    imagecopy(
        $montage, // Destination image link resource
        $current_image, // Source image link resource
        $x_index * $montage_index_step, // x-coordinate of destination point
        $y_index * $montage_index_step, // y-coordinate of destination point
        0, // x-coordinate of source point
        0, // y-coordinate of source point
        $montage_index_step, // Source width
        $montage_index_step  // Source height
    );
    imagedestroy($current_image);
    $x_index++;
    if ($x_index > ($montage_images_per_row - 1) ) {
        $x_index = 0;
        $y_index++;
    }
}

if( $show_footer ) {
    $yellow = imagecolorallocate($montage, 255, 255, 0);

    imagestring( $montage,
        4, // font 1-5
        5, // x
        $montage_height + 6, // y
        $smt->site_name,
        $yellow
    );
    imagestring( $montage,
        2, // font 1-5
        5, // x
        $montage_height + 24, // y
        str_replace('//','',$smt->site_url),
        $yellow
    );
}

ob_start();
imagepng($montage);
$image_data = ob_get_contents();
ob_end_clean();

imagedestroy($montage);
$smt->end_timer('imagecreate');

$data_url = 'data:image/png;base64,' . base64_encode($image_data);

print '<p>'
. '<img src="' . $data_url . '"'
. ' width="' . $montage_width . '"'
. ' height="' . ($montage_height + $footer_height) . '"'
. ' usemap="#montage"'
. '>'
. '</p>';

print '<p><b>' . sizeof($images) . '</b> images used in this montage:<br />';

$count = 0;
$areas = array();
$descs = array();
foreach( $images as $image ) {
    $count++;
    $areas[$count] = $smt->url('info') . '?i=' . $image['pageid'];
    $descs[$count] = htmlspecialchars($smt->strip_prefix($image['title']))
        . "\n" . $smt->display_licensing($image);
    print '<br />#' . $count . ': '
    . '<a href="' . $smt->url('info') . '?i=' . $image['pageid'] . '">'
    . htmlspecialchars($smt->strip_prefix($image['title'])) . '</a>'
    . ' - ' . $smt->display_licensing($image)
    ;
}

print ''
. '<map name="montage">'
. '<area shape="rect" coords="0,0,50,50" href="'
. $areas[1] . '" title="#1: ' . $descs[1] . '">'
. '<area shape="rect" coords="50,0,100,50" href="'
. $areas[2] . '" title="#2: ' . $descs[2] . '">'
. '<area shape="rect" coords="0,50,50,100" href="'
. $areas[3] . '" title="#3: ' . $descs[3] . '">'
. '<area shape="rect" coords="50,50,100,100" href="'
. $areas[4] . '" title="#4: ' . $descs[4] . '">'
. '</map>';


print '</p>';

print '<p>Data URL: ' . number_format(strlen($data_url))
. ' characters<br /><textarea cols="60" rows="20">' . $data_url . '</textarea><br /></p>';

print '</div>';
$smt->include_footer();
