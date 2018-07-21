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
    <div class="col">
    Settings:
    <ul>
        <li>Site Name: <kbd><?= Config::$siteName ?></kbd></li>
        <li>Curation Mode: <kbd><?= Config::$siteInfo['curation']
                ? '✅ON'
                : '❌OFF'
                ?></kbd></li>
        <li>Site Url: <kbd><?= Config::$siteUrl ?></kbd></li>
        <li>Protocol: <kbd><?= Config::$protocol ?></kbd></li>
        <li>Server: <kbd><?= Config::$server ?></kbd></li>
    </ul>
    <hr />
    Directories:
    <ul>
        <li>cwd: <kbd><?= getcwd() ?></kbd></li>
        <li>sourceDirectory: <kbd><?= Config::$sourceDirectory ?></kbd></li>
        <li>databaseDirectory: <kbd><?= Config::$databaseDirectory ?></kbd></li>
        <li>adminConfigFile: <kbd><?= Config::$adminConfigFile ?></kbd></li>
    </ul>
    <hr />
    Discovery:
    <ul>
        <li><a href="<?= Tools::url('sitemap') ?>">sitemap.xml</a></li>
        <li>/public/.htaccess:
            <?= (is_readable(Config::$publicDirectory . '/.htaccess')
                ? '✅ACTIVE'
                : '❌MISSING'
            ) ?></li>
        <li><a href="/robots.txt">/robots.txt</a>:
            <span style="font-family:monospace;"><?= $this->smt->checkRobotstxt() ?></span>
        </li>

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
