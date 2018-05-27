<?php
/**
 * Shared Media Tagger
 * About page
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 */

use Attogram\SharedMedia\Tagger\Tools;

$site = $smt->database->queryAsArray('SELECT name, about FROM site WHERE id = 1');
$about = !empty($site[0]['about']) ? $site[0]['about'] : 'Welcome';
$name = !empty($site[0]['name']) ? $site[0]['name'] : 'Shared Media Tagger';

$smt->title = 'About ' . $name;
$smt->includeHeader();
$smt->includeMediumMenu();
print '
<div class="box white" style="padding:30px;">
<h1>' . $name. '</h1>
<p>' . $about . '</p>
<hr />
<h2>How to use this site:</h2>
<br /><br />
<dl>
    <dt><h3><a href="' . Tools::url('home') . '">Review a file</a><h3></dt>
    <dd>Click a rating for each media file shown.
    Click an image to goto the <b>Info</b> page with details on the media file.
    </dd>
    <br />
    <dt><h3><a href="' . Tools::url('categories') . '">Browse Categories</a><h3></dt>
    <dd>Every file belongs to each least 1 category.
    This page lists all the categories, and the combined ratings for each category.
    </dd>
    <br />
    <dt><h3><a href="' . Tools::url('reviews') . '">Browse Reviews</a><h3></dt>
    <dd>All rated media is shown here, with a page for each rating option.</dd>
    <br />
    <dt><h3><a href="' . Tools::url('users') . '">Browse Users</a><h3></dt>
    <dd>All ratings per user are shown here.</dd>
    <br />
    <dt><h3><a href="' . Tools::url('contact') . '">Contact</a></h3></dt>
    <dd>Have a question or comment?  Contact the site administrators with this form.</dd>
</dl>
<hr /><h2>Reusing images, videos and audio files</h2>
<br /><br />
<p>All media files highlighted on this site are
<b><a href="https://freedomdefined.org/Definition" target="commons">Free Cultural Works</a></b>.</p>
<p>You may copy, use and modify these media files for commercial or non-commercial purposes, 
as long as you properly follow the licensing.<p>
<p>See each media files info page for complete licensing information.<p>
</div>';

$smt->includeFooter();
