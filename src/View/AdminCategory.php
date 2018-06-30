<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Category Admin
 *
 * @var TaggerAdmin smt
 * @var string $spacer
 * @var array $cats
 */

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="box white">
<ul>
    <li><b><?=
        number_format((float) $this->smt->database->getCategoriesCount())
    ?></b> Active Categories</li>
    <li><b>?</b> Technical Categories</li>
    <li><b>?</b> Empty Categories</li>
</ul>
<p>
    <form action="" method="GET">
        <input name="scommons" type="text" size="35" value="">
        <input type="submit" value="   Find Categories on COMMONS  ">
    </form>
</p>
<p>
    <form action="" method="GET">
        <input type="hidden" name="v" value="1">
        <input type="text" name="s" value="" size="20">
        <input type="submit" value="   Search LOCAL Categories   ">
    </form>
    <br />
    <br />

    <a href="<?= Tools::url('admin') ?>category?v=1">[View&nbsp;Category&nbsp;List]</a>
    <?= $spacer ?>
    <a href="./sqladmin?table=category&action=row_create" target="sqlite">[Manually&nbsp;add&nbsp;category]</a>
    <?= $spacer ?>
    <a href="<?= Tools::url('admin') ?>category?g=all">[Import&nbsp;Category&nbsp;Info]</a>
</p>

<?php

if (($this->smt->database->getCategoriesCount() > 1000)
    && isset($_GET['v']) && ($_GET['v'] != 1)
) {
    print '</div>';
    $this->smt->includeFooter();
    Tools::shutdown();
}

?>
<table border="1">
    <tr style="background-color:lightblue;font-style:italic;">
        <td>Category:</td>
        <td><small>Loc<br />files</small></td>
        <td><small><a href="?wf=1">Com<br/>files</a></small></td>
        <td><small><a href="?sca=all">Sub<br />cats</a></small></td>
        <td>view</td>
        <td><a href="?g=all">info</a></td>
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
    . '<td><b><a href="' . Tools::url('category') . '/'
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
        . ($cat['files'] ? number_format((float) $cat['files']) : '<span style="color:#ccc;">0</span>') . '</td>'
    ;
    if ($cat['subcats'] > 0) {
        $subcatslink = '<a href="?sc=' . Tools::categoryUrlencode($cat['name']) . '"">+'
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
    . '<td style="padding:0 10px 0 10px;"><a href="?c='
        . Tools::categoryUrlencode($cat['name']) . '">Info</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="?i=' . Tools::categoryUrlencode($cat['name']) . '">Import</a></td>'
    . '<td style="padding:0 10px 0 10px;"><a href="./media?dc='
        . Tools::categoryUrlencode($cat['name']) . '">Clear</a></td>'
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
