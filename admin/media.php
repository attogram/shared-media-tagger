<?php
// Shared Media Tagger
// Media Admin

$f = __DIR__.'/../smt.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$f = __DIR__.'/smt-admin.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$smt = new smt_admin('Media Admin');
$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>Media Admin:</p>';


if( isset($_GET['dm']) ) {
	print delete_media($_GET['dm']);
	$smt->vacuum();
}

if( isset($_GET['dc']) ) {
	print delete_media_in_category( $smt->category_urldecode($_GET['dc']));
	$smt->vacuum();
}

// MENU ////////////////////////////////////////////
?>
<form action="" method="GET">
* Delete Media:
<input type="text" name="dm" value="" size="10" />
<input type="submit" value="  Delete via pageid  "/>
</form>
<br /><br />
<form action="" method="GET">
* Delete Media in Category:
<input type="text" name="dc" value="" size="30" />
<input type="submit" value="  Delete via Category Name  "/>
</form>
<?php

print '</div>';
$smt->include_footer();

// TODO: move functions into smt-admin class

////////////////////////////////////////////////////
function delete_media_in_category( $category_name ) {
	global $smt;
	//$smt->notice('::delete_media_in_category: category_name: ' . $category_name);

	if( !$category_name || !is_string($category_name) ) {
		$smt->error('::delete_media_in_category: Invalid Category Name: ' . $category_name);
		return FALSE;
	}
	$r = '<div style="white-space:nowrap; font-family:monospace; background-color:lightsalmon;">'
	. 'Deleting Media in <b>' . $category_name . '</b>';
	
	$media = $smt->get_media_in_category( $category_name );
	
	$r .= '<br /><b>' . count($media) . '</b> Media files found in Category';
	
	foreach( $media as $pageid ) {
		$r .= '<br />Deleting #' . $pageid;
		$r .= delete_media($pageid);
	}
	$r .= '</div><br />';
	return $r;
}


////////////////////////////////////////////////////
function delete_media( $pageid ) {
	global $smt;
	
	if( !$pageid || !$smt->is_positive_number($pageid) ) {
		$smt->error('Invalid PageID');
		return;
	}
	 
	$r = '<div style="white-space:nowrap;  font-family:monospace; background-color:lightsalmon;">'
	. 'Deleting Media :pageid = ' . $pageid;
	
	$sql = array();
	$sql[] = 'DELETE FROM media WHERE pageid = :pageid';
	$sql[] = 'DELETE FROM category2media WHERE media_pageid = :pageid';
	$sql[] = 'DELETE FROM tagging WHERE media_pageid = :pageid';
	$bind = array(':pageid'=>$pageid);

	foreach( $sql as $s ) {
		if( $x = $smt->query_as_bool($s, $bind) ) {
			$r .= '<br />OK: ' . $s;
		} else {
			$r .= '<br />ERROR: ' . $s;
		}
		
	}
	$r .= '</div><br />';
	return $r;
	
}