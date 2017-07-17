<?php
// Shared Media Tagger
// Site Admin

$f = __DIR__.'/../smt.php';
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$f = __DIR__.'/smt-admin.php';
if(!file_exists($f)||!is_readable($f)){ print 'Site down for maintenance'; exit; } require_once($f);
$smt = new smt_admin();

$smt->title = 'Site Admin';
$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white">';

if( isset($_GET['tagid']) && $smt->is_positive_number($_GET['tagid']) ) {
    save_tag();
}




$sites = $smt->query_as_array('SELECT id, name, about FROM site ORDER BY id LIMIT 1', array() );
if( !$sites || !is_array($sites[0])) {
    $smt->error('No Site Found');
    $sites[0] = array('id'=>0,'name'=>'','about'=>'');
}
$site = $sites[0];
if( !$site['name'] ) {$site['name'] = '(empty)'; }
if( !$site['about'] ) {$site['about'] = '(empty)'; }

print ''
. '<p>Site Name / About:  (<a
target="sqlite" href="./sqladmin.php?table=site&action=row_editordelete&pk=['
. $site['id'] . ']&type=edit">EDIT</a>)'
. '<div style="font-weight:bold; font-size:120%; color:white; background-color:black; padding:2px;">'
. $site['name'] . '</div>'
. '<div style="color:black; background-color:#ddd; padding:2px;">'
. $site['about'] . '</div>';

$tags = $smt->get_tags();

print '<br /><em>Tags preview:</em>'
. $smt->display_tags(0)
. '<br /><em>Tags setup:</em>'
. '<table border="1" style="margin:0px;"><tr>'
. '<th>#</th>'
. '<th>pos</th>'
. '<th>Name</th>'
. '<th>Tag Name</th>'
//. '<th>Color</th>'
//. '<th>BGcolor</th>'
//. '<th>Hover Color</th>'
//. '<th>Hover BGcolor</th>'
//. '<th>padding</th>'
. '<th></th>'
. '</tr>';
foreach( $tags as $tag ) {
    print '<form action="" method="GET"><input type="hidden" name="tagid" value="' . $tag['id'] . '">';
    print '<tr >'
    . '<td>'
        . '<a target="sqlite" href="./sqladmin.php?table=tag&action=row_editordelete&pk=['
            . $tag['id'] . ']&type=edit">' . $tag['id'] . '</a>'
    . '</td>'
    . '<td><input name="position" value="' . $tag['position'] . '" size="1" /></td>'
    . '<td><textarea name="name" rows="4" cols="20">' . htmlentities($tag['name']) . '</textarea></td>'
    . '<td><textarea name="display_name" rows="4" cols="25">' . htmlentities($tag['display_name']) . '</textarea></td>'
//    . '<td><input name="color" value="' . $tag['color'] . '" size="8" /></td>'
//    . '<td><input name="bgcolor" value="' . $tag['bgcolor'] . '" size="8" /></td>'
//    . '<td><input name="hover_color" value="' . $tag['hover_color'] . '" size="8" /></td>'
//    . '<td><input name="hover_bgcolor" value="' . $tag['hover_bgcolor'] . '" size="8" /></td>'
//    . '<td><input name="padding" value="' . $tag['padding'] . '" size="2" /></td>'
    . '<td><input type="submit" value="    Save Tag #' . $tag['id'] .'  "></td>'
    . '</tr>'
    ;
    print '</form>';
}
print '</table>';
print '<br /><a href="./sqladmin.php?table=tag&action=row_create" target="sqlite">ADD NEW tag</a>'
 . '<br /><a href="./sqladmin.php?action=row_view&table=tag" target="sqlite">VIEW/EDIT tag table</a>';


print '</div>';
$smt->include_footer();





////////////////////////////////////////////////////
function save_tag() {
    global $smt;

    $sql = 'UPDATE tag SET
        position = :position,
        name = :name, display_name = :display_name,
        color = :color, bgcolor = :bgcolor,
        hover_color = :hover_color, hover_bgcolor = :hover_bgcolor,
        padding = :padding
    WHERE id = :id
    ';
    $bind = array(
        ':id'    => @$_GET['tagid'],
        ':position'    => @$_GET['position'],
        ':name'    => @$_GET['name'],
        ':display_name'    => @$_GET['display_name'],
        ':color'    => @$_GET['color'],
        ':bgcolor'    => @$_GET['bgcolor'],
        ':hover_color'    => @$_GET['hover_color'],
        ':hover_bgcolor'    => @$_GET['hover_bgcolor'],
        ':padding'    => @$_GET['padding'],
    );

    if( $smt->query_as_bool($sql, $bind) ) {
        $smt->notice('OK: Saved Tag ID#'.$_GET['tagid']);
        return TRUE;
    }
    $smt->notice('save_tag: Can Not Save Tag Data.<br />'.$sql.'<br/>  bind: <pre>'
        . print_r($bind,1) . ' </pre>');
    return FALSE;
}