<?php
// Shared Media Tagger
// Category Admin

if( function_exists('set_time_limit') ) { set_time_limit( 250 ); }

$f = __DIR__.'/../smt.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$f = __DIR__.'/smt-admin.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$smt = new smt_admin('Category Admin');
$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white">';

///////////////////////////////////////////////////////////////////////////////

if( isset($_GET['i']) && $_GET['i'] ) {
	import_images_from_category($smt);
	$this->vacuum();
}

if( isset($_POST['cats']) && $_POST['cats'] ) {
	import_categories($smt);
	$this->vacuum();
}

if( isset($_GET['c']) && $_GET['c'] ) {
	get_category_info($smt);
}

if( isset($_GET['d']) && $_GET['d'] ) {
	delete_category($smt);
	$this->vacuum();
}

if( isset($_GET['s']) && $_GET['s'] ) { 
	get_search_results($smt);
}

if( isset($_GET['sc']) && $_GET['sc'] ) { 
	$smt->get_subcats( $smt->category_urldecode($_GET['sc']) );
}

if( isset($_GET['g']) && $_GET['g']=='all' ) {
	$toget = array();
	$cats = $smt->query_as_array('SELECT name, pageid, files, subcats FROM category ORDER BY name');
	foreach( $cats as $c ) {
		if( $c['subcats'] != '' && $c['files'] != '' && $c['pageid'] != '' ) {
			continue;
		}
		if( sizeof($toget) == 50 ) {
			break;
		}
		$toget[] = $c['name'];
	}
	$_GET['c'] = implode('|',$toget);
	get_category_info($smt);
}

	
///////////////////////////////////////////////////////////////////////////////


if( isset($_GET['sca']) && $_GET['sca']=='all' ) {
	$sql = 'SELECT id, name, pageid, files, subcats FROM category WHERE subcats > 0 ORDER BY name'; 
	$smt->notice('SHOWING only categories with subcategories');

} elseif( isset($_GET['wf']) ) {
	$sql = 'SELECT id, name, pageid, files, subcats FROM category WHERE files > 0 ORDER BY name';
	$smt->notice('SHOWING only categories with files');
	
} elseif( isset($_POST['s']) ) {
	$sql = "SELECT id, name, pageid, files, subcats FROM category WHERE name LIKE :search ORDER BY name";
	$bind = array(':search'=>'%' . $_POST['s']. '%');
	$smt->notice('SHOWING only categories with search text: ' . $_POST['s'] );
	
} else {
	$sql = 'SELECT id, name, pageid, files, subcats FROM category ORDER BY name';

}
if( !isset($bind) ) { $bind = array(); }
$cats = $smt->query_as_array($sql, $bind);

if( !is_array($cats) ) { $cats = array(); }



print '<p><form action="" method="GET">'
. '<input id="s" name="s" type="text" size="35" value=""/>'
. '<input type="submit" value="   Find Categories on Commons  " /></form>'

. ' &nbsp; &nbsp; &nbsp; '
. ' <a href="./sqladmin.php?table=category&action=row_create" target="sqlite">'
. ' [Manually add category]</a>'
. ' &nbsp; &nbsp; &nbsp; '
. '<a href="./' . basename(__FILE__) . '?g=all">[Import Category Info]</a>'

. '</p>';


print '<table border="1">'
. '<tr style="background-color:lightblue;font-style:italic;">'
. '<td><form method="POST"><input type="text" name="s" value="" size="20"><input type="submit" value="search"></form>
</td>'
. '<td><small>Loc<br />files</small></td>'
. '<td><small><a href="./'. basename(__FILE__) . '?wf=1">Com<br/>files</a></small></td>'
. '<td><small><a href="./' . basename(__FILE__) . '?sca=all">Sub<br />cats</a></small></td>'
. '<td>view</td>'
. '<td><a href="./' . basename(__FILE__) . '?g=all">info</a></td>'
. '<td>import</td>'
. '<td>Clear</td>'
. '<td>Delete</td>'
. '</tr>'
;

reset($cats);
$common_files_count = $local_files_count = 0;
foreach( $cats as $c ) {
	
	$common_files_count += $c['files'];
	
	print '<tr>'
	. '<td><b><a href="' . $smt->url('category') . '?c=' 
	. $smt->category_urlencode($smt->strip_prefix($c['name']))
	. '">' . $smt->strip_prefix($c['name']) . '</a></b></td>';
	
	$local_files = '';
	$lfsql = '
		SELECT count(category_id) AS count 
		FROM category2media 
		WHERE category_id = :category_id';
	$lfbind = array(':category_id'=>$c['id']);
	$lf = $smt->query_as_array($lfsql, $lfbind);	
	if( !$lf || !isset($lf[0]['count']) || !$lf[0]['count'] ) {
		$local_files = '<span style="color:#ccc;">0</span>';
	} else {
		$local_files = $lf[0]['count'];
		$local_files_count += $lf[0]['count'];
	}


	if( $local_files != $c['files'] ) {
		$alert_td = ' style="background-color:lightsalmon;"';
	} else {
		$alert_td = '';
	}
	print ''
	. '<td' . $alert_td . '>' . $local_files . '</td>'
	. '<td>' . ($c['files'] ? $c['files'] : '<span style="color:#ccc;">0</span>') . '</td>'
	;
	if( $c['subcats'] > 0 ) {
		$subcatslink = '<a href="./' . basename(__FILE__) . '?sc=' . $smt->category_urlencode($c['name']) . '"">+' 
		. $c['subcats'] . '</a>';
	} else {
		$subcatslink = '';
		if( $c['pageid'] > 0 ) {
			$subcatslink = '<span style="color:#ccc;">0</span>';
		}
	}
	print '<td>' . $subcatslink . '</td>';

	print ''
	. '<td><a target="commons" href="https://commons.wikimedia.org/wiki/' . $smt->category_urlencode($c['name']) . '">view</a></td>'
	. '<td><a href="./' . basename(__FILE__) . '?c=' . $smt->category_urlencode($c['name']) . '">info</a></td>'
	. '<td><a href="./' . basename(__FILE__) . '?i=' . $smt->category_urlencode($c['name']) . '">import</a></td>'
	. '<td><a href="./media.php?dc=' . $smt->category_urlencode($c['name']) . '">Clear</a></td>'
	. '<td><a href="./' . basename(__FILE__) . '?d=' . urlencode($c['id']) . '">Delete</a></td>'
	. '</tr>'
	;
}
print '</table>';


print '<br /><b>' . $local_files_count . '</b> Files Under Review';
print '<br /><b>' . $common_files_count . '</b> Total Files on Commons';

///////////////////////////////////////////////////////////////////////////////

print '</div>';
$smt->include_footer();



///////////////////////////////////////////////////////////////////////////////


///////////////////////////////////////////////////////////////////////////////
function delete_category($smt) {
	$category_id = $_GET['d'];
	$smt->notice("Deleting category ID #$category_id");
	$sql = array(
		'DELETE FROM category WHERE id = :category_id',
		'DELETE FROM category2media WHERE category_id = :category_id',
	);
	$r = array();
	foreach( $sql as $s ) {
		if( $smt->query_as_bool($s, array(':category_id'=>$category_id) ) ) {
			$r[] = 'Delete OK  : ' . $s;
		} else {
			$r[] = 'Delete FAIL: ' . $s;
		}
	}
	$smt->notice($r);
	return;
}

///////////////////////////////////////////////////////////////////////////////
function get_search_results($smt) {
	$search = urldecode($_GET['s']);
	$cats = $smt->find_categories($search);

	if( !$cats || !is_array($cats) ) {
		$smt->notice('Error: no categories found');
		exit;
	}
	print '<p>Searched "' . $search . '": showing <b>' . sizeof($cats) . '</b> of <b>' . $smt->totalhits . '</b> categories</p>';
	print '
	<script type="text/javascript" language="javascript">// <![CDATA[
	function checkAll(formname, checktoggle)
	{
	  var checkboxes = new Array(); 
	  checkboxes = document[formname].getElementsByTagName(\'input\');
	 
	  for (var i=0; i<checkboxes.length; i++)  {
		if (checkboxes[i].type == \'checkbox\')   {
		  checkboxes[i].checked = checktoggle;
		}
	  }
	}
	// ]]></script>
	<a onclick="javascript:checkAll(\'cats\', true);" href="javascript:void();">check all</a>
	<a onclick="javascript:checkAll(\'cats\', false);" href="javascript:void();">uncheck all</a>
	';

	print '<form action="" name="cats" method="POST">'
	. '<input type="submit" value="  save to database  "><br /><br />';

	while( list(,$x) = each($cats) ) {
		//print '<pre>' . print_r($x,1) . '<pre>';
		print '<input type="checkbox" checked="checked" name="cats[]" value="' . urlencode($x['title']) . '"><strong>' 
		. $x['title'] 
		. '</strong><small> ' 
		. '<a target="commons" href="https://commons.wikimedia.org/wiki/' 
			. $smt->category_urlencode($x['title']) . '">(view)</a> '
		. ' (' . $x['snippet'] . ')'
		. ' (' . $x['size'] . ')</small><br />';
		//print '<input type="hidden" name="' . urlencode($x['title']) . '" value="' . urlencode($x['snippet']) . '" />';
	}
	print '</form>';
	print '</div>';
	$smt->include_footer();
	exit;	
}

///////////////////////////////////////////////////////////////////////////////
function import_categories($smt) {
	print '<p>Saving categories to database:</p>';
	foreach( $_POST['cats'] as $p ) {
		$name = $smt->category_urldecode($p);
		$r = $smt->insert_category( $name );
	}
	print '</div>';
	$smt->include_footer();
	exit;
}

///////////////////////////////////////////////////////////////////////////////
function get_category_info($smt) {
	$c = urldecode($_GET['c']);
	//print '<p>Category Info: <b>' . $c . '</b></p>';
	$r = $smt->get_category_info($c);	
	if( !$r ) { 
		$smt->error('failed to get category info'); 
		return; 
	}
	foreach( $r as $c ) {
		$title = $c['title'];
		$pageid = $c['pageid'];
		$files = $c['categoryinfo']['files'];
		$subcats = $c['categoryinfo']['subcats'];
		$x = $smt->query_as_bool(
			'UPDATE category 
			SET pageid = :pageid, files = :files, subcats = :subcats
			WHERE name = :name',
			array( ':pageid'=>$pageid, ':files'=>$files, ':subcats'=>$subcats, ':name'=>$title)
		);
		if( $x ) {
			//$smt->notice("::get_category_info: UPDATED category: title:$title pageid:$pageid files:$files subcats:$subcats");
		} else {
			$smt->notice("::get_category_info: error updateing category: title:$title pageid:$pageid files:$files subcats:$subcats");
		}
	}

}
	
///////////////////////////////////////////////////////////////////////////////
function import_images_from_category($smt) {
	$category_name = $smt->category_urldecode($_GET['i']);
	$category_name = $smt->strip_prefix($category_name);
	
	print 'Importing media from <b><a href="' . $smt->url('category') . '?c='
	. $smt->category_urlencode($category_name)
	. '">'
	. htmlentities($category_name)
	. '</a></b>';
	$r = $smt->get_media_from_category( $category_name );
	print '</div>';
	$smt->include_footer();
	exit;	
}