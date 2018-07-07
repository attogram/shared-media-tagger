<?php
/**
 * Shared Media Tagger
 * Tag Admin
 *
 * @var TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Tag Admin';
$smt->includeHeader();
$smt->includeTemplate('MenuSmall');
$smt->includeAdminMenu();
print '<div class="box white">';

//////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['tagid']) && Tools::isPositiveNumber($_GET['tagid'])) {
    $smt->saveTag();
}

//////////////////////////////////////////////////////////////////////////////////////////////
$tags = $smt->database->getTags();

print '<br /><em>Tags preview:</em>'
. $smt->displayTags(0)
. '<br /><em>Tags setup:</em>'
. '<table border="1" style="margin:0;"><tr>'
. '<th>#</th>'
. '<th>pos</th>'
. '<th>Name</th>'
. '<th>Tag Name</th>'
. '<th></th>'
. '</tr>';
foreach ($tags as $tag) {
    print '<form action="" method="GET"><input type="hidden" name="tagid" value="' . $tag['id'] . '">'
    . '<tr><td><a target="sqlite" href="./sqladmin?table=tag&action=row_editordelete&pk=['
    . $tag['id'] . ']&type=edit">' . $tag['id'] . '</a></td>'
    . '<td><input name="position" value="' . $tag['position'] . '" size="1" /></td>'
    . '<td><textarea name="name" rows="4" cols="20">' . htmlentities((string) $tag['name']) . '</textarea></td>'
    . '<td><textarea name="display_name" rows="4" cols="25">'
        . htmlentities((string) $tag['display_name']) . '</textarea></td>'
    . '<td><input type="submit" value="    Save Tag #' . $tag['id'] .'  "></td>'
    . '</tr></form>';
}
print '</table>';
print '<br /><a href="./sqladmin?table=tag&action=row_create" target="sqlite">ADD NEW tag</a>'
 . '<br /><a href="./sqladmin?action=row_view&table=tag" target="sqlite">VIEW/EDIT tag table</a>';

print '</div>';
$smt->includeFooter();
