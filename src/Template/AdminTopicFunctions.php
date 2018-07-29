<?php
/**
 * Shared Media Tagger
 * Admin Topic Functions Template
 */
declare(strict_types = 1);

?>
<br clear="all" />
<hr />
<p>
    Topic Admin
</p>
<?php $this->includeTemplate('AdminTopicInfo', $this->topic); ?>
<br />
<p>
    <a onclick="javascript:checkAll('media', true);" href="javascript:void();">check all media</a>
    &nbsp; &nbsp;
    <a onclick="javascript:checkAll('media', false);" href="javascript:void();">uncheck all media</a>
    &nbsp; &nbsp;
    <input type="submit" value="Delete selected media">
</p>
</form>
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
