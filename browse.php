<?php
// Shared Media Tagger
// Browse All

$page_limit = 20; // # of files per page

$init = __DIR__.'/smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$smt = new smt();

$sort = 'random';
if( isset($_GET['s']) ) {
	$sort = $_GET['s'];
}
$where = '';
switch( $sort ) {
	default:
	case '':
	case 'random': $orderby = ' ORDER BY RANDOM()'; break;
	case 'pageid': $orderby = ' ORDER BY pageid'; break;
	case 'size': $orderby = ' ORDER BY size'; break;
	case 'title':  $orderby = ' ORDER BY title'; break;
	case 'mime': $orderby = ' ORDER BY mime'; break;
	case 'width': $orderby = ' ORDER BY width'; break;
	case 'height': $orderby = ' ORDER BY height'; break;
	case 'datetimeoriginal': $orderby = ' ORDER BY datetimeoriginal'; break;
	case 'timestamp': $orderby = ' ORDER BY timestamp'; break;
	case 'updated': $orderby = ' ORDER BY updated'; break;
	case 'licenseuri': $orderby = ' ORDER BY licenseuri'; break;
	case 'licensename': $orderby = ' ORDER BY licensename'; break;
	case 'licenseshortname': $orderby = ' ORDER BY licenseshortname'; break;
	case 'usageterms': $orderby = ' ORDER BY usageterms'; break;
	case 'attributionrequired': $orderby = ' ORDER BY attributionrequired'; break;
	case 'restrictions': $orderby = ' ORDER BY restrictions'; break;
	case 'user': $orderby = ' ORDER BY user'; break;
	case 'duration': $orderby = ' ORDER BY duration'; break;
	case 'sha1': $orderby = ' ORDER BY sha1'; break;
	case 'skin': $orderby = ' ORDER BY skin'; $where = ' WHERE skin IS NOT NULL AND skin > 0'; break;
}

$dir = 'd'; $sql_dir = ' DESC';
if( isset($_GET['d']) ) {
	switch($_GET['d']) {
		case 'a': $dir = 'a'; $sql_dir = ' ASC'; break;
		case 'd': $dir = 'd'; $sql_dir = ' DESC'; break;
	}
}
$sql_count = 'SELECT count(pageid) AS count FROM media' . $where;
$raw_count = $smt->query_as_array($sql_count);
$result_size = 0;
if( $raw_count ) {
	$result_size = $raw_count[0]['count'];
}


///////////////
function pager_link($offset) {
	global $sort, $dir;
	$link = '<a href="?o=' . $offset;
	if( $sort ) { 
		$link .= '&amp;s=' . $sort; 
	}
	if( $dir ) {
		$link .= '&amp;d=' . $dir;
	}
	$link .= '">';
	return $link;
	
}
///////////////

$pager = '';
$sql_offset = '';
$current_page = 1;
if( $sort != 'random' && ($result_size > $page_limit) ) {
    $offset = isset($_GET['o']) ? $_GET['o'] : 0;
    $sql_offset = " OFFSET $offset";
    $page_count = 0;
    $pager = '<small>page: ';
    for( $x = 0; $x < $result_size; $x+=$page_limit ) {
        if( $x == $offset ) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . '&nbsp;' . ++$page_count . '&nbsp;</span> ';
			$current_page = $page_count;
            continue;
        }
        $pager .= pager_link($x) . '&nbsp;' . ++$page_count . '&nbsp;</a> ';
    }
	$pager .= '</small>';
}

$sql = 'SELECT * FROM media';
$sql .= $where . $orderby . $sql_dir . ' LIMIT ' . $page_limit . $sql_offset;

$medias = $smt->query_as_array($sql);


$smt->title = 'Browse ' . number_format($result_size) . ' Files, sorted by ' . $sort . ' ' . $sql_dir . ', page #'.$current_page.' - ' . $smt->site_name;
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
////////////////////////////////////////////////////////////////////////

print '<div class="box white">';
//$smt->notice($sql);

print '<form>
Browse Files, sorty by <select name="s">
<option value="random"' . $smt->is_selected('random', $sort) . '>Random</option>
<option value="pageid"' . $smt->is_selected('pageid', $sort) . '>ID</option>
<option value="size"' . $smt->is_selected('size', $sort) . '>Size</option>
<option value="title"' . $smt->is_selected('title', $sort) . '>File Name</option>
<option value="mime"' . $smt->is_selected('mime', $sort) . '>Mime Type</option>
<option value="width"' . $smt->is_selected('width', $sort) . '>Width</option>
<option value="height"' . $smt->is_selected('height', $sort) . '>Height</option>
<option value="datetimeoriginal"' . $smt->is_selected('datetimeoriginal', $sort) . '>Original Datetime</option>
<option value="timestamp"' . $smt->is_selected('timestamp', $sort) . '>Upload Datetime</option>
<option value="updated"' . $smt->is_selected('updated', $sort) . '>Last Updated</option>
<option value="licenseuri"' . $smt->is_selected('licenseuri', $sort) . '>License URI</option>
<option value="licensename"' . $smt->is_selected('licensename', $sort) . '>License Name</option>
<option value="licenseshortname"' . $smt->is_selected('licenseshortname', $sort) . '>License Short Name</option>
<option value="usageterms"' . $smt->is_selected('usageterms', $sort) . '>Usage Terms</option>
<option value="attributionrequired"' . $smt->is_selected('attributionrequired', $sort) . '>Attribution Required</option>
<option value="restrictions"' . $smt->is_selected('restrictions', $sort) . '>Restrictions</option>
<option value="user"' . $smt->is_selected('user', $sort) . '>Uploading User</option>
<option value="duration"' . $smt->is_selected('duration', $sort) . '>Duration</option>
<option value="sha1"' . $smt->is_selected('sha1', $sort) . '>Sha1 Hash</option>
<option value="skin"' . $smt->is_selected('skin', $sort) . '>Skin Percentage</option>
</select>
<select name="d">
<option value="d"' . $smt->is_selected('d', $dir) . '>Descending</option>
<option value="a"' . $smt->is_selected('a', $dir) . '>Ascending</option>
</select>

<input type="submit" value="Browse" />
</form>
<br />
' . number_format($result_size) . ' Files' . ($pager ? ', '.$pager : '') . '<br clear="all" />';


foreach($medias as $media) {
	print $smt->display_thumbnail_box($media);
}










if( $pager ) {
    print '<p>' . $pager . '</p>';
}


print '</div>';
$smt->include_footer();
