<?php
// Shared Media Tagger
// Reviews

$f = __DIR__.'/smt.php';
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);

$smt = new smt();

$me = $smt->url('reviews');
$tags = $smt->get_tags();

//$smt->title = $smt->get_total_review_count() . ' Reviews - ' . $smt->site_name;
$smt->title = 'Reviews - ' . $smt->site_name;
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );

$order = isset($_GET['o']) ? $smt->category_urldecode($_GET['o']) : '';

print '<div class="box white">';
print 'Reviews:<br />';

foreach( $tags as $tag ) {
    $tag_count = $smt->get_tagging_count( $tag['id'] );
    print '<span class="reviewbutton tag' . $tag['position'] . '">'
    . '<a href="' . $me . '?o=reviews.' . $smt->category_urlencode($tag['name']) . '">'
    . '+' . $tag_count . ' ' . $tag['name'] . '</a></span>';
}
print '<span class="reviewbutton"><a href="' . $me . '?o=total.reviews">+'
. $smt->get_tagging_count() . ' Total</a></span>'
. '<hr />';

// Reviews per tag
if( (preg_match('/^reviews\.(.*)/', $order, $matches)) === 1 ) {
    $tag_name = $matches[1];
    $tag_id = $smt->get_tag_id_by_name($tag_name);
    if( !$tag_id ) {
        $smt->notice('Invalid Review Name');
        $order = '';
    } else {
        $order = 'PER.TAG';
    }
    //$smt->notice("PREG: tag_name=$tag_name tag_id=$tag_id matches=" . print_r($matches,1));

}


$limit = 100;

switch( $order ) {

    default:
        print '<p>Please choose a report above.</p></div>';
        $smt->include_footer();
        exit;


    case 'PER.TAG':
        $tags = $smt->get_tags();
        $order_desc = $tag_name; // . ' reviews';
        $sql = '
        SELECT t.count, t.tag_id, m.*
        FROM tagging AS t, media AS m
        WHERE t.media_pageid = m.pageid AND t.tag_id = :tag_id
        ORDER BY t.count DESC LIMIT ' . $limit;
        $bind = array(':tag_id'=>$tag_id);
        break;


    case 'total.reviews':
        $order_desc = 'Total # of reviews';
        $sql = '
        SELECT SUM(t.count) AS tcount, t.tag_id, m.*
        FROM tagging AS t, media AS m
        WHERE t.media_pageid = m.pageid
        GROUP BY m.pageid
        ORDER BY tcount DESC
        LIMIT ' . $limit;
        $bind = array();
        break;
}

$x = $smt->query_as_array($sql, $bind);
if( !is_array($x) ) { $x = array(); }

//$smt->notice($x);
?>
<p><b><?php print $order_desc; ?></b>: <?php print sizeof($x); ?> files reviewed.</p>

<?php
foreach( $x as $media ) {
    print $smt->display_thumbnail_box($media);
}
?>
</div>
<?php
$smt->include_footer();
