<?php
// Shared Media Tagger
// User Admin

$number_of_images = 9;
$thumb_width = 100;
$montage_width = 300;
$montage_height = 300;
$montage_images_per_row = 3;
$montage_index_step = $thumb_width;

$footer_height = 40;

$order_by = 'ORDER BY RANDOM()';
//$order_by = 'ORDER BY t.count DESC';


$mimetypes[] = 'image/jpeg';
$mimetypes[] = 'image/gif';
$mimetypes[] = 'image/png';


$init = __DIR__.'/../smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$init = __DIR__.'/smt-admin.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt_admin();

$tag_id = (!empty($_GET['t']) && $smt->is_positive_number($_GET['t'])) 
	? (int)$_GET['t'] 
	: 1;


$smt->title = 'Create';
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
$smt->include_admin_menu();
print '<div class="box white"><p><a href="create.php">Create</a></p>';
print '<ul>'
. '<li>Create Montag: '
. ' <a href="create.php?montage=1&amp;t=1">Tag:1</a>'
. ' <a href="create.php?montage=1&amp;t=2">Tag:2</a>'
. ' <a href="create.php?montage=1&amp;t=3">Tag:3</a>'
. ' <a href="create.php?montage=1&amp;t=4">Tag:4</a>'
. ' <a href="create.php?montage=1&amp;t=5">Tag:5</a></li>'
. '</ul>';

if( empty($_GET['montage']) ) {
	print '</div>';
	$smt->include_footer();
	return;
}

/*
$sql = '
SELECT m.*, t.count
FROM  media AS m, tagging AS t, tag as tg
WHERE t.media_pageid = m.pageid 
AND   t.tag_id = :tag_id
AND   tg.id = t.tag_id
AND   m.thumbmime IN ("' . implode($mimetypes, '", "') . '")
' . $order_by . ' LIMIT ' . $number_of_images;
*/
$sql = '
SELECT m.* 
FROM media AS m
WHERE m.thumbmime IN ("' . implode($mimetypes, '", "') . '")
ORDER BY RANDOM() LIMIT ' . $number_of_images;


$images = $smt->query_as_array($sql /*,array(':tag_id'=>$tag_id)*/ );

if( !$images ) {
	$smt->error('No tagged images for tag ID ' . $tag_id);
	print '</div>';
	$smt->include_footer();
	return;
}

$montage = imagecreatetruecolor($montage_width, $montage_height + $footer_height);
$x_index = $y_index = 0;
foreach($images as $image) {
	$url = str_replace('325px', $thumb_width.'px', $image['thumburl']);
	switch( $image['thumbmime'] ) {
		case 'image/gif': $current_image = imagecreatefromgif($url); break;
		case 'image/jpeg': $current_image = imagecreatefromjpeg($url); break;
		case 'image/png': $current_image = imagecreatefrompng($url);
		default: 
			//print '<P>ERROR: unknown mime type</P>'; 
			continue; 
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


ob_start();
imagepng($montage);
$image_data = ob_get_contents();
ob_end_clean();

imagedestroy($montage);

$data_url = 'data:image/pgn;base64,' . base64_encode($image_data);

print '<p>'
. '<img src="' . $data_url 
. '" width="' . $montage_width 
. '" height="' . ($montage_height + $footer_height) . '">'
. '</p>';

print '<p><b>' . sizeof($images) . '</b> images used in this montage:<br />';

$count = 0;
foreach( $images as $image ) {
	$count++;
	print '<br />#' . $count . ': ' 
 	. '<a href="' . $smt->url('info') . '?i=' . $image['pageid'] . '">' 
	. htmlspecialchars($smt->strip_prefix($image['title'])) . '</a>'
	. ' - ' . $smt->display_licensing($image)
	;
}

print '</p>';


//print '<pre>tag: ' . $tag_id . '<br />' . $sql .  '</pre>';
print '</div>';
$smt->include_footer();
