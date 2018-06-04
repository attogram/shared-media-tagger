<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Media Admin
 *
 * @var array $data
 */
?>
<div class="box white">
    <p>
        Media Admin:
    </p>
    <p>
        <?= $data['result'] ?>
    </p>
    <form action="" method="GET">
        * Add Media:
        <input type="text" name="am" value="" size="10" />
        <input type="submit" value="  Add via pageid  "/>
    </form>
    <br />
    <br />
    <form action="" method="GET">
        * Delete &amp; Block Media:
        <input type="text" name="dm" value="" size="10" />
        <input type="submit" value="  Delete via pageid  "/>
    </form>
    <br />
    <br />
    <form action="" method="GET">
        * Delete &amp; Block All Media in Category:
        <input type="text" name="dc" value="" size="30" />
        <input type="submit" value="  Delete via Category Name  "/>
    </form>
    <p>
        * <a href="./media-blocked">View/Edit Blocked Media</a>
    </p>
</div>
