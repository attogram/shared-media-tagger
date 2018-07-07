<?php
/**
 * Shared Media Tagger
 * Thumbnail Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$thumb = $this->getThumbnail($this->media);

$pageid = !empty($this->media['pageid']) ? $this->media['pageid'] : null;
$title = !empty($this->media['title']) ? $this->media['title'] : null;

?>
<div style="background-color:#eee;display:inline-block;text-align:center;vertical-align:top;margin:3px;padding:3px;">
    <div style="display:inline-block;text-align:center;">
        <a href="<?= Tools::url('info') ?>/<?= $pageid ?>">
        <img src="<?= $thumb['url'] ?>"
             width="<?= $thumb['width'] ?>"
             height="<?= $thumb['height'] ?>"
             title="<?= htmlentities((string) $title) ?>" /></a>
    </div>
    <?php
        print str_replace(' / ', '<br />', $this->displayAttribution($this->media, 17, 21));
    ?>
    <?php
        $this->includeAdminMediaFunctions($this->media['pageid']);
    ?>
</div>
