<?php
/**
 * Shared Media Tagger
 * Browse all
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTagger $smt
 */

$pageLimit = 20; // # of files per page

$sort = 'random';
if (isset($_GET['s'])) {
    $sort = $_GET['s'];
}
$where = '';
switch ($sort) {
    default:
    case '':
    case 'random':
        $orderby = ' ORDER BY RANDOM()';
        break;
    case 'pageid':
        $orderby = ' ORDER BY pageid';
        $extra = 'pageid';
        break;
    case 'size':
        $orderby = ' ORDER BY size';
        $extra = 'size';
        $extra_numberformat = 1;
        break;
    case 'title':
        $orderby = ' ORDER BY title';
        break;
    case 'mime':
        $orderby = ' ORDER BY mime';
        $extra = 'mime';
        break;
    case 'width':
        $orderby = ' ORDER BY width';
        $extra = 'width';
        $extra_numberformat = 1;
        break;
    case 'height':
        $orderby = ' ORDER BY height';
        $extra = 'height';
        $extra_numberformat = 1;
        break;
    case 'datetimeoriginal':
        $orderby = ' ORDER BY datetimeoriginal';
        break;
    case 'timestamp':
        $orderby = ' ORDER BY timestamp';
        $extra = 'timestamp';
        break;
    case 'updated':
        $orderby = ' ORDER BY updated';
        $extra = 'updated';
        break;
    case 'licenseuri':
        $orderby = ' ORDER BY licenseuri';
        break;
    case 'licensename':
        $orderby = ' ORDER BY licensename';
        break;
    case 'licenseshortname':
        $orderby = ' ORDER BY licenseshortname';
        $extra = 'licenseshortname';
        break;
    case 'usageterms':
        $orderby = ' ORDER BY usageterms';
        $extra = 'usageterms';
        break;
    case 'attributionrequired':
        $orderby = ' ORDER BY attributionrequired';
        $extra = 'attributionrequired';
        break;
    case 'restrictions':
        $orderby = ' ORDER BY restrictions';
        $extra = 'restrictions';
        break;
    case 'user':
        $orderby = ' ORDER BY user';
        $extra = 'user';
        break;
    case 'duration':
        $orderby = ' ORDER BY duration';
        $extra = 'duration';
        break;
    case 'sha1':
        $orderby = ' ORDER BY sha1';
        break;
    case 'skin':
        $orderby = ' ORDER BY skin';
        $where = ' WHERE skin IS NOT NULL';
        $extra = 'skin';
        break;
    case 'ahash':
        $orderby = ' ORDER BY ahash';
        $where = ' WHERE ahash IS NOT NULL';
        break;
    case 'dhash':
        $orderby = ' ORDER BY dhash';
        $where = ' WHERE dhash IS NOT NULL';
        break;
    case 'phash':
        $orderby = ' ORDER BY phash';
        $where = ' WHERE phash IS NOT NULL';
        break;
}

if ($smt->siteInfo['curation'] == 1) {
    if ($where) {
        $where .= " AND curated = '1'";
    } else {
        $where = " WHERE curated = '1'";
    }
}

$dir = 'd';
$sql_dir = ' DESC';
if (isset($_GET['d'])) {
    switch ($_GET['d']) {
        case 'a':
            $dir = 'a';
            $sql_dir = ' ASC';
            break;
        case 'd':
            $dir = 'd';
            $sql_dir = ' DESC';
            break;
    }
}

switch ($sort) {
    default:
        $sql_count = 'SELECT count(pageid) AS count FROM media' . $where;
        $raw_count = $smt->queryAsArray($sql_count);
        $result_size = 0;
        if ($raw_count) {
            $result_size = $raw_count[0]['count'];
        }
        break;

    case 'random':
        $result_size = $pageLimit;
        break;
}

//////////////////////////////////////////
$pager = '';
$sql_offset = '';

$offset = isset($_GET['o']) ? $_GET['o'] : 0;

$current_page = ($offset / $pageLimit) + 1;
$number_of_pages = ceil($result_size / $pageLimit);

if ($sort != 'random' && ($result_size > $pageLimit)) {
    $sql_offset = " OFFSET $offset";
    $page_count = 0;
    $pager = '<small>page: ';
    for($x = 0; $x < $result_size; $x+=$pageLimit) {
        $page_count++;

        if ($current_page == $page_count) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . pagerLink($x) . '&nbsp;' . $page_count . '&nbsp;</a> </span>';
            continue;
        }

        $edge_buffer = 3; // always show first and last pages
        $buffer = 5; // always show pages before/after current page
        if ($page_count <= $edge_buffer
            || $page_count > ($number_of_pages-$edge_buffer)
            || (($page_count > ($current_page-$buffer)) && ($page_count < ($current_page+$buffer)))
        ) {
            $pager .= pagerLink($x) . '&nbsp;' . $page_count . ' </a>';
            continue;
        }

        if ($page_count % 50 == 0) {
            $pager .= pagerLink($x) . '. </a>';
        }
    }
    $pager .= '</small>';
}
//////////////////////////////////////////


$sql = 'SELECT * FROM media';
$sql .= $where . $orderby . $sql_dir . ' LIMIT ' . $pageLimit . $sql_offset;

$medias = $smt->queryAsArray($sql);


$smt->title = 'Browse ' . number_format($result_size) . ' Files, sorted by ' . $sort . ' ' . $sql_dir
    . ', page #' . $current_page . ' - ' . $smt->siteName;
$smt->includeHeader();
$smt->includeMediumMenu();
////////////////////////////////////////////////////////////////////////

print '<div class="box white">';
print '<form>
Browse Files, sorty by <select name="s">
<option value="random"' . $smt->isSelected('random', $sort) . '>Random</option>
<option value="pageid"' . $smt->isSelected('pageid', $sort) . '>ID</option>
<option value="size"' . $smt->isSelected('size', $sort) . '>Size</option>
<option value="title"' . $smt->isSelected('title', $sort) . '>File Name</option>
<option value="mime"' . $smt->isSelected('mime', $sort) . '>Mime Type</option>
<option value="width"' . $smt->isSelected('width', $sort) . '>Width</option>
<option value="height"' . $smt->isSelected('height', $sort) . '>Height</option>
<option value="datetimeoriginal"' . $smt->isSelected('datetimeoriginal', $sort) . '>Original Datetime</option>
<option value="timestamp"' . $smt->isSelected('timestamp', $sort) . '>Upload Datetime</option>
<option value="updated"' . $smt->isSelected('updated', $sort) . '>Last Updated</option>
<option value="licenseuri"' . $smt->isSelected('licenseuri', $sort) . '>License URI</option>
<option value="licensename"' . $smt->isSelected('licensename', $sort) . '>License Name</option>
<option value="licenseshortname"' . $smt->isSelected('licenseshortname', $sort) . '>License Short Name</option>
<option value="usageterms"' . $smt->isSelected('usageterms', $sort) . '>Usage Terms</option>
<option value="attributionrequired"' . $smt->isSelected('attributionrequired', $sort) . '>Attribution Required</option>
<option value="restrictions"' . $smt->isSelected('restrictions', $sort) . '>Restrictions</option>
<option value="user"' . $smt->isSelected('user', $sort) . '>Uploading User</option>
<option value="duration"' . $smt->isSelected('duration', $sort) . '>Duration</option>
<option value="sha1"' . $smt->isSelected('sha1', $sort) . '>Sha1 Hash</option>
<option value="skin"' . $smt->isSelected('skin', $sort) . '>Skin Percentage</option>
<option value="dhash"' . $smt->isSelected('dhash', $sort) . '>Difference Hash</option>
<option value="phash"' . $smt->isSelected('phash', $sort) . '>Perceptual Hash</option>
<option value="ahash"' . $smt->isSelected('ahash', $sort) . '>Average Hash</option>
</select>
<select name="d">
<option value="d"' . $smt->isSelected('d', $dir) . '>Descending</option>
<option value="a"' . $smt->isSelected('a', $dir) . '>Ascending</option>
</select>
<input type="submit" value="Browse" />
</form><br />' . number_format($result_size) . ' Files' . ($pager ? ', '.$pager : '');

if ($smt->isAdmin()) {
    print '<form action="' . $smt->url('admin') .'media.php" method="GET" name="media">';
    print $smt->displayAdminMediaListFunctions();
}

print '<br clear="all" />';

foreach($medias as $media) {
    if (isset($extra)) {
        print '<div style="display:inline-block;">'
        . '<span style="background-color:#eee; border:1px solid #f99; font-size:80%;">';

        if (isset($extra_numberformat)) {
            print number_format($media[$extra]);
        } else {
            print $media[$extra];
        }
        print '</span><br />';
    }
    print $smt->displayThumbnailBox($media);
    if (isset($extra)) {
        print '</div>';
    }
}

print '<br clear="all" />';

if ($smt->isAdmin()) {
     print $smt->displayAdminMediaListFunctions() . '</form>';
}

if ($pager) {
    print '<p>' . $pager . '</p>';
}

print '</div>';
$smt->includeFooter();

/**
 * @param $offset
 * @return string
 */
function pagerLink($offset) {
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
