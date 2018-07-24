<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Topics
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var bool $hidden
 * @var string $search
 * @var array $topics
 * @var string $pager
 * @var string $search
 */
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col">
        <div style="padding:10px 0 10px 0;float:right;">
            <form method="GET">
                <a href="<?php print Tools::url('topics'); ?>" style="font-size:80%;">Active</a> &nbsp;
                <a href="<?php print Tools::url('topics'); ?>?h=1" style="font-size:80%;">Tech</a> &nbsp;
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
                    <th scope="col">Topic</th>
                </tr>
            </thead>
            <tbody>
            <?php
            foreach ($topics as $topic) {
                if (!isset($topic['name'])) {
                    continue;
                }
                if (!isset($topic['local_files'])) {
                    $topic['local_files'] = 0;
                }
                $localUrl = Tools::url('topic') . '/'
                    . Tools::topicUrlencode(Tools::stripPrefix($topic['name']));
                ?>
                <tr data-href="<?= $localUrl ?>">
                    <th scope="row"><?=  number_format((float) $topic['local_files']) ?></th>
                    <td><a href="<?= $localUrl ?>"><?= Tools::stripPrefix($topic['name']) ?></a></td>
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
            <a href="<?= Tools::url('topics') ?>">Active Topics</a>
            -  <a href="<?= Tools::url('topics') ?>?h=1">Technical Topics</a>
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
