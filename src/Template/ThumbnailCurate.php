<?php
/**
 * Shared Media Tagger
 * Thumbnail Template
 *
 * @var array $data
 */
declare(strict_types = 1);

$thumb = $this->getThumbnail($data);

?>
<div class="d-inline-block align-top text-center">
    <input type="checkbox" name="m[]" value="<?= $data['pageid'] ?>" class="form-check-input" />
    <br />
    <a target="commons"
       href="<?=$data['descriptionurl'] ?>"><img
            class="border"
            src="<?=
            $thumb['url']
        ?>" width="<?=
            $thumb['width']
        ?>" height="<?=
            $thumb['height']
        ?>" title="<?=
            $data['pageid']
            . ' - ' . $data['width'] . ' x ' . $data['height'] . ' px'
            . ' - ' . $data['mime']
            . ' - ' . $data['size'] . ' bytes'
            . "\n"
            . htmlentities((string) (!empty($data['title']) ? $data['title'] : ''))
            . "\n"
            . htmlentities((string) (!empty($data['imagedescription']) ? $data['imagedescription'] : ''))
    ?>" /></a>
    <div style="font-size:65%;">
        <?= $this->getMediaName($data, 27) ?>
    </div>
</div>
