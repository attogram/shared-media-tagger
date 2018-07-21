<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Topic
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var array   $topicInfo
 * @var string  $topicName
 * @var string  $topicNameDisplay
 * @var int|string $topicSize
 * @var string  $pager
 * @var string  $votesPerTopic
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col">

    <div style="float:right; padding:0 20px 4px 0; font-size:80%;">
        <?= $votesPerTopic ?>
    </div>
    <h1><?= $topicNameDisplay ?></h1>
    <b><?= $topicSize ?></b> files
    <?= $pager ? ', '.$pager : '' ?>
    <br clear="all" />
    <?php
    if (Tools::isAdmin()) {
        print '<form action="' . Tools::url('admin')
            . '/media" method="GET" name="media">';
    }

    foreach ($topic as $media) {
        $this->smt->includeTemplate('Thumbnail', $media);
    }

    if ($pager) {
        print '<p>' . $pager . '</p>';
    }

    if (Tools::isAdmin()) {
        print $this->smt->includeAdminTopicFunctions($topicName);
    }
    ?>
    </div>
</div>
