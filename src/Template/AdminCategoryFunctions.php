<?php
/**
 * Shared Media Tagger
 * Admin Category Functions Template
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
    &nbsp;&nbsp;
    <a onclick="javascript:checkAll('media', false);" href="javascript:void();">uncheck all</a>
    <br />
    <br />
    <a target="c" href="https://commons.wikimedia.org/wiki/<?=
        Tools::categoryUrlencode($this->category['name'])
    ?>">VIEW ON COMMONS</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>category/?c=<?=
        Tools::categoryUrlencode($this->category['name'])
    ?>">Get Category Info</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>category/?i=<?=
        Tools::categoryUrlencode($this->category['name'])
    ?>" onclick="return confirm('Confirm: Import Media To Category?');">Import
    <?= !empty($this->category['files']) ? $this->category['files'] : '?' ?> Files into Category</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>category/?sc=<?=
        Tools::categoryUrlencode($this->category['name'])
    ?>" onclick="return confirm('Confirm: Add Sub-Categories?');">Add
    <?= !empty($this->category['subcats']) ? $this->category['subcats'] : '?' ?> Sub-Categories</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>media?dc=<?=
        Tools::categoryUrlencode($this->category['name'])
        ?>" onclick="return confirm('Confirm: Clear Media from Category?');">Clear Media from Category</a>
    <br />
    <br />
    <a href="<?= Tools::url('admin') ?>category/?d=<?=
        urlencode($this->category['id'])
    ?>" onclick="return confirm('Confirm: Delete Category?');">Delete Category</a>
    <br />
    <pre><?= print_r($this->category, true)?></pre>
</div>
</form>
