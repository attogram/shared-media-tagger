<?php
/**
 * Shared Media Tagger
 * Thumbnail Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$thumb = $this->getThumbnail($this->media);

?>
<div class="d-inline-block align-top text-right m-1 p-1"
     style="background-color:#eee;">
    <a style="line-height:90%;" class="nohover" href="<?=
        Tools::url('info')
    ?>/<?=
        (!empty($this->media['pageid']) ? $this->media['pageid'] : null)
    ?>"><img src="<?=
            $thumb['url']
        ?>" width="<?=
            $thumb['width']
        ?>" height="<?=
            $thumb['height']
        ?>" title="<?=
            htmlentities((string) (!empty($this->media['title']) ? $this->media['title'] : ''))
        ?>" />
    <br >
    <div style="font-size:65%;">
        <?= $this->getMediaName($this->media, 25) ?>
        <br />
        &copy; <?= $this->getArtistName($this->media, 22) ?>
        <br />
        <?= $this->getLicenseName($this->media, 26) ?>
    </div>
    <?php $this->includeAdminMediaFunctions($this->media['pageid']); ?>
</div>
