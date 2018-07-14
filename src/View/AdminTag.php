<?php
/**
 * Shared Media Tagger
 * Tag Admin
 *
 * @var TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\TaggerAdmin;
use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'Tag Admin';
$smt->includeHeader();
$smt->includeTemplate('Menu');
$smt->includeTemplate('AdminMenu');

$tags = $smt->database->getTags();
?>
<div class="row bg-white">
    <div class="col-12 mb-4">
        <?php
        if (isset($_GET['tagid']) && Tools::isPositiveNumber($_GET['tagid'])) {
            $smt->saveTag();
            $tags = $smt->database->getTags();
        }
        ?>
        <br />
        <em>Tags preview:</em>
        <div class="bg-secondary">
            <?php $smt->includeTags(0); ?>
        </div>

        <br />
        <em>Tags setup:</em>
        <table border="1" style="margin:0;">
            <tr>
                <th>Position</th>
                <th>Score</th>
                <th>Full format</th>
                <th>Tag format</th>
                <th></th>
            </tr>
            <?php foreach ($tags as $tag) { ?>
            <form action="" method="GET">
            <input type="hidden" name="tagid" value="<?= $tag['id'] ?>">
            <tr>
                <td>
                    <input name="position" value="<?= $tag['position'] ?>" size="1" />
                </td>
                <td>
                    <input name="score" value="<?= $tag['score'] ?>" size="1" />
                </td>
                <td>
                    <textarea name="name" rows="2" cols="30"><?=
                        htmlentities((string) $tag['name']) ?></textarea>
                </td>
                <td>
                    <textarea name="display_name" rows="2" cols="10"><?=
                        htmlentities((string) $tag['display_name']) ?></textarea>
                </td>
                <td>
                    <input type="submit" value="    Save Tag #<?= $tag['id'] ?>  ">
                </td>
            </tr>
            </form>
            <?php } ?>
        </table>
    </div>
</div>
<?php

$smt->includeFooter();
