<?php
/**
 * Shared Media Tagger
 * Tag
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

use Attogram\SharedMedia\Tagger\Tools;

$mediaId = isset($_GET['m']) ? $_GET['m'] : false;
$tagId = isset($_GET['t']) ? $_GET['t'] : false;

if ($tagId || !Tools::isPositiveNumber($tagId)) {
    $smt->fail404('404 Tag ID Not Found');
}

if (!$mediaId || !Tools::isPositiveNumber($mediaId)) {
    $smt->fail404('404 Media ID Not Found');
}

// Tag exists?
if (!$smt->database->queryAsBool('SELECT id FROM tag WHERE id = :tag_id LIMIT 1', [':tag_id' => $tagId])) {
    $smt->fail404('404 Tag Not Found');
}

// Media exists?
if (!$smt->database->queryAsBool(
    'SELECT pageid FROM media WHERE pageid = :media_id LIMIT 1',
    [':media_id' => $mediaId]
)
) {
    $smt->fail404('404 Media Not Found');
}

// Get user, or create new user
if (!$smt->database->getUser(1)) {
    $smt->fail404('404 User Not Found');
}

// has user already rated this file?
$addUserTag = true;
$rating = $smt->database->queryAsArray(
    'SELECT tag_id, count
    FROM user_tagging
    WHERE user_id = :user_id
    AND media_pageid = :media_id',
    [':user_id' => $smt->user_id, ':media_id' => $mediaId]
);
if ($rating) {  // existing user rating for this media file
    $oldTag = $rating[0]['tag_id'];
    $oldCount = $rating[0]['count'];
    if ($oldTag == $tagId) { // user NOT changing tag, do nothing
        goto redirect;
    }
    $smt->database->saveUserLastTagTime();
    $addUserTag = false;

    // user_tagging: Switch old tag to new tag
    $res = $smt->database->queryAsBool(
        'UPDATE user_tagging
        SET tag_id = :tag_id
        WHERE user_id = :user_id
        AND media_pageid = :media_id',
        [':tag_id' => $tagId, ':user_id' => $smt->user_id, ':media_id' => $mediaId]
    );

    // global tagging: -1 old tag
    $res = $smt->database->queryAsBool(
        'UPDATE tagging
        SET count = count - 1
        WHERE media_pageid = :media_id
        AND tag_id = :tag_id',
        [':media_id' => $mediaId, ':tag_id' => $oldTag]
    );
} // end if already rated

if ($addUserTag) {
    // user tagging: +1 new tag
    $where = ' WHERE user_id=:user_id AND tag_id=:tag_id AND media_pageid=:media_id';
    $sql = 'SELECT count FROM user_tagging' . $where;
    $bind = [':user_id' => $smt->user_id, ':tag_id' => $tagId, ':media_id' => $mediaId];
    if ($smt->database->queryAsArray($sql, $bind)) {
        $res = $smt->database->queryAsBool(
            'UPDATE user_tagging SET count = count + 1'  . $where,
            $bind
        );
    } else {
        $res = $smt->database->queryAsBool(
            'INSERT INTO user_tagging (count, tag_id, media_pageid, user_id) '
            . ' VALUES (1, :tag_id, :media_id, :user_id)',
            $bind
        );
    }
    $smt->database->saveUserLastTagTime();
}

// global tagging: +1 new tag
$where = ' WHERE tag_id=:tag_id AND media_pageid=:media_id';
$sql = 'SELECT count FROM tagging' . $where;
$bind = [':tag_id' => $tagId, ':media_id' => $mediaId];
$gtag = $smt->database->queryAsArray($sql, $bind);
if (!$gtag) {
    $res = $smt->database->queryAsBool(
        'INSERT INTO tagging (count, tag_id, media_pageid) VALUES (1, :tag_id, :media_id)',
        $bind
    );
} else {
    $res = $smt->database->queryAsBool('UPDATE tagging SET count = count + 1' . $where, $bind);
}

// get next random image
redirect:

$next = $smt->database->getRandomMedia();
if (isset($next[0]['pageid'])) {
    header('Location: ./?i=' . $next[0]['pageid']);

    return;
}

header('Location: ./');
