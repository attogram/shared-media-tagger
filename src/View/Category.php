<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Category
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var array $categoryInfo
 * @var string $categoryName
 * @var string $categoryNameDisplay
 * @var int|string $categorySize
 * @var string $pager
 * @var string $reviewsPerCategory
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="box white">
    <div style="float:right; padding:0 20px 4px 0; font-size:80%;">
        <?= $reviewsPerCategory ?>
    </div>
    <h1><?= $categoryNameDisplay ?></h1>
    <br />
    <b><?= $categorySize ?></b> files
    <?= $pager ? ', '.$pager : '' ?>
    <br clear="all" />
    <?php
    if (Tools::isAdmin()) {
        print '<form action="' . Tools::url('admin')
            . 'media" method="GET" name="media">';
    }

    foreach ($category as $media) {
        print $this->smt->displayThumbnailBox($media);
    }

    if ($pager) {
        print '<p>' . $pager . '</p>';
    }

    if (Tools::isAdmin()) {
        print $this->smt->displayAdminCategoryFunctions($categoryName);
    }
    ?>
</div>
