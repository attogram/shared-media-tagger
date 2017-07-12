<?php
// Shared Media File
// Tag

$init = __DIR__.'/smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt('Tag');

$smt->notice = FALSE;

$media_id = isset($_GET['m']) ? $_GET['m'] : FALSE;

$tag_id = isset($_GET['t']) ? $_GET['t'] : FALSE;

if( !$tag_id || !$smt->is_positive_number($tag_id) ) {
    $smt->fail404('404 Tag ID Not Found');
}

if( !$media_id || !$smt->is_positive_number($media_id) ) {
    $smt->fail404('404 Media ID Not Found');
}

// Tag exists?
if( !$smt->query_as_bool('SELECT id FROM tag WHERE id = :tag_id LIMIT 1', array(':tag_id'=>$tag_id)) ) {
    $smt->fail404('404 Tag Not Found');
}

// Media exists?
if( !$smt->query_as_bool('SELECT pageid FROM media WHERE pageid = :media_id LIMIT 1', array(':media_id'=>$media_id)) ) {
    $smt->fail404('404 Media Not Found');
}

// Get user, or create new user
if( !$smt->get_user(1) ) {
    $smt->fail404('404 User Not Found');
}

// Update user last time
$res = $smt->query_as_bool(
	'UPDATE user SET last = :last WHERE id = :user_id',
	array(':user_id'=>$smt->user_id, ':last'=>gmdate('Y-m-d H:i:s'))
);

// has user already rated this file?
$add_user_tag = TRUE;
$rating = $smt->query_as_array(
    'SELECT tag_id, count
    FROM user_tagging
    WHERE user_id = :user_id
    AND media_pageid = :media_id',
    array(':user_id'=>$smt->user_id, ':media_id'=>$media_id)
);
if( $rating ) {  // existing user rating for this media file

    $old_tag = $rating[0]['tag_id'];
    $old_count = $rating[0]['count'];

    //$smt->debug('2NDRATE: user:' . $smt->user_id . ' new:tag:'.$tag_id.' old_tag:'.$old_tag.' old_count:'.$old_count);

    if( $old_tag == $tag_id ) { // user NOT changing tag, do nothing
        goto redirect;
    }

    // user_tagging: Switch old tag to new tag
    $res = $smt->query_as_bool(
        'UPDATE user_tagging
        SET tag_id = :tag_id
        WHERE user_id = :user_id
        AND media_pageid = :media_id',
        array(':tag_id'=>$tag_id, ':user_id'=>$smt->user_id, ':media_id'=>$media_id)
    );
    $add_user_tag = FALSE;
    //$smt->debug('2NDRATE: CHANGE: USER_TAGGING: old_tag:'.$old_tag. '  new_tag:'.$tag_id.'   result='.$res);

    // global tagging: -1 old tag
    $res = $smt->query_as_bool(
        'UPDATE tagging
        SET count = count - 1
        WHERE media_pageid = :media_id
        AND tag_id = :tag_id',
        array(':media_id'=>$media_id, ':tag_id'=>$old_tag)
    );
    //$smt->debug('2NDRATE: TAGGING: -1 old_tag:'.$old_tag.'   result='.$res);

} // end if already rated


if( $add_user_tag ) {
    // user tagging: +1 new tag
    $where = 'WHERE user_id=:user_id AND tag_id=:tag_id AND media_pageid=:media_id';
    $sql = 'SELECT count FROM user_tagging ' . $where;
    $bind = array(':user_id'=>$smt->user_id, ':tag_id'=>$tag_id, ':media_id'=>$media_id);
    if( $smt->query_as_array($sql, $bind) ) {
        $res = $smt->query_as_bool( 'UPDATE user_tagging SET count = count + 1 ' . $where, $bind );
        //$smt->debug('user tagging UPDATED. result:'.$res);
    } else {
        $res = $smt->query_as_bool( 'INSERT INTO user_tagging ( count, tag_id, media_pageid, user_id ) '
            . ' VALUES ( 1, :tag_id, :media_id, :user_id )', $bind);
        //$smt->debug('user tagging INSERTED. result:'.$res);
    }
}

// global tagging: +1 new tag
$where = 'WHERE tag_id=:tag_id AND media_pageid=:media_id';
$sql = 'SELECT count FROM tagging ' . $where;
$bind = array(':tag_id'=>$tag_id, ':media_id'=>$media_id);$gtag = $smt->query_as_array( $sql, $bind );
if( !$gtag ) {
    $res = $smt->query_as_bool( 'INSERT INTO tagging ( count, tag_id, media_pageid ) VALUES ( 1, :tag_id, :media_id )', $bind);
    //$smt->debug('global tagging INSERTED. result:'.$res);
} else {
    $res = $smt->query_as_bool( 'UPDATE tagging SET count = count + 1 ' . $where, $bind );
    //$smt->debug('global tagging UPDATED. result:'.$res);
}


// get next random image
redirect:

$next = $smt->get_random_media();
if( isset($next[0]['pageid']) ) {
    header('Location: ./?i=' . $next[0]['pageid']);
    return;
}

header('Location: ./');
