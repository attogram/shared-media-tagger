<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Curation Admin
 *
 * @var array $data
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<style>
.curation_container { background-color:#ddd; color:black; padding:10px; display:flex; flex-wrap:wrap; }
.curation_container img { margin:1px; }
.curation_keep { border:12px solid green; }
.curation_delete { border:12px solid red; }
.curation_que { border:12px solid grey; }
</style>
<div class="row bg-secondary">
    <div class="col-12 mb-4">
        <form name="media" action="" method="GET">
            <?= $data['menu'] ?>
            <div class="curation_container">
        <?php

        foreach ($data['medias'] as $media) {
            $thumb = $this->smt->getThumbnail($media);
            $url = $thumb['url'];
            $width = $thumb['width'];
            $height = $thumb['height'];
            $pageid = $media['pageid'];
            $imgInfo = str_replace(
                "Array\n(",
                '',
                htmlentities((string) print_r($media, true))
            );
            print '<div>'
                . '<a target="site" style="font-size:10pt; text-align:center;" href="'
                . Tools::url('info') . '/' . $pageid . '">' . $pageid . '</a><br />'
                . '<img name="' . $pageid . '" id="' . $pageid.'"  src="' . $url . '"'
                . ' width="' . $width . '" height="' . $height . '" title="'
                . $imgInfo . '" onclick="curation_click(this.id);" class="curation_que">'
                . '</div>'
                . '<input style="display:none;" type="checkbox" name="keep[]" id="keep' . $pageid
                . '" value="' . $pageid . '">'
                . '<input style="display:none;" type="checkbox" name="delete[]" id="delete' . $pageid
                . '" value="' . $pageid . '">';
        }

        ?>
        <br />
        <?= $data['menu'] ?>
    </div>

        </form>
    </div>
</div>
<script>
function mark_all_keep() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', true);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', false);
    });
    $("img").each( function() {
        $(this).prop('class','curation_keep');
    });
}
function mark_all_delete() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', false);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', true);
    });
    $("img").each( function() {
        $(this).prop('class','curation_delete');
    });
}
function mark_all_que() {
    $(":checkbox[id^=keep]").each( function() {
        $(this).prop('checked', false);
    });
    $(":checkbox[id^=delete]").each( function() {
        $(this).prop('checked', false);
    });
    $("img").each( function() {
        $(this).prop('class','curation_que');
    });
}
function curation_click(pageid) {
    var media = $('#' + pageid);
    var media_keep = $('#keep' + pageid);
    var media_delete = $('#delete' + pageid);
    switch( media.prop('class') ) {
        case 'curation_que':
            media.prop('class', 'curation_delete');
            media_keep.prop('checked', false);
            media_delete.prop('checked', true);
            return;
        case 'curation_delete':
            media.prop('class', 'curation_keep');
            media_keep.prop('checked', true);
            media_delete.prop('checked', false);
            return;
        case 'curation_keep':
            media.prop('class', 'curation_que');
            media_keep.prop('checked', false);
            media_delete.prop('checked', false);
            return;
    }
}
</script>
