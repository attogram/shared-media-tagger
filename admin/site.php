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
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white">';

if( isset($_POST) && $_POST ) {
    save_site_info();
}

//////////////////////////////////////////////////////////////////////////////////////////////
$sites = $smt->query_as_array('SELECT * FROM site ORDER BY id LIMIT 1', array() );

if( !$sites || !is_array($sites[0])) {
    $smt->error('Creating New Site, ID #1');
    $smt->query_as_bool("INSERT INTO site (id) VALUES ('1')");
    $sites[0]['id'] = 1;
}
$site = $sites[0];


//////////////////////////////////////////////////////////////////////////////////////////////


print ''
. '<form action="" method="POST">'
. '<input type="hidden" name="id" value="' . @$site['id'] . '">'
. '<input type="submit" value="           Save Site Setup           ">'

. '<br /><br />Name:<br />'
. '<input name="name" type="text" size="30" value="' . htmlentities(@$site['name']) . '">'

. '<br /><br />About:<br />'
. '<textarea name="about" rows="5" cols="70">' . htmlentities(@$site['about']) . '</textarea>'

. '<br /><br />Header:<br />'
. '<textarea name="header" rows="5" cols="70">' . htmlentities(@$site['header']) . '</textarea>'

. '<br /><br />Footer:<br />'
. '<textarea name="footer" rows="5" cols="70">' . htmlentities(@$site['footer']) . '</textarea>'

. '<br /><br /><input type="submit" value="           Save Site Setup           ">'
. '</form>'
. '<br /><br />'
. '<br /><small>Site ID: ' . @$site['id'] . '</small>'
. '<br /><small>Last updated: ' . @$site['updated'] . '</small>';


print '<br /><br /><br /><br /><br /><hr />Modify Database directly:<ul>'
. '<li><a target="sqlite" href="./sqladmin.php?table=site&action=row_editordelete&pk=['
. @$site['id'] . ']&type=edit">EDIT site</a></li>'
. '<li><a target="sqlite" href="./sqladmin.php?table=site&action=row_create">'
. 'CREATE NEW site</a></li>'
. '</ul>';


print '</div>';
$smt->include_footer();


//////////////////////////////////////////////////////////////////////////////////////////////
function save_site_info() {

    global $smt;

    $smt->debug('save_site_info()');

    $bind = array();
    while( list($name,$value) = each($_POST) ) {
        switch( $name ) {
            case 'id':
                $bind[":$name"] = $value;
                break;
            case 'name':
            case 'about':
            case 'header':
            case 'footer':
                $set[] = "$name = :$name";
                $bind[":$name"] = $value;
                break;
        }
    }

    $sql = 'UPDATE site SET ' . implode($set,', ') . ' WHERE id = :id';
    $smt->debug($sql);
    $smt->debug($bind);

    if( $smt->query_as_bool($sql, $bind) ) {
        $smt->notice('OK: Site Info Saved');
        return TRUE;
    }
    $smt->error('Unable to update site: ' . print_r($smt->last_error,1) );
    return FALSE;
}
