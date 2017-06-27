<?php
// Shared Media File
// Tag

$media_id = isset($_GET['m']) ? $_GET['m'] : FALSE;
$tag_id = isset($_GET['t']) ? $_GET['t'] : FALSE;

if( !$media_id | !$tag_id ) {
	print 'INVALID TAG';
	exit;
}

$f = __DIR__.'/smt.php'; 
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);

$smt = new smt('Tag');

$where = 'WHERE tag_id=:tag_id AND media_pageid=:media_id';
$sql = 'SELECT count FROM tagging ' . $where;
$bind = array(':tag_id'=>$tag_id, ':media_id'=>$media_id);
	
$r = $smt->query_as_array( $sql, $bind );
if( !$r ) {
	$isql = 'INSERT INTO tagging ( count, tag_id, media_pageid ) VALUES ( 1, :tag_id, :media_id )';
	$ir = $smt->query_as_bool( $isql, $bind );
} else {
	$usql = 'UPDATE tagging SET count = count + 1 ' . $where;
	$ur = $smt->query_as_bool( $usql, $bind );
}


// get next random image
$next = $smt->get_random_media();
if( isset($next[0]['pageid']) ) {
	header('Location: ./?i=' . $next[0]['pageid']);
	exit;
}

header('Location: ./');
exit;
