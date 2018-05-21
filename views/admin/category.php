<?php
/**
 * Shared Media Tagger
 * Category Admin
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin $smt
 */

if (function_exists('set_time_limit')) {
    set_time_limit(1000);
}

$smt->title = 'Category Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white">';

///////////////////////////////////////////////////////////////////////////////
// Import images from a category
if (isset($_GET['i']) && $_GET['i']) {
    $categoryName = $smt->category_urldecode($_GET['i']);
    $catUrl = '<a href="' . $smt->url('category')
    . '?c=' . $smt->category_urlencode($smt->strip_prefix($categoryName)) . '">'
    . htmlentities($smt->strip_prefix($categoryName)) . '</a>';
    print '<p>Importing media from <b>' . $catUrl . '</b></p>';
    $smt->get_media_from_category($categoryName);
    $smt->update_categories_local_files_count();
    print '<p>Imported media from <b>' . $catUrl . '</b></p>';
    print '</div>';
    $smt->include_footer();
    return;
}

///////////////////////////////////////////////////////////////////////////////
if (isset($_POST['cats']) && $_POST['cats']) {
    $smt->import_categories($_POST['cats']);
    $smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

if (isset($_GET['c']) && $_GET['c']) {
    if ($smt->save_category_info(urldecode($_GET['c']))) {
        $smt->notice(
            'OK: Refreshed Category Info: <b><a href="' . $smt->url('category')
            . '?c=' . $smt->strip_prefix($smt->category_urlencode($_GET['c'])) . '">'
            . htmlentities($smt->category_urldecode($_GET['c']))
        ) . '</a></b>';
    }
    //$smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

if (isset($_GET['d']) && $_GET['d']) {
    $smt->delete_category($_GET['d']);
    $smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

if (isset($_GET['scommons']) && $_GET['scommons']) {
    getSearchResults($smt);
    print '</div>';
    $smt->include_footer();
    return;
}

if (isset($_GET['sc']) && $_GET['sc']) {
    $smt->get_subcats($smt->category_urldecode($_GET['sc']));
    $smt->update_categories_local_files_count();
    print '</div>';
    $smt->include_footer();
    return;
}

$orderBy = ' ORDER BY hidden ASC, local_files DESC, files DESC, name ASC ';

if (isset($_GET['g']) && $_GET['g']=='all') {
    $toget = [];
    $cats = $smt->query_as_array('SELECT * FROM category ' . $orderBy);
    foreach ($cats as $cat) {
        if ($cat['subcats'] != '' && $cat['files'] != '' && $cat['pageid'] != '') {
            continue;
        }
        if (sizeof($toget) == 50) {
            break;
        }
        $toget[] = $cat['name'];
    }
    $_GET['c'] = implode('|', $toget);
    $smt->get_category_info($_GET['c']);
    print '</div>';
    $smt->include_footer();
    return;
}

///////////////////////////////////////////////////////////////////////////////

if (isset($_GET['sca']) && $_GET['sca']=='all') {
    $sql = 'SELECT * FROM category WHERE subcats > 0 ' . $orderBy;
    $smt->notice('SHOWING only categories with subcategories');

} elseif (isset($_GET['wf'])) {
    $sql = 'SELECT * FROM category WHERE files > 0 ' . $orderBy;
    $smt->notice('SHOWING only categories with files');

} elseif (isset($_GET['s'])) {
    $sql = 'SELECT * FROM category WHERE name LIKE :search ' . $orderBy;
    $bind = [':search'=>'%' . $_GET['s']. '%'];
    $smt->notice('SHOWING only categories with search text: ' . $_GET['s']);

} else {
    $sql = 'SELECT * FROM category ' . $orderBy;

}
if (!isset($bind)) {
    $bind = [];
}
$cats = $smt->query_as_array($sql, $bind);

if (!is_array($cats)) {
    $cats = [];
}

$spacer = ' &nbsp; &nbsp; &nbsp; ';

print ''
. '<ul>'
. '<li><b>' . number_format($smt->get_categories_count()) . '</b> Active Categories</li>'
. '<li><b>?</b> Technical Categories</li>'
. '<li><b>?</b> Empty Categories</li>'
. '</ul>'
. '<p><form action="" method="GET">'
. '<input name="scommons" type="text" size="35" value="">'
. '<input type="submit" value="   Find Categories on COMMONS  "></form>'
. '<br /><br />'
. '<p><form action="" method="GET">'
. '<input type="hidden" name="v" value="1">'
. '<input type="text" name="s" value="" size="20">'
. '<input type="submit" value="   Search LOCAL Categories   "></form>'
. '<br /><br />'

. '<a href="' . $smt->url('admin') . 'category.php?v=1">[View&nbsp;Category&nbsp;List]</a>'
. $spacer
. ' <a href="./sqladmin.php?table=category&action=row_create" target="sqlite">'
. ' [Manually&nbsp;add&nbsp;category]</a>'
. $spacer
. '<a href="' . $smt->url('admin') . 'category.php?g=all">[Import&nbsp;Category&nbsp;Info]</a>'
. '</p>';

if (($smt->get_categories_count() > 1000) && (@$_GET['v'] != 1)) {
    print '</div>';
    $smt->include_footer();
    return;
}

print '<table border="1">'
. '<tr style="background-color:lightblue;font-style:italic;">'
. '<td>Category:</td>'
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

$commonFilesCount = $localFilesCount = 0;

foreach ($cats as $cat) {
    $commonFilesCount += $cat['files'];

    print '<tr>'
    . '<td><b><a href="' . $smt->url('category') . '?c='
    . $smt->category_urlencode($smt->strip_prefix($cat['name']))
    . '">' . $smt->strip_prefix($cat['name']) . '</a></b></td>';

    $localFiles = '';

    $lcount = $cat['local_files'];
    if (!$lcount) {
        $localFiles = '<span style="color:#ccc;">0</span>';
    } else {
        $localFiles = $lcount;
        $localFilesCount += $lcount;
    }

    if ($localFiles != $cat['files']) {
        $alertTd = ' style="background-color:lightsalmon;"';
    } else {
        $alertTd = '';
    }
    print ''
    . '<td class="right" ' . $alertTd . '>' . $localFiles . '</td>'
    . '<td class="right">' . ($cat['files'] ? number_format($cat['files']) : '<span style="color:#ccc;">0</span>') . '</td>'
    ;
    if ($cat['subcats'] > 0) {
        $subcatslink = '<a href="./' . basename(__FILE__) . '?sc=' . $smt->category_urlencode($cat['name']) . '"">+'
        . $cat['subcats'] . '</a>';
    } else {
        $subcatslink = '';
        if ($cat['pageid'] > 0) {
            $subcatslink = '<span style="color:#ccc;">0</span>';
        }
    }
    print '<td class="right">' . $subcatslink . '</td>';

    print ''
    . '<td style="padding:0 10px 0 10px;"><a target="commons" href="https://commons.wikimedia.org/wiki/'
        . $smt->category_urlencode($cat['name']) . '">View</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__)
        . '?c=' . $smt->category_urlencode($cat['name']) . '">Info</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__)
        . '?i=' . $smt->category_urlencode($cat['name']) . '">Import</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./media.php?dc='
        . $smt->category_urlencode($cat['name']) . '">Clear</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__)
        . '?d=' . urlencode($cat['id']) . '">Delete</a></td>'
    . '</tr>'
    ;
}
print '</table>';
print '<br /><b>' . $localFilesCount . '</b> Files Under Review';
print '<br /><b>' . $commonFilesCount . '</b> Total Files on Commons';
///////////////////////////////////////////////////////////////////////////////

print '</div>';
$smt->include_footer();


/**
 * @param \Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin $smt
 */
function getSearchResults(\Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin $smt)
{
    $search = urldecode($_GET['scommons']);

    if (!$smt->find_categories($search)) {
        $smt->notice('Error: no categories found');
        return;
    }
    $cats = @$smt->commons_response['query']['search'];
    if (!$cats || !is_array($cats)) {
        $smt->notice('Error: no categories returned');
        return;
    }
    print '<p>Searched "' . $search . '": showing <b>' . sizeof($cats) . '</b> of <b>'
        . $smt->totalhits . '</b> categories</p>';
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

    while ((list(,$cat) = each($cats))) {
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
