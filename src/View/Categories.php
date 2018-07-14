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
<div class="row bg-white">
    <div class="col-12">
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
                <input type="submit" value="filter">
            </form>
        </div>

        <?= $pager ?>

        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th scope="col">Files</th>
                    <th scope="col">Category</th>
                </tr>
            </thead>
            <tbody>
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
                ?>
                <tr data-href="<?= $localUrl ?>">
                    <th scope="row"><?=  number_format((float) $category['local_files']) ?></th>
                    <td><a href="<?= $localUrl ?>"><?= Tools::stripPrefix($category['name']) ?></a></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
        <p>
            <?= $pager ?>
        </p>
        <p class="center" style="padding:10px;">
            <a href="<?= Tools::url('categories') ?>">Active Categories</a>
            -  <a href="<?= Tools::url('categories') ?>?h=1">Technical Categories</a>
        </p>
    </div>
</div>
<script>
$(document).ready(function($){
    $('tr').click(function() {
        window.document.location = $(this).data('href');
    })
});
</script>
