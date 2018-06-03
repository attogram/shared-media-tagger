<?php
/**
 * Shared Media Tagger
 * Blocked Media Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Blocked Media Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white"><p>Blocked Media Admin:</p>';
?>
* <a target="sqlite" href="<?php print Tools::url('admin'); ?>sqladmin?table=block&action=row_view">'
    . 'Database: View/Edit Blocked Media</a>
<hr />

<?php

// TODO - pager

$sql = "SELECT * 
		FROM block 
		ORDER BY pageid ASC
		LIMIT 200
		";

$blocks = $smt->database->queryAsArray($sql);
if (!$blocks || !is_array($blocks)) {
    $blocks = [];
}

print '<p><b>' . sizeof($blocks) . '</b> Blocked Media</p>';

foreach ($blocks as $block) {
    $url = $block['thumb'];
    $width = 220;
    $url = str_replace('325px', $width . 'px', $url);
    print ''
    . '<img src="' . $url . '" width="' . $width . '" style="vertical-align:middle;">'
    . '<div style="display:inline-block;border:1px solid red;padding:10px;">'
    . $block['pageid']
    . ': '
    . '<a target="commmons" href="https://commons.wikimedia.org/w/Home.php?curid=' . $block['pageid'] . '">'
    . Tools::stripPrefix($block['title'])
    . '<br />'
    . '<br />'
    . '<a target="sqlite" href="./sqladmin?table=block&action=row_editordelete&pk=%5B'
        . $block['pageid'] . '%5D&type=delete">* Remove from Block List</a>'
    . '</div>'
    . '<br clear="all" />'
    . '<br clear="all" />'
    ;
}

print '</div>';

$smt->includeFooter();
