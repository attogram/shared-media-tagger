<?php
// Shared Media Tagger
// Categories

$init = __DIR__.'/smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$search = FALSE;
if( isset($_GET['s']) && $_GET['s'] ) {
	$search = $_GET['s'];
}

$mode = 'active';
if( isset($_GET['h']) && $_GET['h'] ) {
	$mode = 'hidden';
}

$smt = new smt();


//$order_by = 'ORDER BY c.name';
$order_by = 'ORDER BY local_count DESC';

if( $search ) {
    $sql = '
    SELECT c.id, c.name,
        c.files AS commons_count,
        count(c2m.media_pageid) AS local_count
    FROM category AS c
    LEFT OUTER JOIN category2media AS c2m ON c2m.category_id = c.id
    WHERE c.name LIKE :search
    GROUP BY c.name
    ' . $order_by;
    $bind = array(':search'=>'%' . $search . '%');
} else {
    $sql = '
    SELECT c.id, c.name,
        c.files AS commons_count,
        count(c2m.media_pageid) AS local_count
    FROM category AS c
    LEFT OUTER JOIN category2media AS c2m ON c2m.category_id = c.id
    GROUP BY c.name
    ' . $order_by;
    $bind = array();
}

$cats = $smt->query_as_array($sql, $bind);
if( !is_array($cats) ) {
    $cats = array();
}

$active = $disabled = $hidden = array();
foreach( $cats as $cat ) {
    if( $cat['local_count'] == 0 ) {
        $disabled[] = $cat;
		continue;
    } 
	if( $smt->is_hidden_category($cat['name']) ) {
		$hidden[] = $cat;
		continue;
	}
    $active[] = $cat;
}
unset($cats);

switch( $mode ) {
	case 'active':
	default:
		$smt->title = sizeof($active) . ' Categories - ' . $smt->site_name;
		break;
	case 'hidden';
		$smt->title = sizeof($hidden) . ' Technical Categories - ' . $smt->site_name;
		break;
}

$smt->include_header();
$smt->include_menu();
?>
<div class="box white">
<div class="center">
<form method="GET">
<?php if( $mode == 'hidden' ) { print '<input type="hidden" name="h" value="1">'; } ?>
<input type="text" name="s" value="<?php $search ? print htmlentities(urldecode($search)) : print ''; ?>" size="20">
<input type="submit" value=" Search Categories ">
</form>
</div>
<br />

<?php 
switch( $mode ) {
	case 'active':
	default:	
		print '<p class="center" style="padding:10px;">' . sizeof($active) . ' Active Categories</p>';
		print_category_table( $smt, $active); 
		print '<p class="center" style="padding:20px;"><a href="' . $smt->url('categories') . '?h=1">View ' . sizeof($hidden) . ' Technical Categories</a></p>';
		break;
	case 'hidden':
		print '<p class="center" style="padding:10px;"><a href="' . $smt->url('categories') . '">View ' . sizeof($active) . ' Active Categories</a></p>';
		print '<p class="center" style="padding:10px;">' . sizeof($hidden) . ' Technical Categories</p>';
		print_category_table( $smt, $hidden); 
		break;
}
?>


<br /><br />
<p class="center"><?php print sizeof($disabled) . ' categories in curation que'; ?></p>
</div>
<?php
$smt->include_footer();



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

        print '<td class="right"><a href="' . $local_url . '">' . $c['local_count'] . '</a></td>';

        print '<td style="padding:0 0 0 10px; font-weight:bold;"><a href="' . $local_url . '">' 
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
