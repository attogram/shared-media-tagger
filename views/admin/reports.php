<?php
/**
 * Shared Media Tagger
 * Reports Admin
 *
 * @var \Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin;

if (function_exists('set_time_limit')) {
    set_time_limit(1000);
}

$smt->title = 'Admin Reports';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();
print '<div class="box white"><p><a href="' . $smt->url('admin') .'reports.php">' . $smt->title . '</a></p>
<ul>
<li><a href="' . $smt->url('admin') . 'reports.php?r=localfiles">update_categories_local_files_count()</a>
<br /><br />
<li><a href="' . $smt->url('admin') . 'reports.php?r=category2media">Check: category2media</a>
<br /><br />
<li><a href="' . $smt->url('admin') . 'reports.php?r=catclean">Check/Clean: category</a></li>
</ul>
<hr />';


switch (@$_GET['r']) {
    default:
        print '<p>Please choose a report above</p>';
        break;
    case 'localfiles':
        $smt->updateCategoriesLocalFilesCount();
        break;
    case 'catclean':
        catClean($smt);
        break;
    case 'category2media':
        category2media($smt);
        break;
} // end switch

print '</div>';
$smt->includeFooter();

/**
 * @param SharedMediaTaggerAdmin $smt
 */
function category2media(SharedMediaTaggerAdmin $smt)
{
    $c2ms = $smt->queryAsArray('SELECT * FROM category2media');
    print '<p>' . number_format(sizeof($c2ms)) . ' category2media</p>';

    $categoriesRaw = $smt->queryAsArray('SELECT id FROM category');
    print '<p>' . number_format(sizeof($categoriesRaw)) . ' Categories</p>';
    $categories = [];
    foreach ($categoriesRaw as $cats) {
        $categories[$cats['id']] = true;
    }

    $mediaRaw = $smt->queryAsArray('SELECT pageid FROM media');
    print '<p>' . number_format(sizeof($mediaRaw)) . ' Media</p>';
    $media = [];
    foreach ($mediaRaw as $med) {
        $media[$med['pageid']] = true;
    }

    $checked = 0;
    $errors = [];
    print '<pre>';
    foreach ($c2ms as $c2m) {
        $checked++;
        if (!isset($categories[$c2m['category_id']])) {
            $errors[] = $c2m['id'];
            print '<br />c2m_id:' . $c2m['id'] . ' CATEGORY NOT FOUND'
            . ' c:' . $c2m['category_id']
            . ' m:' . $c2m['media_pageid'];
        }
        if (!isset($media[$c2m['media_pageid']])) {
            $errors[] = $c2m['id'];
            print '<br />c2m_id:' . $c2m['id'] . ' MEDIA NOT FOUND'
            . ' c:' . $c2m['category_id']
            . ' m:' . $c2m['media_pageid'];
        }
    }
    print '</pre>';
    print '<p>' . number_format($checked) . ' checked</p>';
    print '<p>' . number_format(sizeof($errors)) . ' ERRORS</p>';

    $sql = 'DELETE FROM category2media WHERE id IN ( '
        . implode($errors, ', ') . ' );';
    print '<p>'.$sql.'</p>';
}

/**
 * @param SharedMediaTaggerAdmin $smt
 */
function catClean(SharedMediaTaggerAdmin $smt)
{
    $tab = " \t ";

    $checkerLimit = 25;
    if (isset($_GET['checker']) && $_GET['checker']) {
        $checkerLimit = (int) $_GET['checker'];
    }

    print '<p>Clean Category Table:</p>'
    . '<p><a href="?r=catclean&amp;cleaner=1">RUN CLEANER</a> (updates: local_files, sanitizes: hidden, missing.  No API calls.)</p>'
    . '<p><a href="?r=catclean&amp;checker=' . $checkerLimit . '">RUN CATEGORY-INFO CHECKER x'
    . $checkerLimit. '</a>  (updates ALL category info.  Remote API calls.)</p>';

    if (isset($_GET['cleaner'])) {
        $categories = $smt->queryAsArray('SELECT * FROM category');
        //print '<p>START: CLEANER</p>';
        $smt->beginTransaction();
        $result = '';
        foreach ($categories as $category) {
            //$result .= ' ' . $category['id'];
            $bind = array();
            $bind[':local_files'] = $smt->getCategorySize($category['name']);
            $bind[':hidden'] = 0;
            if ($category['hidden'] == 1) {
                $bind[':hidden'] = 1;
            }
            $bind[':missing'] = 0;
            if ($category['missing'] == 1) {
                $bind[':missing'] = 1;
            }
            $bind[':id'] = $category['id'];
            $upd = $smt->queryAsBool('UPDATE category SET
                    local_files = :local_files,
                    hidden = :hidden,
                    missing = :missing
                    WHERE id = :id', $bind);
            if ($upd) {
                continue;
            }
            $result .= '<span style="color:red;">ERR:' . $category['id'] . '</span>';
        }
        $smt->commit();
        $smt->vacuum();
        print '<p>OK: RAN: CLEANER: <span style="font-size:80%;">' . $result . '</span></p>';
    }

    if (isset($_GET['checker'])) {
        $categories = $smt->queryAsArray(
            'SELECT * FROM category ORDER BY updated ASC LIMIT ' . $checkerLimit
        );
        //print '<p>START: CATEGORY-INFO CHECKER x' . $checker_limit . '</p>';
        $smt->beginTransaction();
        $result = '';
        foreach ($categories as $category) {
            $result .= ' ' . $category['id'];
            if ($smt->saveCategoryInfo($category['name'])) {
                continue;
            }
            $result .= '<span style="color:red;">ERR:' . $category['id'] . '</span>';
        }
        $smt->commit();
        $smt->vacuum();
        print '<p>OK: RAN: CATEGORY-INFO CHECKER: <span style="font-size:80%;">' . $result . '</span></p>';
    }

    $categories = $smt->queryAsArray(
        'SELECT * FROM category ORDER BY hidden ASC, local_files DESC, name ASC'
    );
    print '<p><b>' . number_format(sizeof($categories)) . '</b> Categories</p>';

    print '<pre>'
    . '<b>LOCAL' . $tab
    . 'COM' . $tab
    . 'H M ID' . $tab
    . 'Last Updated' . $tab . $tab
    . 'Category</b><br />';
    foreach ($categories as $category) {
        print ''
        . number_format($category['local_files']) . $tab
        . number_format($category['files']) . $tab
        . $category['hidden'] . ' '
        . $category['missing'] . ' '
        . $category['id'] . $tab
        . ($category['updated'] ? $category['updated'] : '0000-00-00 00:00:00') . $tab
        . '<a target="site" href="' . $smt->url('category') . '?c='
        . $smt->categoryUrlencode($smt->stripPrefix($category['name']))
        . '">' . $category['name'] . '</a>'
        . '<br />';
    }
    print '<br />END or report.</pre>';
}
