<?php
// Shared Media Tagger
// Category Admin

if( function_exists('set_time_limit') ) { set_time_limit( 1000 ); }

$init = __DIR__.'/../smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; return; } require_once($init);
$init = __DIR__.'/smt-admin.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; return; } require_once($init);

$smt = new smt_admin();

$smt->title = 'Category Admin';
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
$smt->include_admin_menu();
print '<div class="box white">';

///////////////////////////////////////////////////////////////////////////////
// Import images from a category
if( isset($_GET['i']) && $_GET['i'] ) {
    $category_name = $smt->category_urldecode($_GET['i']);
    $cat_url = '<a href="' . $smt->url('category')
    . '?c=' . $smt->category_urlencode($smt->strip_prefix($category_name)) . '">'
    . htmlentities($smt->strip_prefix($category_name)) . '</a>';
    print '<p>Importing media from <b>' . $cat_url . '</b></p>';
    $smt->get_media_from_category( $category_name );
    $smt->update_categories_local_files_count();
    print '<p>Imported media from <b>' . $cat_url . '</b></p>';
    print '</div>';
    $smt->include_footer();
    return;
}


///////////////////////////////////////////////////////////////////////////////
if( isset($_POST['cats']) && $_POST['cats'] ) {
    $smt->import_categories( $_POST['cats'] );
    $smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

if( isset($_GET['c']) && $_GET['c'] ) {
    if( $smt->save_category_info( urldecode($_GET['c']) ) ) {
        $smt->notice('OK: Refreshed Category Info: '
        . '<b><a href="' . $smt->url('category') . '?c='
        . $smt->strip_prefix($smt->category_urlencode($_GET['c']))
        . '">' . htmlentities($smt->category_urldecode($_GET['c']))) . '</a></b>';
    }
    //$smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

if( isset($_GET['d']) && $_GET['d'] ) {
    $smt->delete_category($_GET['d']);
    $smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

if( isset($_GET['s']) && $_GET['s'] ) {
    get_search_results($smt);
    print '</div>';
    $smt->include_footer();
    return;
}

if( isset($_GET['sc']) && $_GET['sc'] ) {
    $smt->get_subcats( $smt->category_urldecode($_GET['sc']) );
    $smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}


$order_by = ' ORDER BY hidden ASC, local_files DESC, files DESC, name ASC ';

if( isset($_GET['g']) && $_GET['g']=='all' ) {
    $toget = array();
    $cats = $smt->query_as_array('SELECT * FROM category ' . $order_by);
    foreach( $cats as $c ) {
        if( $c['subcats'] != '' && $c['files'] != '' && $c['pageid'] != '' ) {
            continue;
        }
        if( sizeof($toget) == 50 ) {
            break;
        }
        $toget[] = $c['name'];
    }
    $_GET['c'] = implode('|',$toget);
    $smt->get_category_info($smt);
    print '</div>';
    $smt->include_footer();
    return;
}


///////////////////////////////////////////////////////////////////////////////


if( isset($_GET['sca']) && $_GET['sca']=='all' ) {
    $sql = 'SELECT * FROM category WHERE subcats > 0 ' . $order_by;
    $smt->notice('SHOWING only categories with subcategories');

} elseif( isset($_GET['wf']) ) {
    $sql = 'SELECT * FROM category WHERE files > 0 ' . $order_by;
    $smt->notice('SHOWING only categories with files');

} elseif( isset($_POST['s']) ) {
    $sql = 'SELECT * FROM category WHERE name LIKE :search ' . $order_by;
    $bind = array(':search'=>'%' . $_POST['s']. '%');
    $smt->notice('SHOWING only categories with search text: ' . $_POST['s'] );

} else {
    $sql = 'SELECT * FROM category ' . $order_by;

}
if( !isset($bind) ) { $bind = array(); }
$cats = $smt->query_as_array($sql, $bind);

if( !is_array($cats) ) { $cats = array(); }


$spacer = ' &nbsp; &nbsp; &nbsp; ';
print '<p><form action="" method="GET">'
. '<input id="s" name="s" type="text" size="35" value=""/>'
. '<input type="submit" value="   Find Categories on Commons  " /></form>'
. '<br /><br />'
. '<a href="' . $smt->url('admin') . 'category.php?v=1">[View&nbsp;Category&nbsp;List]</a>'
. $spacer
. ' <a href="./sqladmin.php?table=category&action=row_create" target="sqlite">'
. ' [Manually&nbsp;add&nbsp;category]</a>'
. $spacer
. '<a href="' . $smt->url('admin') . 'category.php?g=all">[Import&nbsp;Category&nbsp;Info]</a>'
. '</p>';

if( !isset($_GET['v']) || $_GET['v'] != '1' ) {
    print '</div>';
    $smt->include_footer();
    return;
}

print '<table border="1">'
. '<tr style="background-color:lightblue;font-style:italic;">'
. '<td><form method="POST"><input type="text" name="s" value="" size="20"><input type="submit" value="search"></form>
</td>'
. '<td><small>Loc<br />files</small></td>'
. '<td><small><a href="./'. basename(__FILE__) . '?wf=1">Com<br/>files</a></small></td>'
. '<td><small><a href="./' . basename(__FILE__) . '?sca=all">Sub<br />cats</a></small></td>'
. '<td>view</td>'
. '<td><a href="./' . basename(__FILE__) . '?g=all">info</a></td>'
. '<td>import</td>'
. '<td>Clear</td>'
. '<td>Delete</td>'
. '</tr>'
;

reset($cats);

$common_files_count = $local_files_count = 0;

foreach( $cats as $c ) {

    $common_files_count += $c['files'];

    print '<tr>'
    . '<td><b><a href="' . $smt->url('category') . '?c='
    . $smt->category_urlencode($smt->strip_prefix($c['name']))
    . '">' . $smt->strip_prefix($c['name']) . '</a></b></td>';

    $local_files = '';

    $lcount = $c['local_files'];
    if( !$lcount ) {
        $local_files = '<span style="color:#ccc;">0</span>';
    } else {
        $local_files = $lcount;
        $local_files_count += $lcount;
    }

    if( $local_files != $c['files'] ) {
        $alert_td = ' style="background-color:lightsalmon;"';
    } else {
        $alert_td = '';
    }
    print ''
    . '<td class="right" ' . $alert_td . '>' . $local_files . '</td>'
    . '<td class="right">' . ($c['files'] ? number_format($c['files']) : '<span style="color:#ccc;">0</span>') . '</td>'
    ;
    if( $c['subcats'] > 0 ) {
        $subcatslink = '<a href="./' . basename(__FILE__) . '?sc=' . $smt->category_urlencode($c['name']) . '"">+'
        . $c['subcats'] . '</a>';
    } else {
        $subcatslink = '';
        if( $c['pageid'] > 0 ) {
            $subcatslink = '<span style="color:#ccc;">0</span>';
        }
    }
    print '<td class="right">' . $subcatslink . '</td>';

    print ''
    . '<td style="padding:0 10px 0 10px;"><a target="commons" href="https://commons.wikimedia.org/wiki/'
        . $smt->category_urlencode($c['name']) . '">View</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__) . '?c=' . $smt->category_urlencode($c['name']) . '">Info</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__) . '?i=' . $smt->category_urlencode($c['name']) . '">Import</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./media.php?dc=' . $smt->category_urlencode($c['name']) . '">Clear</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__) . '?d=' . urlencode($c['id']) . '">Delete</a></td>'
    . '</tr>'
    ;
}
print '</table>';


print '<br /><b>' . $local_files_count . '</b> Files Under Review';
print '<br /><b>' . $common_files_count . '</b> Total Files on Commons';

///////////////////////////////////////////////////////////////////////////////

print '</div>';
$smt->include_footer();



///////////////////////////////////////////////////////////////////////////////
function get_search_results($smt) {

    $search = urldecode($_GET['s']);

    if( !$smt->find_categories($search) ) {
        $smt->notice('Error: no categories found');
        return;
    }
    $cats = @$smt->commons_response['query']['search'];
    if( !$cats || !is_array($cats) ) {
        $smt->notice('Error: no categories returned');
        return;
    }
    print '<p>Searched "' . $search . '": showing <b>' . sizeof($cats) . '</b> of <b>' . $smt->totalhits . '</b> categories</p>';
    print '
    <script type="text/javascript" language="javascript">// <![CDATA[
    function checkAll(formname, checktoggle)
    {
      var checkboxes = new Array();
      checkboxes = document[formname].getElementsByTagName(\'input\');

      for (var i=0; i<checkboxes.length; i++)  {
        if (checkboxes[i].type == \'checkbox\')   {
          checkboxes[i].checked = checktoggle;
        }
      }
    }
    // ]]></script>
    <a onclick="javascript:checkAll(\'cats\', true);" href="javascript:void();">check all</a>
    <a onclick="javascript:checkAll(\'cats\', false);" href="javascript:void();">uncheck all</a>
    ';

    print '<form action="" name="cats" method="POST">'
    . '<input type="submit" value="  save to database  "><br /><br />';

    while( list(,$cat) = each($cats) ) {
        print '<input type="checkbox" name="cats[]" value="' . urlencode($cat['title']) . '"><strong>'
        . $cat['title']
        . '</strong><small> '
        . '<a target="commons" href="https://commons.wikimedia.org/wiki/'
            . $smt->category_urlencode($cat['title']) . '">(view)</a> '
        . ' (' . $cat['snippet'] . ')'
        . ' (size:' . $cat['size'] . ')</small><br />';
    }
    print '</form>';
}

