<?php
/**
 * Shared Media Tagger
 * Browse all
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var string $sort
 * @var string $dir
 * @var string $resultSize
 * @var array $medias
 * @var string $extra
 * @var string $pager
 * @var int|string $extraNumberformat
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

print '<div class="box white">';
print '<form>
Browse Files, sorty by <select name="s">
<option value="random"' . Tools::isSelected('random', $sort) . ' >Random</option>
<option value="pageid"' . Tools::isSelected('pageid', $sort) . '>ID</option>
<option value="size"' . Tools::isSelected('size', $sort) . '>Size</option>
<option value="title"' . Tools::isSelected('title', $sort) . '>File Name</option>
<option value="mime"' . Tools::isSelected('mime', $sort) . '>Mime Type</option>
<option value="width"' . Tools::isSelected('width', $sort) . '>Width</option>
<option value="height"' . Tools::isSelected('height', $sort) . '>Height</option>
<option value="datetimeoriginal"' . Tools::isSelected('datetimeoriginal', $sort) . '>Original Datetime</option>
<option value="timestamp"' . Tools::isSelected('timestamp', $sort) . '>Upload Datetime</option>
<option value="updated"' . Tools::isSelected('updated', $sort) . '>Last Updated</option>
<option value="licenseuri"' . Tools::isSelected('licenseuri', $sort) . '>License URI</option>
<option value="licensename"' . Tools::isSelected('licensename', $sort) . '>License Name</option>
<option value="licenseshortname"' . Tools::isSelected('licenseshortname', $sort) . '>License Short Name</option>
<option value="usageterms"' . Tools::isSelected('usageterms', $sort) . '>Usage Terms</option>
<option value="attributionrequired"' . Tools::isSelected('attributionrequired', $sort) . '>Attribution Required</option>
<option value="restrictions"' . Tools::isSelected('restrictions', $sort) . '>Restrictions</option>
<option value="user"' . Tools::isSelected('user', $sort) . '>Uploading User</option>
<option value="duration"' . Tools::isSelected('duration', $sort) . '>Duration</option>
<option value="sha1"' . Tools::isSelected('sha1', $sort) . '>Sha1 Hash</option>
</select>
<select name="d">
<option value="d"' . Tools::isSelected('d', $dir) . '>Descending</option>
<option value="a"' . Tools::isSelected('a', $dir) . '>Ascending</option>
</select>
<input type="submit" value="Browse" />
</form><br />' . number_format((float) $resultSize) . ' Files'
    . ($pager ? ', ' . $pager : '');

if (Tools::isAdmin()) {
    print '<form action="' . Tools::url('admin') . 'media.php" method="GET" name="media">';
    print $this->smt->displayAdminMediaListFunctions();
}

print '<br clear="all" />';

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
    print $this->smt->displayThumbnailBox($media);
    if (isset($extra)) {
        print '</div>';
    }
}

print '<br clear="all" />';

if (Tools::isAdmin()) {
    print $this->smt->displayAdminMediaListFunctions() . '</form>';
}

if ($pager) {
    print '<p>' . $pager . '</p>';
}

print '</div>';
