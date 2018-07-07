<?php
/**
 * Shared Media Tagger
 * Admin Media List Functions Template
 */
declare(strict_types = 1);

?>
<script type="text/javascript" language="javascript">
    function checkAll(formname, checktoggle) {
        var checkboxes = new Array();
        checkboxes = document[formname].getElementsByTagName('input');
        for (var i=0; i<checkboxes.length; i++) {
            if (checkboxes[i].type == 'checkbox') {
                checkboxes[i].checked = checktoggle;
            }
        }
    }
</script>
<div class="left pre white" style="display:inline-block;border:1px solid red;margin:2px;padding:2px;">
    <input type="submit" value="Delete selected media">'
    &nbsp;
    <a onclick="javascript:checkAll('media', true);" href="javascript:void();">check all</a>
    &nbsp;
    <a onclick="javascript:checkAll('media', false);" href="javascript:void();">uncheck all</a>
</div>
