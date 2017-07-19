<?php
// Shared Media Tagger
// Categories

$f = __DIR__.'/smt.php';
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);

$smt = new smt();
$smt->title = $smt->get_categories_count() . ' Categories - ' . $smt->site_name;
$smt->include_header();
$smt->include_menu();

if( isset($_GET['s']) && $_GET['s'] ) {
    $sql = '
    SELECT c.id, c.name,
        c.files AS commons_count,
        count(c2m.media_pageid) AS local_count
    FROM category AS c
    LEFT OUTER JOIN category2media AS c2m ON c2m.category_id = c.id
    WHERE c.name LIKE :search
    GROUP BY c.name
    ORDER BY c.name';
    $bind = array(':search'=>'%' . $_GET['s']. '%');
} else {
    $sql = '
    SELECT c.id, c.name,
        c.files AS commons_count,
        count(c2m.media_pageid) AS local_count
    FROM category AS c
    LEFT OUTER JOIN category2media AS c2m ON c2m.category_id = c.id
    GROUP BY c.name
    ORDER BY c.name';
    $bind = array();

}

$cats = $smt->query_as_array($sql, $bind);
if( !is_array($cats) ) {
    $cats = array();
}

$active = $disabled = array();
foreach( $cats as $cat ) {
    if( $cat['local_count'] > 0 ) {
        $active[] = $cat;
    } else {
        $disabled[] = $cat;
    }
}
unset($cats);

?>
<div class="box white">

<div class="center">
<form method="GET"><input type="text" name="s" value="<?php
    isset($_GET['s']) ? print htmlentities(urldecode($_GET['s'])) : print '';
 ?>" size="20"><input type="submit" value="search"></form>
</div>

<br />
<?php print_category_table( $smt, $active); ?>
<br /><br />
<?php
    // print_disabled_category_table( $smt, $disabled);
    print '<p>' . sizeof($disabled) . ' categories in curation que</p>';
?>

</div><?php
$smt->include_footer();
exit;


/////////////////////////////////////////////////////////////
function print_disabled_category_table( $smt, $cats ) {

    print '
<table border="1">
<tr style="background-color:lightgrey;">
 <td style="padding:4px;"><b>' . sizeof($cats) . '</b> Categories in curation que:</td>
</tr>
<tr>
<td>';

    foreach( $cats as $c ) {
        $commons_url = 'https://commons.wikimedia.org/wiki/' . $smt->category_urlencode($c['name']);
        print '<a href="' . $commons_url . '" target="commons">'
            . $smt->strip_prefix($c['name']) . '</a><br />';
    }

    print '</td></tr></table>';
}


/////////////////////////////////////////////////////////////
function print_category_table( $smt, $cats ) {

?>
<table border="1">
<tr style="background-color:lightgrey; font-size:80%;">
<td style="padding:4px;">#files</td>
<td style="padding:4px;"><b><?php print sizeof($cats); ?></b> Categories</td>
<?php
foreach( $smt->get_tags() as $tag ) {
    print '<td style="font-size:110%;" class="tag' . $tag['id'] . ' center">'
        //. $tag['display_name']
        . $tag['name']
        . '</td>';
}
?>
<td style="padding:4px;">#rates</td>
</tr>
<?php
    foreach( $cats as $c ) {
        $local_url = $smt->url('category')
            . '?c=' . $smt->category_urlencode( $smt->strip_prefix( $c['name'] ));
        //$commons_url = 'https://commons.wikimedia.org/wiki/' . $smt->category_urlencode($c['name']);
        print '<tr>';

        print '<td class="center"><a href="' . $local_url . '">' . $c['local_count'] . '</a></td>';

        print '<td style="font-weight:bold;"><a href="' . $local_url . '">' 
		. $smt->strip_prefix($c['name']) . '</a></td>';
		


        $reviews = array();
        foreach( $smt->get_tags() as $tag ) {
            $reviews[ $tag['id'] ] = '<td class="tag' . $tag['id'] . '">&nbsp;</td>';
        }

        $crevs = $smt->get_db_reviews_per_category($c['id']);

        $count = 0;
        foreach( $crevs as $r ) {
            //$smt->notice($r);
            $reviews[ $r['id'] ] = '<td class="tag' . $r['id']
                . '" style="white-space:nowrap; font-size:80%; text-align:right;">+'
                . $r['count'] . ' ' . $r['name'] . '</td>';
            $count += $r['count'];
        }


        print implode('', $reviews);
        print '<td class="right">' . $count . '</td>';
        print '</tr>';
    }

    print '</table>';
}
