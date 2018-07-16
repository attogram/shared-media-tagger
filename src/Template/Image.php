<?php
/**
 * Shared Media Tagger
 * Image Template
 *
 * @var array $data
 */
declare(strict_types = 1);

?>
<img class="img-fluid"
     src="<?= $data['displayUrl'] ?>"
     width="<?= $data['thumbwidth'] ?>"
     height="<?= $data['thumbheight'] ?>"
     alt="">
<div style="font-size:70%;">
    &copy; <?= $this->getArtistName($data, 77) ?>
    /
    <?= $this->getLicenseName($data, 77) ?>
</div>
