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
    case 'pageid': $orderby = ' ORDER BY pageid'; $extra = 'pageid'; break;
    case 'size': $orderby = ' ORDER BY size'; $extra = 'size'; $extra_numberformat = 1; break;
    case 'title':  $orderby = ' ORDER BY title'; break;
    case 'mime': $orderby = ' ORDER BY mime'; $extra = 'mime'; break;
    case 'width': $orderby = ' ORDER BY width'; $extra = 'width'; $extra_numberformat = 1; break;
    case 'height': $orderby = ' ORDER BY height'; $extra = 'height'; $extra_numberformat = 1; break;
    case 'datetimeoriginal': $orderby = ' ORDER BY datetimeoriginal'; break;
    case 'timestamp': $orderby = ' ORDER BY timestamp'; $extra = 'timestamp'; break;
    case 'updated': $orderby = ' ORDER BY updated'; $extra = 'updated'; break;
    case 'licenseuri': $orderby = ' ORDER BY licenseuri'; break;
    case 'licensename': $orderby = ' ORDER BY licensename'; break;
    case 'licenseshortname': $orderby = ' ORDER BY licenseshortname'; $extra = 'licenseshortname'; break;
    case 'usageterms': $orderby = ' ORDER BY usageterms'; $extra = 'usageterms'; break;
    case 'attributionrequired': $orderby = ' ORDER BY attributionrequired'; $extra = 'attributionrequired'; break;
    case 'restrictions': $orderby = ' ORDER BY restrictions'; $extra = 'restrictions'; break;
    case 'user': $orderby = ' ORDER BY user'; $extra = 'user'; break;
    case 'duration': $orderby = ' ORDER BY duration'; $extra = 'duration';  break;
    case 'sha1': $orderby = ' ORDER BY sha1'; break;
    case 'skin': $orderby = ' ORDER BY skin'; $where = ' WHERE skin IS NOT NULL'; $extra = 'skin'; break;
    case 'ahash': $orderby = ' ORDER BY ahash'; $where = ' WHERE ahash IS NOT NULL'; break;
    case 'dhash': $orderby = ' ORDER BY dhash'; $where = ' WHERE dhash IS NOT NULL'; break;
    case 'phash': $orderby = ' ORDER BY phash'; $where = ' WHERE phash IS NOT NULL'; break;
}

$dir = 'd'; $sql_dir = ' DESC';
if( isset($_GET['d']) ) {
    switch($_GET['d']) {
        case 'a': $dir = 'a'; $sql_dir = ' ASC'; break;
        case 'd': $dir = 'd'; $sql_dir = ' DESC'; break;
    }
}

switch( $sort ) {
    default:
        $sql_count = 'SELECT count(pageid) AS count FROM media' . $where;
        $raw_count = $smt->query_as_array($sql_count);
        $result_size = 0;
        if( $raw_count ) {
            $result_size = $raw_count[0]['count'];
        }
        break;

    case 'random':
        $result_size = $page_limit;
        break;
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

//////////////////////////////////////////
$pager = '';
$sql_offset = '';

$offset = isset($_GET['o']) ? $_GET['o'] : 0;

$current_page = ($offset / $page_limit) + 1;
$number_of_pages = ceil($result_size / $page_limit);

//$smt->notice("page_limit:$page_limit , number_of_pages:$number_of_pages ,  offset:$offset ,  current_page:$current_page , result_size:$result_size ,  sort:$sort");

if( $sort != 'random' && ($result_size > $page_limit) ) {

    $sql_offset = " OFFSET $offset";
    $page_count = 0;
    $pager = '<small>page: ';
    for( $x = 0; $x < $result_size; $x+=$page_limit ) {

        $page_count++;

        if( $current_page == $page_count ) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . pager_link($x) . '&nbsp;' . $page_count . '&nbsp;</a> </span>';
            continue;
        }

        $edge_buffer = 3; // always show first and last pages
        $buffer = 5; // always show pages before/after current page
        if(    $page_count <= $edge_buffer
            || $page_count > ($number_of_pages-$edge_buffer)
            || (  ($page_count > ($current_page-$buffer)) && ($page_count < ($current_page+$buffer))  )
        ) {
            $pager .= pager_link($x) . '&nbsp;' . $page_count . ' </a>';
            continue;
        }

        if( $page_count % 50 == 0 ) {
            $pager .= pager_link($x) . '. </a>';
        }
    }
    $pager .= '</small>';
}
//////////////////////////////////////////


$sql = 'SELECT * FROM media';
$sql .= $where . $orderby . $sql_dir . ' LIMIT ' . $page_limit . $sql_offset;

$medias = $smt->query_as_array($sql);


$smt->title = 'Browse ' . number_format($result_size) . ' Files, sorted by ' . $sort . ' ' . $sql_dir . ', page #'.$current_page.' - ' . $smt->site_name;
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
////////////////////////////////////////////////////////////////////////

print '<div class="box white">';
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
<option value="dhash"' . $smt->is_selected('dhash', $sort) . '>Difference Hash</option>
<option value="phash"' . $smt->is_selected('phash', $sort) . '>Perceptual Hash</option>
<option value="ahash"' . $smt->is_selected('ahash', $sort) . '>Average Hash</option>
</select>
<select name="d">
<option value="d"' . $smt->is_selected('d', $dir) . '>Descending</option>
<option value="a"' . $smt->is_selected('a', $dir) . '>Ascending</option>
</select>
<input type="submit" value="Browse" />
</form><br />' . number_format($result_size) . ' Files' . ($pager ? ', '.$pager : '');


if( $smt->is_admin() ) {
    print '<form action="' . $smt->url('admin') .'media.php" method="GET" name="media">';
    print $smt->display_admin_media_list_functions();
}

print '<br clear="all" />';


foreach($medias as $media) {
    if( isset($extra) ) {
        print '<div style="display:inline-block;">'
        . '<span style="background-color:#eee; border:1px solid #f99; font-size:80%;">';

        if( isset($extra_numberformat) ) {
            print number_format($media[$extra]);
        } else {
            print $media[$extra];
        }
        print '</span><br />';
    }
    print $smt->display_thumbnail_box($media);
    if( isset($extra) ) {
        print '</div>';
    }
}

print '<br clear="all" />';

if( $smt->is_admin() ) {
     print $smt->display_admin_media_list_functions(). '</form>';
}

if( $pager ) {
    print '<p>' . $pager . '</p>';
}

print '</div>';
$smt->include_footer();
