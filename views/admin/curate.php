<?php
/**
 * Shared Media Tagger
 * Curation Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Curation Admin';
$smt->useJquery = true;
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
////////////////////////////////////////////////////////////////////////

if (isset($_GET) && $_GET) {
    curateBatch($smt);
}

$uncuratedCount = getUncuratedCount($smt);

$pageLimit = 20; // # of files per page

if (isset($_GET['l']) && Tools::isPositiveNumber($_GET['l'])) {
    $pageLimit = (int) $_GET['l'];
}
if ($pageLimit > 1000) {
    $pageLimit = 1000;
}
if ($pageLimit < 1) {
    $pageLimit = 1;
}

$sql = "SELECT *
        FROM media
        WHERE curated != '1'
        ORDER BY updated ASC
        LIMIT " . $pageLimit;

if (isset($_GET['i']) && Tools::isPositiveNumber($_GET['i'])) {
    $medias = $smt->getMedia($_GET['i']);
} else {
    $medias = $smt->database->queryAsArray($sql);
}

print '<form name="media" action="" method="GET">';

curationMenu();

print '<style>
.curation_container { background-color:#ddd; color:black; padding:10px; display:flex; flex-wrap:wrap; }
.curation_container img { margin:1px; }
.curation_keep { border:12px solid green; }
.curation_delete { border:12px solid red; }
.curation_que { border:12px solid grey; }
</style>';

print <<<EOT
<script type="text/javascript" language="javascript">

function mark_all_keep() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', true);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', false);
    });
    $("img").each( function() {
        $(this).prop('class','curation_keep');
    });
}

function mark_all_delete() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', false);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', true);
    });
    $("img").each( function() {
        $(this).prop('class','curation_delete');
    });
}

function mark_all_que() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', false);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', false);
    });
    $("img").each( function() {
        $(this).prop('class','curation_que');
    });
}

function curation_click(pageid) {
    var media = $('#' + pageid);
    var media_keep = $('#keep' + pageid);
    var media_delete = $('#delete' + pageid);
    switch( media.prop('class') ) {
        case 'curation_que':
            media.prop('class', 'curation_delete');
            media_keep.prop('checked', false);
            media_delete.prop('checked', true);
            return;
        case 'curation_delete':
            media.prop('class', 'curation_keep');
            media_keep.prop('checked', true);
            media_delete.prop('checked', false);
            return;
        case 'curation_keep':
            media.prop('class', 'curation_que');
            media_keep.prop('checked', false);
            media_delete.prop('checked', false);
            return;
    }
}
</script>
EOT;

print '<div class="curation_container">';

foreach ($medias as $media) {
    $thumb = $smt->getThumbnail($media);
    $url = $thumb['url'];
    $width = $thumb['width'];
    $height = $thumb['height'];
    $pageid = $media['pageid'];
    $imgInfo = str_replace("Array\n(", '', htmlentities(print_r($media, 1)));

    print '<div>';
    print '<a target="site" style="font-size:10pt; text-align:center;" href="'
        . $smt->url('info') . '?i=' . $pageid . '">' . $pageid . '</a><br />';

    print '<img name="' . $pageid . '" id="' . $pageid.'"  src="' . $url . '"'
        . ' width="' . $width . '" height="' . $height . '" title="'
        . $imgInfo . '" onclick="curation_click(this.id);" class="curation_que">';
    print '</div>';

    print '<input style="display:none;" type="checkbox" name="keep[]" id="keep' . $pageid
        . '" value="' . $pageid . '">';
    print '<input style="display:none;" type="checkbox" name="delete[]" id="delete' . $pageid
        . '" value="' . $pageid . '">';
}

print '<br />';
curationMenu();
print '</div>'; // end curation_container

print '</form>';
$smt->includeFooter();

/**
 *
 */
function curationMenu()
{
    global $uncuratedCount, $pageLimit;
    print '<div style="background-color:#ddd; color:black; padding-left:10px;">'
    . '<input type="submit" value="          Curate Marked Files        " />'
    . ' <span style="display:inline-block; font-size:90%;">Mark ALL '
    . ' <a href="javascript:mark_all_keep();">[KEEP]</a>'
    . ' <a href="javascript:mark_all_delete();">[DELETE]</a>'
    . ' <a href="javascript:mark_all_que();">[QUE]</a></span>'
    . ' - <a href="./curate.php?l='.$pageLimit.'">'.$pageLimit.'</a> of '
        . number_format($uncuratedCount) . ' in que'
    . '</div>';
}

/**
 * @param TaggerAdmin $smt
 */
function curateBatch(TaggerAdmin $smt)
{
    if (!empty($_GET['keep'])) {
        curateKeep($_GET['keep'], $smt);
    }
    if (!empty($_GET['delete'])) {
        curateDelete($_GET['delete'], $smt);
    }
}

/**
 * @param array $id_array
 * @param TaggerAdmin $smt
 * @return bool
 */
function curateKeep(array $id_array, TaggerAdmin $smt)
{
    if (!is_array($id_array) || !$id_array) {
        return false;
    }
    $ids = implode($id_array, ', ');
    $sql = "UPDATE media SET curated = '1', updated = CURRENT_TIMESTAMP WHERE pageid IN ($ids)";
    if ($smt->database->queryAsBool($sql)) {
        Tools::notice('Curate: KEEP ' . sizeof($id_array));
        return true;
    }
    Tools::error('ERROR setting media curated to KEEP: ' . $smt->database->lastError);
    return false;
}

/**
 * @param array $id_array
 * @param TaggerAdmin $smt
 * @return bool
 */
function curateDelete(array $id_array, TaggerAdmin $smt)
{
    if (!is_array($id_array) || !$id_array) {
        return false;
    }
    foreach ($id_array as $pageid) {
        $smt->deleteMedia($pageid);
    }
    Tools::notice('Curate: DELETE ' . sizeof($id_array));
    $smt->updateCategoriesLocalFilesCount();
    return true;
}

/**
 * @param TaggerAdmin $smt
 * @return string
 */
function getUncuratedCount(TaggerAdmin $smt)
{
    $count = $smt->database->queryAsArray("
        SELECT count(pageid) AS count
        FROM media
        WHERE curated != '1'
        ");
    if (isset($count[0]['count'])) {
        return $count[0]['count'];
    }
    Tools::error($smt->database->lastError);
    return 'ERR';
}
