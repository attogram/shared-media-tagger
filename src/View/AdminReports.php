<?php
/**
 * Shared Media Tagger
 * Reports Admin
 *
 * @var \Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

if (function_exists('set_time_limit')) {
    set_time_limit(1000);
}

$smt->title = 'Admin Reports';
$smt->includeHeader();
$smt->includeTemplate('Menu');
$smt->includeTemplate('AdminMenu');
?>

<div class="row bg-white">
    <div class="col mb-4">
        <p>
            <a href="<?= Tools::url('admin') ?>/reports"><?= $smt->title ?></a>
        </p>
        <ul>
            <li><a href="<?= Tools::url('admin') ?>/reports?r=localfiles">update_topics_local_files_count()</a></li>
            <li><a href="<?= Tools::url('admin') ?>/reports?r=category2media">Check: category2media</a>
            <li><a href="<?= Tools::url('admin') ?>/reports?r=catclean">Check/Clean: category</a></li>
        </ul>
        <hr />
<?php

if (!isset($_GET['r'])) {
    print '</div></div>';
    $smt->includeFooter();
    exit;
}

switch ($_GET['r']) {
    default:
        print '<p>Please choose a report above</p>';
        break;
    case 'localfiles':
        $smt->database->updateTopicsLocalFilesCount();
        break;
    case 'catclean':
        catClean($smt);
        break;
    case 'category2media':
        category2media($smt);
        break;
} // end switch

print '</div></div>';
$smt->includeFooter();


/**
 * @param TaggerAdmin $smt
 */
function category2media(TaggerAdmin $smt)
{
    $c2ms = $smt->database->queryAsArray('SELECT * FROM category2media');
    print '<p>' . number_format((float) sizeof($c2ms)) . ' category2media</p>';

    $topicsRaw = $smt->database->queryAsArray('SELECT id FROM category');
    print '<p>' . number_format((float) sizeof($topicsRaw)) . ' Topics</p>';
    $topics = [];
    foreach ($topicsRaw as $cats) {
        $topics[$cats['id']] = true;
    }

    $mediaRaw = $smt->database->queryAsArray('SELECT pageid FROM media');
    print '<p>' . number_format((float) sizeof($mediaRaw)) . ' Media</p>';
    $media = [];
    foreach ($mediaRaw as $med) {
        $media[$med['pageid']] = true;
    }

    $checked = 0;
    $errors = [];
    print '<pre>';
    foreach ($c2ms as $c2m) {
        $checked++;
        if (!isset($topics[$c2m['category_id']])) {
            $errors[] = $c2m['id'];
            print '<br />c2m_id:' . $c2m['id'] . ' TOPIC NOT FOUND'
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
    print '<p>' . number_format((float) $checked) . ' checked</p>';
    print '<p>' . number_format((float) sizeof($errors)) . ' ERRORS</p>';

    $sql = 'DELETE FROM category2media WHERE id IN ( '
        . implode($errors, ', ') . ' );';
    print '<p>'.$sql.'</p>';
}

/**
 * @param TaggerAdmin $smt
 */
function catClean(TaggerAdmin $smt)
{
    $tab = " \t ";

    $checkerLimit = 25;
    if (isset($_GET['checker']) && $_GET['checker']) {
        $checkerLimit = (int) $_GET['checker'];
    }

    print '<p>Clean Topic Table:</p>'
    . '<p><a href="?r=catclean&amp;cleaner=1">RUN CLEANER</a>'
        . ' (updates: local_files, sanitizes: hidden, missing.  No API calls.)</p>'
    . '<p><a href="?r=catclean&amp;checker=' . $checkerLimit . '">RUN TOPIC-INFO CHECKER x'
    . $checkerLimit. '</a>  (updates ALL category info.  Remote API calls.)</p>';

    if (isset($_GET['cleaner'])) {
        $topics = $smt->database->queryAsArray('SELECT * FROM category');
        //print '<p>START: CLEANER</p>';
        $smt->database->vacuum();
        $result = '';
        foreach ($topics as $topic) {
            //$result .= ' ' . $topic['id'];
            $bind = [];
            $bind[':local_files'] = $smt->database->getTopicSize($topic['name']);
            $bind[':hidden'] = 0;
            if ($topic['hidden'] == 1) {
                $bind[':hidden'] = 1;
            }
            $bind[':missing'] = 0;
            if ($topic['missing'] == 1) {
                $bind[':missing'] = 1;
            }
            $bind[':id'] = $topic['id'];
            $upd = $smt->database->queryAsBool('UPDATE category SET
                    local_files = :local_files,
                    hidden = :hidden,
                    missing = :missing
                    WHERE id = :id', $bind);
            if ($upd) {
                continue;
            }
            $result .= '<span style="color:red;">ERR:' . $topic['id'] . '</span>';
        }
        $smt->database->commit();
        $smt->database->vacuum();
        print '<p>OK: RAN: CLEANER: <span style="font-size:80%;">' . $result . '</span></p>';
    }

    if (isset($_GET['checker'])) {
        $topics = $smt->database->queryAsArray(
            'SELECT * FROM category ORDER BY updated ASC LIMIT ' . $checkerLimit
        );
        //print '<p>START: TOPIC-INFO CHECKER x' . $checker_limit . '</p>';
        $smt->database->vacuum();
        $result = '';
        foreach ($topics as $topic) {
            $result .= ' ' . $topic['id'];
            if ($smt->database->saveTopicInfo($topic['name'])) {
                continue;
            }
            $result .= '<span style="color:red;">ERR:' . $topic['id'] . '</span>';
        }
        $smt->database->commit();
        $smt->database->vacuum();
        print '<p>OK: RAN: TOPIC-INFO CHECKER: <span style="font-size:80%;">' . $result . '</span></p>';
    }

    $topics = $smt->database->queryAsArray(
        'SELECT * FROM category ORDER BY hidden ASC, local_files DESC, name ASC'
    );
    print '<p><b>' . number_format((float) sizeof($topics)) . '</b> Topics</p>';

    print '<pre>'
    . '<b>LOCAL' . $tab
    . 'COM' . $tab
    . 'H M ID' . $tab
    . 'Last Updated' . $tab . $tab
    . 'Topic</b><br />';
    foreach ($topics as $topic) {
        print ''
        . number_format((float) $topic['local_files']) . $tab
        . number_format((float) $topic['files']) . $tab
        . $topic['hidden'] . ' '
        . $topic['missing'] . ' '
        . $topic['id'] . $tab
        . ($topic['updated'] ? $topic['updated'] : '0000-00-00 00:00:00') . $tab
        . '<a target="site" href="' . Tools::url('topic') . '/'
        . Tools::topicUrlencode(Tools::stripPrefix($topic['name']))
        . '">' . $topic['name'] . '</a>'
        . '<br />';
    }
    print '<br />END or report.</pre>';
}
