<?php
/**
 * Shared Media Tagger
 * Category Admin
 *
 * @var TaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

if (function_exists('set_time_limit')) {
    set_time_limit(1000);
}

$smt->title = 'Category Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white">';

///////////////////////////////////////////////////////////////////////////////
// Import images from a category
if (isset($_GET['i']) && $_GET['i']) {
    $categoryName = Tools::categoryUrldecode($_GET['i']);
    $catUrl = '<a href="' . Tools::url('category')
    . '?c=' . Tools::categoryUrlencode(Tools::stripPrefix($categoryName)) . '">'
    . htmlentities(Tools::stripPrefix($categoryName)) . '</a>';
    print '<p>Importing media from <b>' . $catUrl . '</b></p>';
    $smt->database->getMediaFromCategory($categoryName);
    $smt->database->updateCategoriesLocalFilesCount();
    print '<p>Imported media from <b>' . $catUrl . '</b></p>';
    print '</div>';
    $smt->includeFooter();
    return;
}

///////////////////////////////////////////////////////////////////////////////
if (isset($_POST['cats']) && $_POST['cats']) {
    $smt->importCategories($_POST['cats']);
    $smt->database->updateCategoriesLocalFilesCount();
    print '</div>';
    $smt->includeFooter();
    return;
}

if (isset($_GET['c']) && $_GET['c']) {
    if ($smt->admin->saveCategoryInfo(urldecode($_GET['c']))) {
        Tools::notice(
            'OK: Refreshed Category Info: <b><a href="' . Tools::url('category')
            . '?c=' . Tools::stripPrefix(Tools::categoryUrlencode($_GET['c'])) . '">'
            . htmlentities(Tools::categoryUrldecode($_GET['c'])) . '</a></b>'
        );
    }
    print '</div>';
    $smt->includeFooter();
    return;
}

if (isset($_GET['d']) && $_GET['d']) {
    $smt->database->deleteCategory($_GET['d']);
    $smt->database->updateCategoriesLocalFilesCount();
    print '</div>';
    $smt->includeFooter();
    return;
}

if (isset($_GET['scommons']) && $_GET['scommons']) {
    getSearchResults($smt);
    print '</div>';
    $smt->includeFooter();
    return;
}

if (isset($_GET['sc']) && $_GET['sc']) {
    $smt->commons->getSubcats(Tools::categoryUrldecode($_GET['sc']));
    $smt->database->updateCategoriesLocalFilesCount();
    print '</div>';
    $smt->includeFooter();
    return;
}

$orderBy = ' ORDER BY hidden ASC, local_files DESC, files DESC, name ASC ';

if (isset($_GET['g']) && $_GET['g']=='all') {
    Tools::notice('refresh Info for all categories');
    $toget = [];
    $cats = $smt->database->queryAsArray('SELECT * FROM category ' . $orderBy);
    foreach ($cats as $cat) {
        if ($cat['subcats'] != '' && $cat['files'] != '' && $cat['pageid'] != '') {
            continue;
        }
        if (sizeof($toget) == 50) { // @TODO split into blocks
            break;
        }
        $toget[] = $cat['name'];
    }
    $_GET['c'] = implode('|', $toget);
    //Tools::notice('refreshing: ' . $_GET['c']);
    $categoryInfo = $smt->commons->getCategoryInfo($_GET['c']);
    //Tools::debug('got categoryInfo: <pre>' . print_r($categoryInfo, true) . '</pre>');

    Tools::error('@TODO - import categoryInfo to DB');
    print '</div>';
    $smt->includeFooter();
    return;
}

///////////////////////////////////////////////////////////////////////////////

if (isset($_GET['sca']) && $_GET['sca']=='all') {
    $sql = 'SELECT * FROM category WHERE subcats > 0 ' . $orderBy;
    Tools::notice('SHOWING only categories with subcategories');
} elseif (isset($_GET['wf'])) {
    $sql = 'SELECT * FROM category WHERE files > 0 ' . $orderBy;
    Tools::notice('SHOWING only categories with files');
} elseif (isset($_GET['s'])) {
    $sql = 'SELECT * FROM category WHERE name LIKE :search ' . $orderBy;
    $bind = [':search'=>'%' . $_GET['s']. '%'];
    Tools::notice('SHOWING only categories with search text: ' . $_GET['s']);
} else {
    $sql = 'SELECT * FROM category ' . $orderBy;
}
if (!isset($bind)) {
    $bind = [];
}
$cats = $smt->database->queryAsArray($sql, $bind);

if (!is_array($cats)) {
    $cats = [];
}

$spacer = ' &nbsp; &nbsp; &nbsp; ';

print ''
. '<ul>'
. '<li><b>' . number_format($smt->database->getCategoriesCount()) . '</b> Active Categories</li>'
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

. '<a href="' . Tools::url('admin') . 'category.php?v=1">[View&nbsp;Category&nbsp;List]</a>'
. $spacer
. ' <a href="./sqladmin.php?table=category&action=row_create" target="sqlite">'
. ' [Manually&nbsp;add&nbsp;category]</a>'
. $spacer
. '<a href="' . Tools::url('admin') . 'category.php?g=all">[Import&nbsp;Category&nbsp;Info]</a>'
. '</p>';

if (($smt->database->getCategoriesCount() > 1000) && isset($_GET['v']) && ($_GET['v'] != 1)) {
    print '</div>';
    $smt->includeFooter();
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
    . '<td><b><a href="' . Tools::url('category') . '?c='
    . Tools::categoryUrlencode(Tools::stripPrefix($cat['name']))
    . '">' . Tools::stripPrefix($cat['name']) . '</a></b></td>';

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
    . '<td class="right">'
        . ($cat['files'] ? number_format($cat['files']) : '<span style="color:#ccc;">0</span>') . '</td>'
    ;
    if ($cat['subcats'] > 0) {
        $subcatslink = '<a href="./' . basename(__FILE__) . '?sc=' . Tools::categoryUrlencode($cat['name']) . '"">+'
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
        . Tools::categoryUrlencode($cat['name']) . '">View</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__)
        . '?c=' . Tools::categoryUrlencode($cat['name']) . '">Info</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./' . basename(__FILE__)
        . '?i=' . Tools::categoryUrlencode($cat['name']) . '">Import</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./media.php?dc='
        . Tools::categoryUrlencode($cat['name']) . '">Clear</a></td>'
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
$smt->includeFooter();


/**
 * @param TaggerAdmin $smt
 */
function getSearchResults(TaggerAdmin $smt)
{
    $search = urldecode($_GET['scommons']);

    if (!$smt->commons->findCategories($search)) {
        Tools::notice('Error: no categories found');

        return;
    }
    $cats = isset($smt->commons->response['query']['search'])
        ? $smt->commons->response['query']['search']
        : null;
    if (!$cats || !is_array($cats)) {
        Tools::notice('Error: no categories returned');

        return;
    }
    print '<p>Searched "' . $search . '": showing <b>' . sizeof($cats) . '</b> of <b>'
        . $smt->commons->totalHits . '</b> categories</p>';
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

    foreach ($cats as $id => $cat) {
        print '<input type="checkbox" name="cats[]" value="' . urlencode($cat['title']) . '"><strong>'
        . $cat['title']
        . '</strong><small> '
        . '<a target="commons" href="https://commons.wikimedia.org/wiki/'
            . Tools::categoryUrlencode($cat['title']) . '">(view)</a> '
        . ' (' . $cat['snippet'] . ')'
        . ' (size:' . $cat['size'] . ')</small><br />';
    }
    print '</form>';
}
