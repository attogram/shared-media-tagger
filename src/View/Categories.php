<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Categories
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var bool $hidden
 * @var string $search
 * @var array $categories
 * @var string $pager
 * @var string $search
 */
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="white">
<div style="padding:10px 0 10px 0;float:right;">
<form method="GET">
<a href="<?php print Tools::url('categories'); ?>" style="font-size:80%;">Active</a> &nbsp;
<a href="<?php print Tools::url('categories'); ?>?h=1" style="font-size:80%;">Tech</a> &nbsp;
<?php
if ($hidden) {
    print '<input type="hidden" name="h" value="1">';
}
?>
<input type="text" name="s"
       value="<?= $search ? htmlentities((string) urldecode($search)) : ''; ?>" size="16">
<input type="submit" value="search">
</form>
</div>
<?= $pager ?>
<div class="cattable">
<div class="catcon">
<div class="catfiles cathead">Files</div>
<div class="catname cathead">Category</div>
</div>
<?php
foreach ($categories as $category) {
    if (!isset($category['name'])) {
        continue;
    }
    if (!isset($category['local_files'])) {
        $category['local_files'] = 0;
    }
    $localUrl = Tools::url('category') . '/'
        . Tools::categoryUrlencode(Tools::stripPrefix($category['name']));
    print '<div class="catcon">'
    . '<div class="catfiles">' . number_format((float) $category['local_files']) . '</div>'
    . '<div class="catname" onclick="window.location=\'' . $localUrl . '\'">'
    . '<a href="' . $localUrl . '">' . Tools::stripPrefix($category['name']) . '</a>'
    . '</div></div>';
}
?>
</div>
<br /><?= $pager ?>
<br /><br />
<p class="center" style="padding:10px;">
<a href="<?= Tools::url('categories') ?>">Active Categories</a>
 -  <a href="<?= Tools::url('categories') ?>?h=1">Technical Categories</a>
</p><br /><br />
</div>
