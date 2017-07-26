<?php
// Shared Media Tagger
// Media Analysis Admin

$init = __DIR__.'/../smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$init = __DIR__.'/smt-admin.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$smt = new smt_admin();

$smt->title = 'Media Analysis Admin';
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
$smt->include_admin_menu();
print '<div class="box white"><p><a href="./media-analysis.php">' . $smt->title . '</a>:</p>';

// MENU ////////////////////////////////////////////
?>
* <a href="?report">Skin Percentage Report</a>
<br /><br />
<form action="" method="GET">
* Skin Percentage Detection:
<input type="text" name="skin" value="" size="10" />
<input type="submit" value="  Skin Percentage Detection via pageid  "/>
</form>
<hr />
<?php

if( isset($_GET['report']) ) {
	skin_report();
}

if( isset($_GET['skin']) ) {
	$smt->get_media_skin_percentage( $_GET['skin'] );
}

print '</div>';
$smt->include_footer();


////////////////////
function skin_report() {
	global $smt;
	$report = '';
	
	if( isset($_GET['update']) ) {
		$limit = $_GET['update'];
		if( !$limit || !$smt->is_positive_number($limit) ) {
			$limit = 5;
		}
		$updates = $smt->query_as_array('SELECT pageid, updated FROM media ORDER BY updated LIMIT ' . $limit);
		foreach( $updates as $update ) {
			$smt->get_media_skin_percentage( $update['pageid'] );
		}
	}
		
	$medias = $smt->query_as_array('SELECT pageid, skin, updated FROM media ORDER BY skin DESC');
	$null_skin = $null_updated = 0;
	
	foreach( $medias as $media ) {
		if( $media['skin'] == NULL ) { 
			$null_skin++;
			$media['skin'] = 'NULL '; 
		}
		if( $media['updated'] == NULL ) { 
			$null_updated++;
			$media['updated'] = 'NULL'; 
		}
		$report .= '<br />' 
		. '<a target="site" href="' . $smt->url('info') . '?i=' . $media['pageid'] . '">'
		. str_pad($media['pageid'], 10, ' ') . '</a> '
		. str_pad($media['skin'], 5, '0') . '   '
		. $media['updated']
		;
	}

	$report .= '</pre>';
	
	
	$header = '';
	$header .= '<p><a href="?report&amp;update=5">Update Skin Percentages - Update x5 files</a></p>';
	$header .= '<pre>Skin Percentage Report @ ' . $smt->time_now() . ' UTC<br />';
	$header .= '<br />' . number_format(sizeof($medias)) . ' media filles';
	$header .= '<br />' . number_format($null_skin) . ' NULL skins';
	$header .= '<br />' . number_format($null_updated) . ' NULL updated<br />';	
	$header .= '<br />Pageid     Skin      Updated';
	$header .= '<br />------     -----     -------------------';
	



	print $header . $report;

}
