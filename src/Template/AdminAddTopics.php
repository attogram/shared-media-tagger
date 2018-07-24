<?php
/**
 * @var $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<form>
<div class="row bg-info text-center">
<div class="col">
    <button type="submit" class="btn btn-dark mt-3 mb-3 ml-2 mr-2">Import checked Topics</button>
</div>
</div>
<div class="row bg-info text-white small font-italic">
    <div class="col-12 col-sm-6 font-weight-bold">Topic</div>
    <div class="col-4 col-sm-2">Add</div>
    <div class="col-4 col-sm-2">Add Media</div>
    <div class="col-4 col-sm-2">Add Subcats</div>
</div>
<input type="hidden" name="s" value="topics" />
<?php foreach ($data as $topic) { ?>
<div class="row border border-light bg-white hovery">
    <div class="col-12 col-sm-6 font-weight-bold">
        <a target="commons" href="https://commons.wikimedia.org/wiki/<?=
            Tools::topicUrlencode($topic['title'])
            ?>"><?= Tools::stripPrefix($topic['title']) ?></a>
    </div>
    <div class="col-4 col-sm-2">
        <div class="form-check form-check-inline font-weight-bold">
            <input type="checkbox" name="t<?= $topic['pageid'] ?>" />
        </div>
    </div>
    <div class="col-4 col-sm-2">
        <?php if (!empty($topic['files'])) { ?>
        <div class="form-check form-check-inline">
            <input type="checkbox" name="a<?= $topic['pageid'] ?>" />
            <?= $topic['files'] ?>
        </div>
        <?php } ?>
    </div>
    <div class="col-4 col-sm-2">
        <?php if (!empty($topic['subcats'])) { ?>
        <div class="form-check form-check-inline">
            <input type="checkbox" name="b<?= $topic['pageid'] ?>" />
            <?= $topic['subcats'] ?>
        </div>
        <?php } ?>
    </div>
</div>
<?php } ?>
<div class="row bg-info text-white small font-italic">
    <div class="col-12 col-sm-6 font-weight-bold">Topic</div>
    <div class="col-4 col-sm-2">Add</div>
    <div class="col-4 col-sm-2">Add Media</div>
    <div class="col-4 col-sm-2">Add Subcats</div>
</div>
<div class="row bg-info text-center">
    <div class="col">
        <button type="submit" class="btn btn-dark mt-3 mb-3 ml-2 mr-2">Import checked Topics</button>
    </div>
</div>
</form>
