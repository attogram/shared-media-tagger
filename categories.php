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


if( $search ) {
	$all_categories = $smt->query_as_array(
		'SELECT id, name FROM category WHERE name LIKE :search',
		array(':search'=>'%' . $search . '%')
	);
} else {
	$all_categories = $smt->query_as_array('SELECT id, name FROM category');
}


$active = $hidden = array();
foreach( $all_categories as $cat ) {
	if( $smt->is_hidden_category($cat['name']) ) {
		$hidden[$cat['id']] = $cat;
		continue;
	}
    $active[$cat['id']] = $cat;
}
unset($all_categories);
$sizeof_active = sizeof($active);
$sizeof_hidden = sizeof($hidden);

switch( $mode ) {
	default:
	case 'active': $categories = $active; break;
	case 'hidden': $categories = $hidden; break;
}
unset($active);
unset($hidden);

$ids = array();
foreach( $categories as $cat ) {
	$ids[] = $cat['id'];
}
$sql = '
    SELECT c2m.category_id, count(c2m.media_pageid) AS local_count
    FROM category2media AS c2m	
	WHERE c2m.category_id IN ( ' . implode($ids, ', ') . ')
    GROUP BY c2m.category_id
	ORDER BY local_count DESC';
unset($ids);
$bind = array();	

$local_count = $smt->query_as_array($sql, $bind);

foreach( $local_count AS $cat ) {
	$categories[$cat['category_id']]['local_count'] = $cat['local_count'];
}
unset($local_count);

aasort($categories, 'local_count');
///////////////////////////////////////////////////
function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) { $sorter[$ii]=@$va[$key]; }
    asort($sorter);
    foreach ($sorter as $ii => $va) { $ret[$ii]=$array[$ii]; }
    $array=array_reverse($ret);
}
///////////////////////////////////////////////////

switch( $mode ) {
	case 'active':
	default:
		$smt->title = sizeof($categories) . ' Categories - ' . $smt->site_name;
		break;
	case 'hidden';
		$smt->title = sizeof($categories) . ' Technical Categories - ' . $smt->site_name;
		break;
}
/////
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

$full_size = sizeof($categories);
if( $full_size > 1000 ) {
	$categories = array_slice($categories, 0, 1000);
	print '<p class="center">Showing ' . sizeof($categories) . ' of ' . $full_size . ' Categories</p>';
}

switch( $mode ) {
	case 'active':
	default:	
		print '<p class="center" style="padding:10px;">' . $sizeof_active . ' Active Categories</p>';
		print_category_table( $smt, $categories ); 
		print '<p class="center" style="padding:20px;"><a href="' 
		. $smt->url('categories') . '?h=1">View ' 
		. $sizeof_hidden . ' Technical Categories</a></p>';
		break;
	case 'hidden':
		print '<p class="center" style="padding:10px;"><a href="' 
		. $smt->url('categories') . '">View ' . $sizeof_active 
		. ' Active Categories</a></p>';
		print '<p class="center" style="padding:10px;">' . $sizeof_hidden 
		. ' Technical Categories</p>';
		print_category_table( $smt, $categories ); 
		break;
}
?>


<br /><br />
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
            . '?c=' . $smt->category_urlencode( $smt->strip_prefix( @$c['name'] ));
        //$commons_url = 'https://commons.wikimedia.org/wiki/' . $smt->category_urlencode($c['name']);
        print '<tr>';

        print '<td class="right"><a href="' . $local_url . '">' . @$c['local_count'] . '</a></td>';

        print '<td style="padding:0 0 0 10px; font-weight:bold;"><a href="' . $local_url . '">' 
		. $smt->strip_prefix(@$c['name']) . '</a></td>';
		


        $reviews = array();
        foreach( $smt->get_tags() as $tag ) {
            $reviews[ $tag['id'] ] = '<td class="tag' . $tag['id'] . '">&nbsp;</td>';
        }

        $crevs = $smt->get_db_reviews_per_category(@$c['id']);

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
