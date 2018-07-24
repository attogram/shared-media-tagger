<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Topic Admin
 *
 * @var TaggerAdmin smt
 * @var string $spacer
 * @var array $cats
 */

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col mb-4">
<form action="" method="GET">
<p>
    <input type="hidden" name="v" value="1">
    <input type="text" name="s" value="" size="20">
    <input type="submit" value="   Search LOCAL Topics   ">
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>/topic?v=1">[View&nbsp;Topic&nbsp;List]</a>
</p>
</form>
<?php
if (($this->smt->database->getTopicsCount() > 1000)
    && isset($_GET['v']) && ($_GET['v'] != 1)
) {
    print '</div>';
    $this->smt->includeFooter();
    Tools::shutdown();
}
?>
<table border="1">
    <tr style="background-color:lightblue;font-style:italic;">
        <td>Topic:</td>
        <td><small>Loc<br />files</small></td>
        <td><small><a href="?wf=1">Com<br/>files</a></small></td>
        <td><small><a href="?sca=all">Sub<br />cats</a></small></td>
        <td>view</td>
        <td>refresh</td>
        <td>import</td>
        <td>Clear</td>
        <td>Delete</td>
    </tr>
<?php

reset($cats);
$commonFilesCount = $localFilesCount = 0;
foreach ($cats as $cat) {
    $commonFilesCount += $cat['files'];
    print '<tr>'
    . '<td><b><a href="' . Tools::url('topic') . '/'
    . Tools::topicUrlencode(Tools::stripPrefix($cat['name']))
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
        . ($cat['files'] ? number_format((float) $cat['files']) : '<span style="color:#ccc;">0</span>') . '</td>'
    ;
    if ($cat['subcats'] > 0) {
        $subcatslink = '<a href="?sc=' . Tools::topicUrlencode($cat['name']) . '"">+'
        . $cat['subcats'] . '</a>';
    } else {
        $subcatslink = '';
        if ($cat['pageid'] > 0) {
            $subcatslink = '<span style="color:#ccc;">0</span>';
        }
    }
    print '<td class="right">' . $subcatslink . '</td>';

    print ''
    . '<td style="padding:0 10px 0 10px;"><a target="c" href="https://commons.wikimedia.org/wiki/'
        . Tools::topicUrlencode($cat['name']) . '">View</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="'
        . Tools::url('admin') . '/add/?s=topic&amp;t' . $cat['pageid'] . '=on">'
        . 'Refresh</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="?i=' . Tools::topicUrlencode($cat['name']) . '">Import</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./media?dc='
        . Tools::topicUrlencode($cat['name']) . '">Clear</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="?d=' . urlencode($cat['id']) . '">Delete</a></td>'
    . '</tr>'
    ;
}

?>
    </table>
    <br />
    <b><?= $localFilesCount ?></b> Files Under Review
    <br />
    <b><?= $commonFilesCount ?></b> Total Files on Commons
    </div>
</div>
