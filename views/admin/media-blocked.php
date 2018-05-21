<?php
// Shared Media Tagger
// Blocked Media Admin


$smt->title = 'Blocked Media Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>Blocked Media Admin:</p>';
?>
* <a target="sqlite" href="<?php print $smt->url('admin'); ?>sqladmin.php?table=block&action=row_view">Database: View/Edit Blocked Media</a>
<hr />

<?php

// TODO - pager

$sql = "SELECT * 
		FROM block 
		ORDER BY pageid ASC
		LIMIT 200
		";

$blocks = $smt->query_as_array($sql);
if (!$blocks || !is_array($blocks)) {
    $blocks = array();
}

print '<p><b>' . sizeof($blocks) . '</b> Blocked Media</p>';

foreach ($blocks as $block) {
    $url = $block['thumb'];
    $width = 220;
    $url = str_replace('325px', $width . 'px', $url);


    print ''
    . '<img src="' . $url . '" width="' . $width . '" style="vertical-align:middle;">'
    . '<div style="display:inline-block; border:1px solid red; padding:10px;">'
    . $block['pageid']
    . ': '
    . '<a target="commmons" href="https://commons.wikimedia.org/w/index.php?curid=' . $block['pageid'] . '">'
    . $smt->strip_prefix($block['title'])
    . '<br />'
    . '<br />'
    . '<a target="sqlite" href="./sqladmin.php?table=block&action=row_editordelete&pk=%5B'
        . $block['pageid'] . '%5D&type=delete">* Remove from Block List</a>'
    . '</div>'
    . '<br clear="all" />'
    . '<br clear="all" />'
    ;
}


print '</div>';
$smt->include_footer();
