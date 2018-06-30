<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Media Info
 *
 * @var \Attogram\SharedMedia\Tagger\Tagger $smt
 * @var int|string $pageid
 * @var array $media
 * @var array $data
 */

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="container">
    <div class="row">
        <div class="col-sm-6 box grey center" style="height:stretch;">
            <?= $this->smt->displayTags($pageid) ?>
            <?= $this->smt->displayMedia($media) ?>
            <div class="left" style="margin:auto; width:<?= Config::$sizeMedium ?>px;">
                <br />
                <?= $this->smt->displayReviews($this->smt->database->getReviews($pageid)) ?>
                <?= $this->smt->displayCategories($pageid, false) ?>
                <?= $data['admin'] ?>
            </div>
        </div>
        <div class="col-sm-6 box white">
            <p style="font-size:130%; font-weight:bold;">
                <textarea readonly rows="5" style="width:100%;"><?=
                    !empty($media['imagedescription'])
                        ? strip_tags($media['imagedescription'])
                        : Tools::stripPrefix($media['title'])
                ?></textarea>
            </p>
            <p>
                <em>Download file:</em>
            <ul>
                <li><b><a target="commons" href="<?= $media['url'] ?>"><?=
                            Tools::stripPrefix($media['title']) ?></a></b></li>
                <ul>
                    <small>
                        <li>size: <b><?= number_format((float) $media['size']) ?></b> bytes</li>
                        <li>w x h: <b><?= number_format((float) $media['width']); ?>
                                x <?= number_format((float) $media['height']) ?></b> pixels</li>
                        <li>mime: <b><?= $media['mime']; ?></b></li>
                        <?php
                        if ($media['duration'] > 0) {
                            print '<li>duration: <b>' . Tools::secondsToTime($media['duration']) . '</b></li>';
                        } ?>
                    </small>
                </ul>
            </ul>
            </p>
            <p>
                <em>Licensing information:</em>
                <ul>
                <li>Artist: <b><?= ($media['artist']
                            ? htmlentities(strip_tags($media['artist']))
                            : 'unknown')
                        ?></b></li>
                <?php
                $fix = [
                    'Public domain'=>'Public Domain',
                    'CC-BY-SA-3.0'=>'CC BY-SA 3.0'
                ];

                foreach ($fix as $bad => $good) {
                    if ($media['usageterms'] == $bad) {
                        $media['usageterms'] = $good;
                    }
                    if ($media['licensename'] == $bad) {
                        $media['licensename'] = $good;
                    }
                    if ($media['licenseshortname'] == $bad) {
                        $media['licenseshortname'] = $good;
                    }
                }
                $lics = [];
                $lics[] = $media['licensename'];
                $lics[] = $media['licenseshortname'];
                $lics[] = $media['usageterms'];
                $lics = array_unique($lics);

                if ($media['licenseuri'] && $media['licenseuri'] != 'false') {
                    print '<li>License: <b><a target="license" href="'
                    . $media['licenseuri'] . '">' . implode(' - ', $lics)  . '</a></b></li>';
                } else {
                    print '<li>License: <b>' . implode('<br />', $lics) . '</b></li>';
                }
                if ($media['attributionrequired'] && $media['attributionrequired'] != 'false') {
                    print '<li>Attribution Required</b></li>';
                }
                if ($media['restrictions'] && $media['restrictions'] != 'false') {
                    print '<li>Restrictions: <b>' . $media['restrictions'] .'</b></li>';
                }
                ?>
                </ul>
            </p>
            <p>
                <style>li { margin-bottom:6px; }</style>
                <em>Media information:</em>
                <ul>
                    <li>ID: <b><?= $media['pageid']; ?></b></li>
                    <li>Source: <a target="commons" href="<?=
                        $media['descriptionurl'] ?>">commons.wikimedia.org</a></li>
                    <li>Original datetime: <b><?= $media['datetimeoriginal'] ?></b></li>
                    <li>Upload datetime: <b><?= $media['timestamp'] ?></b></li>
                    <li>Upload by: <b>User:<?= $media['user'] ?></b></li>
                    <li>SHA1: <small><b><?= $media['sha1'] ?></b></small></li>
                    <li>Last refresh: <?= $media['updated'] ?> UTC</li>
                </ul>
            </p>
            <p>
                <em>Categories:</em>
                <br />
                <?= $this->smt->displayCategories($pageid) ?>
            </p>
            <p>
                <em>Technical Categories:</em>
                <br />
                <small><?= $this->smt->displayCategories($pageid, true) ?></small>
            </p>
        </div>
    </div>
</div>
<br />
