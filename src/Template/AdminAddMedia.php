<?php
/**
 * @var $data
 */
declare(strict_types = 1);

?>
<form>
<div class="row bg-white pb-3">
    <div class="col">
        <?php
        foreach ($data as $media) {
            $this->includeTemplate('ThumbnailCurate', $media);
        }
        ?>
    </div>
</div>
<div class="row bg-info text-center">
    <div class="col">
        <button type="submit" class="btn btn-dark mt-3 mb-3 ml-2 mr-2">Import checked Media</button>
    </div>
</div>
</form>
