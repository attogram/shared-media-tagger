<?php
/**
 * Shared Media Tagger
 * Topic Mass Admin
 *
 * @var array $data
 */
declare(strict_types = 1);

if (empty($data['topics'])) {
    $data = [];
    $data['topics'] = [];
}
?>
<div class="row bg-white">
    <div class="col">
        <a href="<?= $data['refresh'] ?>">
            <button type="button" class="btn btn-success">
                Mass Refresh these <?= count($data['topics']) ?> Oldest Topics
            </button>
        </a>
        <?php
        foreach ($data['topics'] as $topic) {
            $this->smt->includeTemplate('AdminTopicInfo', $topic);
        }
        ?>
    </div>
</div>
