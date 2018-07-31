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
