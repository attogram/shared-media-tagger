<?php
// Shared Media Tagger
// Curation Admin

$page_limit = 20; // # of files per page

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
////////////////////////////////////////////////////////////////////
$smt->title = 'Curation Admin';
$smt->use_jquery = TRUE;
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
////////////////////////////////////////////////////////////////////////

if( isset($_GET) && $_GET ) {
    curate_batch();
}

$uncurated_count = get_uncurated_count();

$sql = "SELECT *
        FROM media
        WHERE curated != '1'
        ORDER BY updated ASC
        LIMIT " . $page_limit;

$medias = $smt->query_as_array($sql);

print '<form name="media" action="" method="GET">';

print '<div style="background-color:#ddd; color:black; padding-left:10px;">'
. '<b>' . number_format($uncurated_count) . '</b> Media Files in Curation Que.'
. '  (curation mode: ' . $smt->site_info['curation'] . ')'
. '<br />'
. '<input type="submit" value="          Curate          " />'
. ' &nbsp;
Mark ALL:
<a href="javascript:mark_all_keep();">[KEEP]</a>
<a href="javascript:mark_all_delete();">[DELETE]</a>
<a href="javascript:mark_all_que();">[QUE]</a>'
. '</div>';

print '<style>
.curation_container {
    background-color:#ddd;
    color:black;
    padding:10px;
    display:flex;
    flex-wrap:wrap;
}
.curation_container img {
    margin:1px;
}
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

foreach($medias as $media) {
    $thumb = $smt->get_thumbnail($media);
    $url = $thumb['url'];
    $width = $thumb['width'];
    $height = $thumb['height'];
    $id = $media['pageid'];
    $img_info = str_replace("Array\n(",'',htmlentities(print_r($media,1)));
    print '<img name="'.$id.'" id="'.$id.'"  src="' . $url . '"'
        . ' width="' . $width . '" height="' . $height . '" title="'
        . $img_info . '" onclick="curation_click(this.id);" class="curation_que">';
    print '<input style="display:none;" type="checkbox" name="keep[]" id="keep'.$id.'" value="'.$id.'">';
    print '<input style="display:none;" type="checkbox" name="delete[]" id="delete'.$id.'" value="'.$id.'">';
}


print '</div>'; // end curation_container

print '</form>';
$smt->include_footer();

////////////////////////////////////////////////////////////////////////////
function curate_batch() {
    global $smt;
    curate_keep(@$_GET['keep']);
    curate_delete(@$_GET['delete']);
}


////////////////////////////////////////////////////////////////////////////
function curate_keep( $id_array ) {
    global $smt;
    if( !is_array($id_array) || !$id_array ) {
        return FALSE;
    }
    $ids = implode($id_array,', ');
    $sql = "UPDATE media SET curated = '1' WHERE pageid IN ($ids)";
    if( $smt->query_as_bool($sql) ) {
        $smt->notice('OK: KEPING: ' . $ids);
        return TRUE;
    }
    $smt->error('ERROR setting media curated to KEEP: ' . $smt->last_error);
    return FALSE;

}

////////////////////////////////////////////////////////////////////////////
function curate_delete( $id_array ) {
    global $smt;
    if( !is_array($id_array) || !$id_array ) {
        return FALSE;
    }
    foreach( $id_array as $pageid ) {
        $smt->delete_media($pageid);
    }
    $smt->notice('OK: DELETED: ' . implode($id_array,', '));
    return TRUE;
}

////////////////////////////////////////////////////////////////////////////
function get_uncurated_count() {
    global $smt;
    $count = $smt->query_as_array("
        SELECT count(pageid) AS count
        FROM media
        WHERE curated != '1'
        ");
    if( isset($count[0]['count'] ) ) {
        return $count[0]['count'];
    }
    $smt->error($smt->last_error);
    return 'ERR';
}