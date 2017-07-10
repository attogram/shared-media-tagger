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
	print $smt->delete_media($_GET['dm']) . '<hr />';
}

if( isset($_GET['dc']) ) {
	print delete_media_in_category( $smt->category_urldecode($_GET['dc'])) . '<hr />';
	$smt->vacuum();
}

// MENU ////////////////////////////////////////////
?>
<form action="" method="GET">
* Delete &amp; Block Media:
<input type="text" name="dm" value="" size="10" />
<input type="submit" value="  Delete via pageid  "/>
</form>
<br /><br />
<form action="" method="GET">
* Delete Media in Category:
<input type="text" name="dc" value="" size="30" />
<input type="submit" value="  Delete via Category Name  "/>
</form>
<br /><br />
* <a target="sqlite" href="<?php print $smt->url('admin'); ?>sqladmin.php?table=block&action=row_view">View/Edit Blocked Media</a>
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
		$r .= $smt->delete_media($pageid);
	}
	$r .= '</div><br />';
	return $r;
}
