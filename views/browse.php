<?php
/**
 * Shared Media Tagger
 * Browse all
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

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
        $extraNumberformat = 1;
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
        $extraNumberformat = 1;
        break;
    case 'height':
        $orderby = ' ORDER BY height';
        $extra = 'height';
        $extraNumberformat = 1;
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
}

if (Config::$siteInfo['curation'] == 1) {
    if ($where) {
        $where .= " AND curated = '1'";
    } else {
        $where = " WHERE curated = '1'";
    }
}

$dir = 'd';
$sqlDir = ' DESC';
if (isset($_GET['d'])) {
    switch ($_GET['d']) {
        case 'a':
            $dir = 'a';
            $sqlDir = ' ASC';
            break;
        case 'd':
            $dir = 'd';
            $sqlDir = ' DESC';
            break;
    }
}

switch ($sort) {
    default:
        $sqlCount = 'SELECT count(pageid) AS count FROM media' . $where;
        $rawCount = $smt->database->queryAsArray($sqlCount);
        $resultSize = 0;
        if ($rawCount) {
            $resultSize = $rawCount[0]['count'];
        }
        break;

    case 'random':
        $resultSize = $pageLimit;
        break;
}

//////////////////////////////////////////
$pager = '';
$sqlOffset = '';

$offset = isset($_GET['o']) ? $_GET['o'] : 0;

$currentPage = ($offset / $pageLimit) + 1;
$numberOfPages = ceil($resultSize / $pageLimit);

if ($sort != 'random' && ($resultSize > $pageLimit)) {
    $sqlOffset = " OFFSET $offset";
    $pageCount = 0;
    $pager = '<small>page: ';
    for ($count = 0; $count < $resultSize; $count += $pageLimit) {
        $pageCount++;

        if ($currentPage == $pageCount) {
            $pager .= '<span style="font-weight:bold; background-color:darkgrey; color:white;">'
            . pagerLink($count) . '&nbsp;' . $pageCount . '&nbsp;</a> </span>';
            continue;
        }

        $edgeBuffer = 3; // always show first and last pages
        $buffer = 5; // always show pages before/after current page
        if ($pageCount <= $edgeBuffer
            || $pageCount > ($numberOfPages-$edgeBuffer)
            || (($pageCount > ($currentPage-$buffer)) && ($pageCount < ($currentPage+$buffer)))
        ) {
            $pager .= pagerLink($count) . '&nbsp;' . $pageCount . ' </a>';
            continue;
        }

        if ($pageCount % 50 == 0) {
            $pager .= pagerLink($count) . '. </a>';
        }
    }
    $pager .= '</small>';
}
//////////////////////////////////////////


$sql = 'SELECT * FROM media';
$sql .= $where . $orderby . $sqlDir . ' LIMIT ' . $pageLimit . $sqlOffset;

$medias = $smt->database->queryAsArray($sql);

$smt->title = 'Browse ' . number_format($resultSize) . ' Files, sorted by ' . $sort . ' ' . $sqlDir
    . ', page #' . $currentPage . ' - ' . Config::$siteName;
$smt->includeHeader();
$smt->includeMediumMenu();
////////////////////////////////////////////////////////////////////////

print '<div class="box white">';
print '<form>
Browse Files, sorty by <select name="s">
<option value="random"' . Tools::isSelected('random', $sort) . ' >Random</option>
<option value="pageid"' . Tools::isSelected('pageid', $sort) . '>ID</option>
<option value="size"' . Tools::isSelected('size', $sort) . '>Size</option>
<option value="title"' . Tools::isSelected('title', $sort) . '>File Name</option>
<option value="mime"' . Tools::isSelected('mime', $sort) . '>Mime Type</option>
<option value="width"' . Tools::isSelected('width', $sort) . '>Width</option>
<option value="height"' . Tools::isSelected('height', $sort) . '>Height</option>
<option value="datetimeoriginal"' . Tools::isSelected('datetimeoriginal', $sort) . '>Original Datetime</option>
<option value="timestamp"' . Tools::isSelected('timestamp', $sort) . '>Upload Datetime</option>
<option value="updated"' . Tools::isSelected('updated', $sort) . '>Last Updated</option>
<option value="licenseuri"' . Tools::isSelected('licenseuri', $sort) . '>License URI</option>
<option value="licensename"' . Tools::isSelected('licensename', $sort) . '>License Name</option>
<option value="licenseshortname"' . Tools::isSelected('licenseshortname', $sort) . '>License Short Name</option>
<option value="usageterms"' . Tools::isSelected('usageterms', $sort) . '>Usage Terms</option>
<option value="attributionrequired"' . Tools::isSelected('attributionrequired', $sort) . '>Attribution Required</option>
<option value="restrictions"' . Tools::isSelected('restrictions', $sort) . '>Restrictions</option>
<option value="user"' . Tools::isSelected('user', $sort) . '>Uploading User</option>
<option value="duration"' . Tools::isSelected('duration', $sort) . '>Duration</option>
<option value="sha1"' . Tools::isSelected('sha1', $sort) . '>Sha1 Hash</option>
</select>
<select name="d">
<option value="d"' . Tools::isSelected('d', $dir) . '>Descending</option>
<option value="a"' . Tools::isSelected('a', $dir) . '>Ascending</option>
</select>
<input type="submit" value="Browse" />
</form><br />' . number_format($resultSize) . ' Files' . ($pager ? ', '.$pager : '');

if (Tools::isAdmin()) {
    print '<form action="' . Tools::url('admin') .'media.php" method="GET" name="media">';
    print $smt->displayAdminMediaListFunctions();
}

print '<br clear="all" />';

foreach ($medias as $media) {
    if (isset($extra)) {
        print '<div style="display:inline-block;">'
        . '<span style="background-color:#eee; border:1px solid #f99; font-size:80%;">';

        if (isset($extraNumberformat)) {
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

if (Tools::isAdmin()) {
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
function pagerLink($offset)
{
    global $sort, $dir;
    $link = '<a href="?o=' . $offset;
    if ($sort) {
        $link .= '&amp;s=' . $sort;
    }
    if ($dir) {
        $link .= '&amp;d=' . $dir;
    }
    $link .= '">';

    return $link;
}
