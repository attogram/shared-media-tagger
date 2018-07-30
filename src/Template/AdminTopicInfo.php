<?php
/**
 * Shared Media Tagger
 * Admin Topic Info Template
 *
 * @var array $data
 */
declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$topicName = empty($data['name'])
    ? ''
    : $data['name'];
$topicNameEncoded = Tools::topicUrlencode($topicName);
$topicNameStripped = Tools::stripPrefix($topicName);
$topicNameStrippedEncoded = Tools::topicUrlencode($topicNameStripped);
$topicUrl = Tools::url('topic') . '/' . $topicNameStrippedEncoded;
$pageid = empty($data['pageid'])
    ? 'err'
    : $data['pageid'];
$localFiles = empty($data['local_files'])
    ? 0
    : number_format((float) $data['local_files']);
$remoteFiles = empty($data['files'])
    ? 0
    : '<a onclick="'
        . "return confirm('\\nConfirm Import:\\n\\nReally Import {$data['files']} Media from Topic: "
        . "$topicNameStripped?\\n');"
        . '" href="' . Tools::url('admin') . '/topic?i=' . $topicNameEncoded . '">'
        . number_format((float) $data['files']) . '</a>';
$diffFiles = $data['files'] - $data['local_files'];
$subTopics = empty($data['subcats'])
    ? 0
    : '<a onclick="'
        . "return confirm('\\nConfirm Import:\\n\\nReally Import {$data['subcats']} Subtopics from Topic: "
        . "$topicNameStripped?\\n');"
        . '" href="' . Tools::url('admin') . '/topic?sc=' . $topicNameEncoded . '">'
        . number_format((float) $data['subcats']) . '</a>';

?>
<div class="row border">
    <div class="col-6 col-sm-4 border border-black">
        <span class="font-weight-bold pr-1"><a href="<?= $topicUrl ?>"><?= $topicNameStripped ?></a></span>
        <a target="commons" href="https://commons.wikimedia.org/wiki/<?= $topicNameEncoded ?>">[src]</a>
    </div>
    <div class="col-3 col-sm-2 border border-black">
        <?= $remoteFiles ?> <small>source</small>
        <br /><?= $localFiles ?> <small>local [-<?= $diffFiles ?>]</small>
    </div>
    <div class="col-3 col-sm-2 border border-black">
        <?= $subTopics ?> <small>subs</small>
    </div>
    <div class="col border border-black small text-right">
        <a href="<?= Tools::url('admin') ?>/add/?ti[]=<?=
            $data['pageid']
        ?>"><?=
            $data['updated']
        ?></a>
        <br />
        <?= empty($data['hidden']) ? 'active' : 'hidden ' ?>
        <?= empty($data['curated']) ? 'open ' : 'curated ' ?>
        <?= empty($data['primary']) ? 'secondary' : 'primary' ?>
    </div>
    <div class="col border border-black small text-right">
        # <?= $data['pageid'] ?>
        <?php
        if (empty($data['pageid'])
            || !empty($data['missing']) && $data['missing'] == 1
        ) {
            ?><span class="bg-danger text-white pr-1 pl-1">MISSING</span><?php
        }
        ?>
        <br />
        <a onclick="return confirm('\nConfirm Deletion:\n\nReally Delete All <?=
            $localFiles
        ?> Media From Topic: <?= $topicNameStripped ?>?\n');" href="<?=
            Tools::url('admin')
        ?>/media/?dc=<?= $topicNameEncoded ?>">Clear</a>
        <a onclick="return confirm('\nConfirm Deletion:\n\nReally Delete Topic: <?=
            $topicNameStripped
        ?>?\n');" href="<?= Tools::url('admin') ?>/topic/?d=<?= $data['id'] ?>">Delete</a>

    </div>
</div>
