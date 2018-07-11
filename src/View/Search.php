<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Search View
 *
 * @var array $data
 */

?>
<div class="white">
    <form method="GET">
        <input type="text" name="q" size="30" maxlength="256"
            value="<?=
                !empty($data['query'])
                    ? htmlentities($data['query'])
                    : ''
                ?>" />
        <input type="submit" value="  search  " />
    </form>
    <p><?= count($data['results']) ?> results</p>
    <?php
    if (!empty($data['results'])) {
        foreach ($data['results'] as $media) {
            $this->smt->includeThumbnailBox($media);
        }
    }
    ?>
</div>
