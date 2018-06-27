<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Export Admin
 *
 * @var array $data
 */
?>
<div class="box white">
    <p>Export  Admin</p>
    <ul>
        <?php

        foreach ($data['tags'] as $tag) {
            print '<li>MediaWiki Format: Tag Report: '
                . '<a href="?r=tag&amp;i=' . $tag['id'] . '">' . $tag['name'] . '</a></li>';
        }

        ?>
    </ul>
    <br />
    <textarea cols="90" rows="20"><?= $data['result'] ?></textarea>'
</div>
