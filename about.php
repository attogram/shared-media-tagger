<?php
// Shared Media Tagger
// About

$class = __DIR__.'/smt.php'; 
if(!file_exists($class)||!is_readable($class)){ print 'Site down for maintenance'; exit; } require_once($class);
$smt = new smt('About');

$a = $smt->query_as_array('SELECT name, about FROM site WHERE id = :site', array(':site'=>$smt->site));
//$smt->notice($a);
$about = @$a[0]['about'];
$name = @$a[0]['name'];
	
if( $about == '' ) {
	$about = '<p>Site information coming soon...</p>';
} 
if( $name == '' ) {
	$name = '<p>My Site</p>';
} 

$smt->title = 'About ' . $name;

$smt->include_header();
$smt->include_menu();

print '
<div class="box white" style="padding-top:10px;">
<h1>' . $name. '</h1>
<p>' . $about . '</p>
<hr />
<h2>How to use this site:</h2>
<dl>
	<dt><h3><a href="' . $smt->url('home') . '">Review a file</a><h3></dt>
	<dd>....</dd>
	
	<dt><h3><a href="' . $smt->url('categories') . '">Browse Categories</a><h3></dt>
	<dd>...</dd>

	<dt><h3><a href="' . $smt->url('reviews') . '">Browse Reviews</a><h3></dt>
	<dd>...</dd>	

	<dt><h3><a href="' . $smt->url('contact') . '">Contact</a><h3></dt>
	<dd>...</dd>	
	
</dl>

<hr /><h2>Adding your images, audio and video:</h2>
<dl>
	<dt><h3>Allowed Licenses</h3></dt>
	<dd>....</dd>
	<dt><h3>Uploading to Wikimedia Commons</h3></dt>
	<dd>....</dd>
	<dt><h3>Categorizing</h3></dt>
	<dd>....</dd>
	<dt><h3><a href="' . $smt->url('contact') . '">Contact</a> site admins</h3></dt>
	<dd>...</dd>
</dl>

<hr ><h2>About Shared Media Tagger:</h2>
<p>...</p>
';


print '</div>';
$smt->include_footer();


/*

<p>This site allows you to 
 <a href=".">review</a> a 
 <a href="./categories.php">curated set</a>
  of images and media files from the
 <a href="https://commons.wikimedia.org/" target="commons">Wikimedia Commons</a> archives.<p>

*/