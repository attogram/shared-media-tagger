<?php
/**
 * Shared Media Tagger
 * Tag Bar Template
 *
 * @var object $this
 */
declare(strict_types = 1);

?>
<div style="font-size:270%;">
<?php
foreach ($this->tags as $tag) {
    ?><div class="d-inline"><a class="pl-1 pr-1" href="<?= $tag['link'] ?>"
        title="<?= $tag['name'] ?>"><?= $tag['display_name'] ?></a></div><?php
}
?>
</div>
