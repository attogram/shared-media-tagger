<?php
/**
 * Shared Media Tagger
 * Topic Admin
 *
 * @var array $cats
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col mb-4">
        <form action="" method="GET">
        <p>
            <input type="hidden" name="v" value="1">
            <input type="text" name="s" value="" size="20">
            <input type="submit" value="Filter Topics">
            &nbsp; &nbsp; <a href="<?= Tools::url('admin') ?>/add">+Add New Topic</a>
            &nbsp; &nbsp; <a href="<?= Tools::url('admin') ?>/topic/mass">Mass Refresh</a>
        </p>
        </form>
<?php

reset($cats);

foreach ($cats as $cat) {
    $this->smt->includeTemplate('AdminTopicInfo', $cat);
}
?>
    </div>
</div>
