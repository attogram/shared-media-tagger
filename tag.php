<?php
// Shared Media File
// Tag


$init = __DIR__.'/smt.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt('Tag');

$media_id = isset($_GET['m']) ? $_GET['m'] : FALSE;

$tag_id = isset($_GET['t']) ? $_GET['t'] : FALSE;

if( !$media_id | !$tag_id ) {
    $smt->fail404('404 Tag Not Found');
}

// TODO - check here if user already rated this media - then delete old rating, add new rating


$where = 'WHERE tag_id=:tag_id AND media_pageid=:media_id';
$sql = 'SELECT count FROM tagging ' . $where;
$bind = array(':tag_id'=>$tag_id, ':media_id'=>$media_id);

// Update global tagging data    
$gtag = $smt->query_as_array( $sql, $bind );
if( !$gtag ) {
    $smt->query_as_bool( 'INSERT INTO tagging ( count, tag_id, media_pageid ) VALUES ( 1, :tag_id, :media_id )', $bind);
} else {
    $smt->query_as_bool( 'UPDATE tagging SET count = count + 1 ' . $where, $bind );
}

// Update user tagging data
$where = 'WHERE user_id=:user_id AND tag_id=:tag_id AND media_pageid=:media_id';
$sql = 'SELECT count FROM user_tagging ' . $where;
$bind[':user_id'] = $smt->user_id;
$utag = $smt->query_as_array( $sql, $bind );
if( !$utag ) {
    $smt->query_as_bool( 'INSERT INTO user_tagging ( count, tag_id, media_pageid, user_id ) '
        . ' VALUES ( 1, :tag_id, :media_id, :user_id )', $bind);
} else {
    $smt->query_as_bool( 'UPDATE user_tagging SET count = count + 1 ' . $where, $bind );
}


// get next random image
$next = $smt->get_random_media();
if( isset($next[0]['pageid']) ) {
    header('Location: ./?i=' . $next[0]['pageid']);
    exit;
}

header('Location: ./');
exit;
