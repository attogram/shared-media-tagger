<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Search View
 *
 * @var array $data
 */

?>
<div class="row bg-white">
    <div clas="col-12">
        <form class="form-inline">
            <input type="text"
                   class="form-control mt-3 mb-3 ml-3"
                   name="q"
                   maxlength="256"
                   value="<?=
                    !empty($data['query'])
                       ? htmlentities($data['query'])
                       : ''
                    ?>">
            <button type="submit" class="btn btn-primary mt-3 mb-3 ml-2 mr-2">Search</button>
            <?php if (!empty($_GET['q'])) {
                echo count($data['results']) ?> results
            <?php } ?>
        </form>
    </div>
</div>
<?php if (!empty($data['results'])) { ?>
<div class="row bg-white">
    <div clas="col-12">
        <?php
        foreach ($data['results'] as $media) {
            $this->smt->includeThumbnailBox($media);
        } ?>
    </div>
</div>
<?php } ?>
