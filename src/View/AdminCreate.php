<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Create Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 * @var string $dataUrl
 * @var int|string $montageWidth
 * @var int|string $montageHeight
 * @var int|string $footerHeight
 * @var array $images
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="box white">
    <p>
        <a href="create">Create</a>
    </p>
    <ul>
        <li>Montage 100x100, 2x2: <a href="create?montage=1&amp;t=R">Random Images</a></li>
        <?php
        foreach ($this->smt->database->getTags() as $tag) {
            print '<li>Montage 100x100, 2x2: <a href="create?montage=1&amp;t='
                . $tag['id'] . '">Images tagged: ' . $tag['name'] . '</a></li>';
        }
        ?>
    </ul>

    <p>
        <img src="<?= $dataUrl ?>" usemap="#montage"
             width="<?= $montageWidth ?>" height="<?= ($montageHeight + $footerHeight) ?>">
    </p>

    <p>
        <b><?= sizeof($images) ?></b> images used in this montage:
        <br />
        <?php
        $count = 0;
        $areas = $descs = [];
        $areas[1] = $areas[2] = $areas[3] = $areas[4] = $descs[1] = $descs[2] = $descs[3] = $descs[4] = '';
        foreach ($images as $image) {
            $count++;
            $areas[$count] = Tools::url('info') . '?i=' . $image['pageid'];
            $descs[$count] = htmlspecialchars(Tools::stripPrefix($image['title']))
                . "\n" . $this->smt->displayLicensing($image);
            print '<br />#' . $count . ': '
            . '<a href="' . Tools::url('info') . '?i=' . $image['pageid'] . '">'
            . htmlspecialchars(Tools::stripPrefix($image['title'])) . '</a>'
            . ' - ' . $this->smt->displayLicensing($image)
            ;
        }
        ?>
    </p>
    <p>
        Data URL:
        <br />
        <textarea cols="80" rows="20"><?= $dataUrl ?></textarea>
    </p>
    <p>
        HTML map:
        <br />
        <textarea cols="80" rows="10">
<map name="montage">
    <area shape="rect" coords="0,0,50,50" href="<?= $areas[1] ?>" title="#1: <?= $descs[1] ?>">
    <area shape="rect" coords="50,0,100,50" href="<?= $areas[2] ?>" title="#2: <?= $descs[2] ?>">
    <area shape="rect" coords="0,50,50,100" href="<?= $areas[3] ?>" title="#3: <?= $descs[3] ?>">
    <area shape="rect" coords="50,50,100,100" href="<?= $areas[4] ?>" title="#4: <?= $descs[4] ?>">
</map>
        </textarea>
    </p>
</div>

<map name="montage">
    <area shape="rect" coords="0,0,50,50" href="<?= $areas[1] ?>" title="#1: <?= $descs[1] ?>">
    <area shape="rect" coords="50,0,100,50" href="<?= $areas[2] ?>" title="#2: <?= $descs[2] ?>">
    <area shape="rect" coords="0,50,50,100" href="<?= $areas[3] ?>" title="#3: <?= $descs[3] ?>">
    <area shape="rect" coords="50,50,100,100" href="<?= $areas[4] ?>" title="#4: <?= $descs[4] ?>">
</map>
