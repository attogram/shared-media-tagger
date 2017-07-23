<?php
// Shared Media Tagger
// Admin Reports

$init = __DIR__.'/../smt.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$init = __DIR__.'/smt-admin.php';
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt_admin();

$smt->title = 'Admin Reports';
$smt->include_header();
$smt->include_menu( /*show_counts*/FALSE );
$smt->include_admin_menu();
print '<div class="box white"><p><a href="' . $smt->url('admin') .'reports.php">' . $smt->title . '</a></p>
<ul>
<li><a href="' . $smt->url('admin') . 'reports.php?r=localfiles">update_categories_local_files_count()</a>
<br />
<br />
<li><a href="' . $smt->url('admin') . 'reports.php?r=catclean">Clean Category Table</a></li>
</ul>
<hr />';


switch( @$_GET['r'] ) {
    default: print '<p>Please choose a report above</p>'; break;
    case 'localfiles': $smt->update_categories_local_files_count(); break;
    case 'catclean': catclean(); break;
} // end switch

print '</div>';
$smt->include_footer();




////////////////////////////////
function catclean() {

    global $smt;

    $tab = " \t ";

    $checker_limit = 25;
    if( isset($_GET['checker']) && $_GET['checker'] ) {
        $checker_limit = (int)$_GET['checker'];
    }

    print '<p>Clean Category Table:</p>'
    . '<p><a href="?r=catclean&amp;cleaner=1">RUN CLEANER</a> (updates: local_files, sanitizes: hidden, missing.  No API calls.)</p>'
    . '<p><a href="?r=catclean&amp;checker=' . $checker_limit . '">RUN CATEGORY-INFO CHECKER x'
    . $checker_limit. '</a>  (updates ALL category info.  Remote API calls.)</p>';

    if( isset($_GET['cleaner']) ) {
        $categories = $smt->query_as_array('SELECT * FROM category');
        //print '<p>START: CLEANER</p>';
        $smt->begin_transaction();
        $result = '';
        foreach( $categories as $category ) {
            //$result .= ' ' . $category['id'];
            $bind = array();
            $bind[':local_files'] = $smt->get_category_size($category['name']);
            $bind[':hidden'] = 0;
            if( $category['hidden'] == 1 ) { $bind[':hidden'] = 1; }
            $bind[':missing'] = 0;
            if( $category['missing'] == 1 ) { $bind[':missing'] = 1; }
            $bind[':id'] = $category['id'];
            $upd = $smt->query_as_bool('UPDATE category SET
                    local_files = :local_files,
                    hidden = :hidden,
                    missing = :missing
                    WHERE id = :id', $bind);
            if( $upd ) { continue; }
            $result .= '<span style="color:red;">ERR:' . $category['id'] . '</span>';
        }
        $smt->commit();
        $smt->vacuum();
        print '<p>OK: RAN: CLEANER: <span style="font-size:80%;">' . $result . '</span></p>';
    }

    if( isset($_GET['checker']) ) {
        $categories = $smt->query_as_array(
            'SELECT * FROM category ORDER BY updated ASC LIMIT ' . $checker_limit
        );
        //print '<p>START: CATEGORY-INFO CHECKER x' . $checker_limit . '</p>';
        $smt->begin_transaction();
        $result = '';
        foreach( $categories as $category ) {
            $result .= ' ' . $category['id'];
            if( $smt->save_category_info($category['name']) ) {
                continue;
            }
            $result .= '<span style="color:red;">ERR:' . $category['id'] . '</span>';
        }
        $smt->commit();
        $smt->vacuum();
        print '<p>OK: RAN: CATEGORY-INFO CHECKER: <span style="font-size:80%;">' . $result . '</span></p>';
    }





    $categories = $smt->query_as_array(
        'SELECT * FROM category ORDER BY hidden ASC, local_files DESC, name ASC'
    );
    print '<p><b>' . number_format(sizeof($categories)) . '</b> Categories</p>';

    print '<pre>'
    . '<b>LOCAL' . $tab
    . 'COM' . $tab
    . 'H M ID' . $tab
    . 'Last Updated' . $tab . $tab
    . 'Category</b><br />';
    foreach( $categories as $category ) {
        print ''

        . number_format($category['local_files']) . $tab
        . number_format($category['files']) . $tab
        . $category['hidden'] . ' '
        . $category['missing'] . ' '
        . $category['id'] . $tab

        . ($category['updated'] ? $category['updated'] : '0000-00-00 00:00:00' ) . $tab

        . '<a target="site" href="' . $smt->url('category') . '?c='
        . $smt->category_urlencode($smt->strip_prefix($category['name']))
        . '">' . $category['name'] . '</a>'
        . '<br />';
    }
    print '<br />END or report.</pre>';


} // end catsize()
