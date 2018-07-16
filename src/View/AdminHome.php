<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Admin Home
 *
 * @var \Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row bg-white">
    <div class="col-12">
    Site: <b><a href="./site"><?= Config::$siteName ?></a></b>
    <ul>
        <li><b><?= sizeof($this->smt->database->getTags()) ?></b> <a href="./tags">Tags</a></li>
        <li><b><?= number_format((float) $this->smt->database->getFileCount()) ?></b> Files</li>
        <li><b><?= number_format((float) $this->smt->database->getBlockCount()) ?></b> Blocked Files</li>
        <li><b><?= number_format((float) $this->smt->database->getTotalFilesReviewedCount()) ?></b> Files reviewed</li>
        <li><b><?= number_format((float) $this->smt->database->getTaggingCount()) ?></b> Tagging Count</li>
    </ul>

    <hr />
    Installation:
    <ul>
        <li>server: <kbd><?= Config::$server ?></kbd></li>
        <li>siteName: <kbd><?= Config::$siteName ?></kbd></li>
        <li>home: <a href="<?= Tools::url('home') ?>"><?= Tools::url('home') ?></a></li>
        <li>protocol: <kbd><?= Config::$protocol ?></kbd></li>
        <li>siteUrl: <kbd><?= Config::$siteUrl ?></kbd></li>
        <li>sourceDirectory: <kbd><?= Config::$sourceDirectory ?></kbd></li>
        <li>databaseDirectory: <kbd><?= Config::$databaseDirectory ?></kbd></li>
        <li>adminConfigFile: <kbd><?= Config::$adminConfigFile ?></kbd></li>
    </ul>

    <hr />
    Discovery:
    <ul>
        <li>/public/.htaccess:
            <?= (is_readable(Config::$publicDirectory . '/.htaccess')
                ? '✅ACTIVE'
                : '❌MISSING'
            ) ?></li>
        <li><a href="/robots.txt">/robots.txt</a>:
            <span style="font-family:monospace;"><?= $this->smt->checkRobotstxt() ?></span>
        </li>
        <li><a href="<?= Tools::url('sitemap') ?>">sitemap.xml</a></li>
    </ul>

    <hr />
    About Shared Media Tagger:
    <ul>
        <li><a target="c"
                        href="<?= Tools::url('github_smt') ?>">Github: attogram/shared-media-tagger</a></li>
        <li><a target="c"
               href="<?= Tools::url('github_smt') ?>/blob/master/README.md">README</a></li>
        <li><a target="c"
               href="<?= Tools::url('github_smt') ?>/blob/master/LICENSE.md">LICENSE</a></li>
    </ul>
    </div>
</div>
