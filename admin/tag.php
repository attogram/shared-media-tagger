<?php
// Shared Media Tagger
// Tag Admin

////////////////////////////////////////////////////////////////////
$init = __DIR__.'/../smt.php'; // Shared Media Tagger Main Class
if( !is_readable($init) ) {
    print 'ERROR: not readable: ' . $init;
    return;
}
require_once($init);
$init = __DIR__.'/smt-admin.php'; // Shared Media Tagger Admin Class
if( !is_readable($init) ) {
    print 'ERROR: not readable: ' . $init;
    return;
}
require_once($init);
$smt = new smt_admin(); // The Shared Media Tagger Admin Object
/////////////////////////////////////////////////////////////////////

$smt->title = 'Tag Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white">';

//////////////////////////////////////////////////////////////////////////////////////////////
if( isset($_GET['tagid']) && $smt->is_positive_number($_GET['tagid']) ) {
    save_tag();
}

//////////////////////////////////////////////////////////////////////////////////////////////

$tags = $smt->get_tags();

print '<br /><em>Tags preview:</em>'
. $smt->display_tags(0)
. '<br /><em>Tags setup:</em>'
. '<table border="1" style="margin:0px;"><tr>'
. '<th>#</th>'
. '<th>pos</th>'
. '<th>Name</th>'
. '<th>Tag Name</th>'
. '<th></th>'
. '</tr>';
foreach( $tags as $tag ) {
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
$smt->include_footer();


//////////////////////////////////////////////////////////////////////////////////////////////
function save_tag() {
    global $smt;

    $sql = '
    UPDATE tag
    SET position = :position,
        name = :name,
        display_name = :display_name
    WHERE id = :id
    ';
    $bind = array(
        ':id'    => @$_GET['tagid'],
        ':position'    => @$_GET['position'],
        ':name'    => @$_GET['name'],
        ':display_name'    => @$_GET['display_name']
    );

    if( $smt->query_as_bool($sql, $bind) ) {
        $smt->notice('OK: Saved Tag ID#'.$_GET['tagid']);
        return TRUE;
    }
    $smt->notice('save_tag: Can Not Save Tag Data.<br />'.$sql.'<br/>  bind: <pre>'
        . print_r($bind,1) . ' </pre>');
    return FALSE;
}