<?php
/**
 * Shared Media Tagger
 * Browse all
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var object $this
 * @var array $medias
 * @var string|int $resultSize
 * @var string $extra
 * @var string $pager
 * @var int|string $extraNumberformat
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col">
    <form class="form-inline">
        Browse Files -
        <select name="s">
            <option value="random" <?= Tools::isSelected('random', $this->sort) ?>>Random</option>
            <option value="pageid" <?= Tools::isSelected('pageid', $this->sort) ?>>ID</option>
            <option value="size" <?= Tools::isSelected('size', $this->sort) ?>>Size</option>
            <option value="title" <?= Tools::isSelected('title', $this->sort) ?>>File Name</option>
            <option value="mime" <?= Tools::isSelected('mime', $this->sort) ?>>Mime Type</option>
            <option value="width" <?= Tools::isSelected('width', $this->sort) ?>>Width</option>
            <option value="height" <?= Tools::isSelected('height', $this->sort) ?>>Height</option>
            <option value="datetimeoriginal" <?=
                Tools::isSelected('datetimeoriginal', $this->sort) ?>>Original Datetime</option>
            <option value="timestamp" <?= Tools::isSelected('timestamp', $this->sort) ?>>Upload Datetime</option>
            <option value="updated" <?= Tools::isSelected('updated', $this->sort) ?>>Last Refreshed</option>
            <option value="licenseuri" <?= Tools::isSelected('licenseuri', $this->sort) ?>>License URI</option>
            <option value="licensename" <?= Tools::isSelected('licensename', $this->sort) ?>>License Name</option>
            <option value="licenseshortname" <?=
                Tools::isSelected('licenseshortname', $this->sort) ?>>License Short Name</option>
            <option value="usageterms" <?= Tools::isSelected('usageterms', $this->sort) ?>>Usage Terms</option>
            <option value="attributionrequired" <?=
            Tools::isSelected('attributionrequired', $this->sort) ?>>Attribution Required</option>
            <option value="restrictions" <?= Tools::isSelected('restrictions', $this->sort) ?>>Restrictions</option>
            <option value="user" <?= Tools::isSelected('user', $this->sort) ?>>Uploading User</option>
            <option value="duration" <?= Tools::isSelected('duration', $this->sort) ?>>Duration</option>
            <option value="sha1" <?= Tools::isSelected('sha1', $this->sort) ?>>Sha1 Hash</option>
        </select>
        <select name="d">
            <option value="d" <?= Tools::isSelected('d', $this->direction) ?>>Descending</option>
            <option value="a" <?= Tools::isSelected('a', $this->direction) ?>>Ascending</option>
        </select>
        <input type="submit" value="Browse" />
    </form>
    <?= count($medias) ?> <?=
        $this->sort == 'random'
            ? '<a href="">Random</a> '
            : ''
        ?>Files<?=
            ($pager ? ', ' . $pager : '')
        ?>
<?php
if (Tools::isAdmin()) {
    print '<form action="' . Tools::url('admin') . '/media" method="GET" name="media">';
}
?>
    <br clear="all" />
    <?php
    foreach ($medias as $media) {
        if (isset($extra)) {
            print '<div style="display:inline-block;">'
                . '<span style="background-color:#eee; border:1px solid #f99; font-size:80%;">';

            if (isset($extraNumberformat)) {
                print number_format((float) $media[$extra]);
            } else {
                print $media[$extra];
            }
            print '</span><br />';
        }
        $this->smt->includeTemplate('Thumbnail', $media);
        if (isset($extra)) {
            print '</div>';
        }
    }
    ?>
    <br clear="all" />
    <?php
    if ($pager) {
        print '<p>' . $pager . '</p>';
    }

    if (Tools::isAdmin()) {
        $this->smt->includeTemplate('AdminMediaListFunctions');
        print '</form>';
    }
    ?>
    </div>
</div>
