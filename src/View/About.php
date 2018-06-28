<?php
/**
 * Shared Media Tagger - About page
 *
 * @var array $site
 */

declare(strict_types = 1);
?>
<div class="box white">
    <h1><?= $site['name'] ?></h1>
    <p><?= $site['about'] ?></p>

    <h3>How to use this site:</h3>
    <dl>
        <dt>
            <a href="<?= $site['urlHome'] ?>">Tagging</a>
        </dt>
        <dd>
            On top of each media file is row of tags, click one of them!
            Our tags are:
            <ul>
            <?php
            foreach ($site['tags'] as $tag) {
                print '<li>' . $tag . '</li>';
            }
            ?>
            </ul>
        </dd>

        <dt>
            <a href="<?= $site['urlCategories'] ?>">Categories</a>
        </dt>
        <dd>
            Lists all the categories
        </dd>

        <dt>
            <a href="<?= $site['urlReviews'] ?>">Results</a>
        </dt>
        <dd>
            All tagged media is shown here, with a page for each tag.
        </dd>
    </dl>

    <hr />
    <h3>Reusing images, videos and audio files</h3>
    <br />
    <br />
    <p>
        All media files highlighted on this site are
        <b><a href="https://freedomdefined.org/Definition" target="commons">Free Cultural Works</a></b>.
    </p>
    <p>
        You may copy, use and modify these media files for commercial or non-commercial purposes,
        as long as you properly follow the licensing requirements.
    <p>
    <p>
        See each media files info page for complete licensing information.
    <p>
</div>
