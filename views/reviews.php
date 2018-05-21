<?php
/**
 * Shared Media Tagger
 * Reviews
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTagger $smt
 */

$me = $smt->url('reviews');
$tags = $smt->getTags();

$smt->title = 'Reviews - ' . $smt->siteName;
$smt->includeHeader();
$smt->includeMediumMenu();

$order = isset($_GET['o']) ? $smt->categoryUrldecode($_GET['o']) : '';

print '<div class="box white">Reviews:<br />';

foreach ($tags as $tag) {
    $tagCount = $smt->getTaggingCount($tag['id']);
    print '<span class="reviewbutton tag' . $tag['position'] . '">'
    . '<a href="' . $me . '?o=reviews.' . $smt->categoryUrlencode($tag['name']) . '">'
    . '+' . $tagCount . ' ' . $tag['name'] . '</a></span>';
}
print '<span class="reviewbutton"><a href="' . $me . '?o=total.reviews">+'
    . $smt->getTaggingCount() . ' Total</a></span><hr />';

// Reviews per tag
$tagName = null;
if ((preg_match('/^reviews\.(.*)/', $order, $matches)) === 1) {
    $tagName = $matches[1];
    $tagId = $smt->getTagIdByName($tagName);
    if (!$tagId) {
        $smt->notice('Invalid Review Name');
        $order = '';
    } else {
        $order = 'PER.TAG';
    }
}

$limit = 100;  // @TODO TMP DEV

switch ($order) {
    default:
        print '<p>Please choose a report above.</p></div>';
        $smt->includeFooter();
        exit;

    case 'PER.TAG':
        $tags = $smt->getTags();
        $orderDesc = $tagName; // . ' reviews';
        $sql = '
        SELECT t.count, t.tag_id, m.*
        FROM tagging AS t, media AS m
        WHERE t.media_pageid = m.pageid AND t.tag_id = :tag_id
        ORDER BY t.count DESC LIMIT ' . $limit;
        $bind = [':tag_id'=>$tagId];
        break;

    case 'total.reviews':
        $orderDesc = 'Total # of reviews';
        $sql = '
        SELECT SUM(t.count) AS tcount, t.tag_id, m.*
        FROM tagging AS t, media AS m
        WHERE t.media_pageid = m.pageid
        GROUP BY m.pageid
        ORDER BY tcount DESC
        LIMIT ' . $limit;
        $bind = [];
        break;
}

$rates = $smt->queryAsArray($sql, $bind);
if (!is_array($rates)) {
    $rates = [];
}

print '<p><b>' . $orderDesc . '</b>: ' . sizeof($rates) . ' files reviewed.</p>';

foreach ($rates as $media) {
    print $smt->displayThumbnailBox($media);
}

print '</div>';
$smt->includeFooter();
