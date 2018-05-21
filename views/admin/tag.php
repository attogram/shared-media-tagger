<?php
/**
 * Shared Media Tagger
 * Tag Admin
 *
 * @var SharedMediaTaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin;

$smt->title = 'Tag Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white">';

//////////////////////////////////////////////////////////////////////////////////////////////
if (isset($_GET['tagid']) && $smt->isPositiveNumber($_GET['tagid'])) {
    saveTag($smt);
}

//////////////////////////////////////////////////////////////////////////////////////////////
$tags = $smt->getTags();

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
    . '<tr><td><a target="sqlite" href="./sqladmin.php?table=tag&action=row_editordelete&pk=['
    . $tag['id'] . ']&type=edit">' . $tag['id'] . '</a></td>'
    . '<td><input name="position" value="' . $tag['position'] . '" size="1" /></td>'
    . '<td><textarea name="name" rows="4" cols="20">' . htmlentities($tag['name']) . '</textarea></td>'
    . '<td><textarea name="display_name" rows="4" cols="25">' . htmlentities($tag['display_name']) . '</textarea></td>'
    . '<td><input type="submit" value="    Save Tag #' . $tag['id'] .'  "></td>'
    . '</tr></form>';
}
print '</table>';
print '<br /><a href="./sqladmin.php?table=tag&action=row_create" target="sqlite">ADD NEW tag</a>'
 . '<br /><a href="./sqladmin.php?action=row_view&table=tag" target="sqlite">VIEW/EDIT tag table</a>';

print '</div>';
$smt->includeFooter();

/**
 * @param SharedMediaTaggerAdmin $smt
 * @return bool
 */
function saveTag(SharedMediaTaggerAdmin $smt)
{
    $sql = '
    UPDATE tag
    SET position = :position,
        name = :name,
        display_name = :display_name
    WHERE id = :id';
    $bind = [
        ':id' => @$_GET['tagid'],
        ':position' => @$_GET['position'],
        ':name' => @$_GET['name'],
        ':display_name' => @$_GET['display_name']
    ];

    if ($smt->queryAsBool($sql, $bind)) {
        $smt->notice('OK: Saved Tag ID#'.$_GET['tagid']);
        return true;
    }
    $smt->notice('save_tag: Can Not Save Tag Data.<br />'.$sql.'<br/>  bind: <pre>'
        . print_r($bind, 1) . ' </pre>');
    return false;
}
