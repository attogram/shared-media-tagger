<?php
// Shared Media Tagger
// Media Analysis Admin

set_time_limit(120);

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
* <a href="?r=skin">Skin Percentage Report</a>
<br />
<form action="" method="GET">
* Get Skin Percentage:
<input type="text" name="skin" value="" size="10" />
<input type="submit" value="  Skin Percentage via pageid  "/>
</form>

<br /><br />

* <a href="?r=hash">Image Hash Report</a>
<br />
<form action="" method="GET">
* Get Image Hashes:
<input type="text" name="hash" value="" size="10" />
<input type="submit" value="  Hashes via pageid  "/>
</form>

<hr />
<?php

switch( @$_GET['r'] ) {
    case 'skin': skin_report(); break;
    case 'hash': hash_report(); break;
}

if( isset($_GET['skin']) ) {
    $smt->get_media_skin_percentage( $_GET['skin'] );
}

if( isset($_GET['hash']) ) {
    get_perceptual_hash( $_GET['hash'] );
}

print '</div>';
$smt->include_footer();

////////////////////
function get_perceptual_hash( $pageid ) {

    global $smt;

    $media = $smt->get_media($pageid);
    if( !$media ) {
        $smt->error('Media Not Found');
        return FALSE;
    }
    $url = str_replace('325px','64px', $media[0]['thumburl']);

    //print '<p>' . $media[0]['pageid'] . ': ' . $url . '</p>';

    // TMP until composer setup
    require_once('../use/imagehash/src/ImageHash.php');
    require_once('../use/imagehash/src/Implementation.php');
    require_once('../use/imagehash/src/Implementations/AverageHash.php');
    require_once('../use/imagehash/src/Implementations/DifferenceHash.php');
    require_once('../use/imagehash/src/Implementations/PerceptualHash.php');

    $hashes = array();

    $smt->start_timer('hasher');

    $hasher = new Jenssegers\ImageHash\ImageHash(NULL, 'dec');  // dec, hex

    $hasher->implementation = new Jenssegers\ImageHash\Implementations\AverageHash;
    $hashes[':ahash'] = str_pad(decbin($hasher->hash($url)), 32, '0', STR_PAD_LEFT);

    $hasher->implementation = new Jenssegers\ImageHash\Implementations\DifferenceHash;
    $hashes[':dhash'] = str_pad(decbin($hasher->hash($url)), 32, '0', STR_PAD_LEFT);

    $hasher->implementation = new Jenssegers\ImageHash\Implementations\PerceptualHash;
    $hashes[':phash'] = str_pad(decbin($hasher->hash($url)), 32, '0', STR_PAD_LEFT);

    $smt->end_timer('hasher');

    save_hashes($pageid, $hashes);

}

////////////////////
function save_hashes($pageid, $hashes) {
    global $smt;
    while( list($name,$hash) = each($hashes) ) {

    }
    $sql = 'UPDATE media
    SET ahash = :ahash, dhash = :dhash, phash = :phash, updated = :updated
    WHERE pageid = :pageid';
    $hashes[':updated'] = $smt->time_now();
    $hashes[':pageid'] = $pageid;

    $response = $smt->query_as_bool($sql, $hashes);
    if( $response ) {
        $smt->notice('<a href="' . $smt->url('info')
        . '?i=' . $pageid . '">' . "$pageid</a>: " . print_r($hashes,1) );
        return TRUE;
    }
    $smt->error('ERROR saving hashes');
    return FALSE;
}

////////////////////
function hash_report() {
    global $smt;
    $tab = " ";
    $cr = "\n";

    print '<p><a href="?r=hash&update=1">Update Image Hashes x1</a></p>';
    if( isset($_GET['update']) && $_GET['update'] && $smt->is_positive_number($_GET['update']) ) {
        $runs = $_GET['update'];
        print '<p>UPDATING x' . $runs . '</p>';

        $medias = $smt->query_as_array('
            SELECT pageid
            FROM media
            WHERE ahash IS NULL
            OR dhash IS NULL
            OR phash IS NULL
            ORDER BY updated ASC
            LIMIT ' . $runs);
        foreach($medias as $media) {
            get_perceptual_hash( $media['pageid'] );
        }
    }


    $report = 'Image Hash Report:' . $cr . $cr;
    $medias = $smt->query_as_array('
        SELECT pageid, ahash, dhash, phash, updated
        FROM media
        WHERE ahash IS NOT NULL
        AND dhash IS NOT NULL
        and phash IS NOT NULL
        ORDER BY dhash DESC
        ');
    $report .= 'PageID     Difference Hash                  Perceptual Hash                  Average Hash                     Updated' . $cr
             . '---------- -------------------------------- -------------------------------- -------------------------------- -------------------' . $cr;
    foreach( $medias as $media ) {
        $report .= '<a target="site" href="' . $smt->url('info') . '?i=' . $media['pageid'] . '">'
        . str_pad($media['pageid'], 10, ' ') . '</a>' . $tab
        . str_pad($media['dhash'], 32, '0', STR_PAD_LEFT) . $tab
        . str_pad($media['phash'], 32, '0', STR_PAD_LEFT) . $tab
        . str_pad($media['ahash'], 32, '0', STR_PAD_LEFT) . $tab
        . $media['updated'] . $cr;
    }
    print "<pre>$report</pre>";
}


////////////////////
function skin_report() {
    global $smt;
    $report = '';

    if( isset($_GET['update']) ) {
        $limit = $_GET['update'];
        if( !$limit || !$smt->is_positive_number($limit) ) {
            $limit = 5;
        }
        $updates = $smt->query_as_array('
            SELECT pageid, updated
            FROM media
            WHERE skin IS NULL
            ORDER BY updated
            LIMIT ' . $limit);
        foreach( $updates as $update ) {
            $smt->get_media_skin_percentage( $update['pageid'] );
        }
    }

    $medias = $smt->query_as_array('
        SELECT pageid, skin, updated
        FROM media
        WHERE skin IS NOT NULL
        ORDER BY skin DESC
    ');

    foreach( $medias as $media ) {
        if( !$media['skin'] ) {
                $smt->error('skin not found in media table');
                continue;
        }
        $report .= '<br />'
        . '<a target="site" href="' . $smt->url('info') . '?i=' . $media['pageid'] . '">'
        . str_pad($media['pageid'], 10, ' ') . '</a> '
        . str_pad($media['skin'], 5, ' ') . '   '
        . $media['updated']
        ;
    }

    $report .= '</pre>';

    $header = '';
    $header .= '<p><a href="?r=skin&amp;update=5">Update Skin Percentages - Update x5 files</a></p>';
    $header .= '<pre>Skin Percentage Report @ ' . $smt->time_now() . ' UTC<br />';
    $header .= '<br />' . number_format($smt->get_image_count()) . ' media files in collection';
    $header .= '<br />' . number_format(sizeof($medias)) . ' media files analysed';
    $header .= '<br />Pageid     Skin      Updated';
    $header .= '<br />------     -----     -------------------';

    print $header . $report;

}
