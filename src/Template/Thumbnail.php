<?php
/**
 * Shared Media Tagger
 * Thumbnail Template
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$thumb = $this->getThumbnail($data);

?>
<div class="d-inline-block align-top text-right m-1 p-1"
     style="background-color:#eee;">
    <a style="line-height:90%;" class="nohover" href="<?=
        Tools::url('info')
    ?>/<?=
        (!empty($data['pageid']) ? $data['pageid'] : null)
    ?>"><img src="<?=
            $thumb['url']
        ?>" width="<?=
            $thumb['width']
        ?>" height="<?=
            $thumb['height']
        ?>" title="<?=
            htmlentities((string) (!empty($data['title']) ? $data['title'] : ''))
        ?>" />
    <br >
    <div style="font-size:65%;">
        <?= $this->getMediaName($data, 25) ?>
        <br />
        &copy; <?= $this->getArtistName($data, 22) ?>
        <br />
        <?= $this->getLicenseName($data, 26) ?>
    </div>
    </a>
    <?php $this->includeAdminMediaFunctions($data['pageid']); ?>
</div>
