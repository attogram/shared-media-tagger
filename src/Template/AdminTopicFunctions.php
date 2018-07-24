<?php
/**
 * Shared Media Tagger
 * Admin Topic Functions Template
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

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
<br clear="all" />
<div class="left pre white" style="display:inline-block; border:1px solid red; padding:10px;">
    <input type="submit" value="Delete selected media">
    &nbsp;
    <a onclick="javascript:checkAll('media', true);" href="javascript:void();">check all</a>
    &nbsp; &nbsp;
    <a onclick="javascript:checkAll('media', false);" href="javascript:void();">uncheck all</a>
    <br />
    <br />
    <a target="c" href="https://commons.wikimedia.org/wiki/<?=
        Tools::topicUrlencode($this->topic['name'])
    ?>">VIEW ON COMMONS</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>/add/?s=topic&amp;t<?=
        Tools::topicUrlencode($this->topic['pageid'])
    ?>=on">Refresh Topic Info</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>/topic/?i=<?=
        Tools::topicUrlencode($this->topic['name'])
    ?>" onclick="return confirm('Confirm: Import Media To Topic?');">Import
    <?= !empty($this->topic['files']) ? $this->topic['files'] : '?' ?> Files into Topic</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>/topic/?sc=<?=
        Tools::topicUrlencode($this->topic['name'])
    ?>" onclick="return confirm('Confirm: Add Sub-Topics?');">Add
    <?= !empty($this->topic['subcats']) ? $this->topic['subcats'] : '?' ?> Sub-Topics</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>/media?dc=<?=
        Tools::topicUrlencode($this->topic['name'])
        ?>" onclick="return confirm('Confirm: Clear Media from Topic?');">Clear Media from Topic</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>/topic/?d=<?=
        urlencode($this->topic['id'])
    ?>" onclick="return confirm('Confirm: Delete Topic?');">Delete Topic</a>
    <br />
    <pre><?= print_r($this->topic, true)?></pre>
</div>
</form>
