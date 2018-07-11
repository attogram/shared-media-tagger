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

use Attogram\SharedMedia\Tagger\Tools;

?>

<div class="row">
    <div class="col-sm-7 grey text-center align-top">
        <?= $this->smt->displayTags($pageid) ?>
        <?= $this->smt->displayMedia($media) ?>
        <?php $this->smt->includeAdminMediaFunctions($media['pageid']); ?>
    </div>
    <div class="col-sm-5 white align-top">
        <?php if ($media['imagedescriptionRows'] > 4) { ?>
        <textarea class="h1" readonly rows="<?= $media['imagedescriptionRows'] ?>"
            style="width:100%;font-size:130%; font-weight:bold;"><?=
            $media['imagedescriptionSafe']
        ?></textarea>
        <?php } else { ?>
        <h1><?= $media['imagedescriptionSafe'] ?></h1>
        <?php } ?>

        <dl>
            <dt>Scoring:</dt>
            <dd><?= $this->smt->displayReviews($this->smt->database->getReviews($pageid)) ?></dd>
        </dl>

        <p>
            <em>Categories:</em>
            <br />
            <?= $this->smt->displayCategories($pageid) ?>
        </p>

        <em>Download:</em>
        <ul>
            <li>Source: <a target="c" href="<?= $media['descriptionurl'] ?>">commons.wikimedia.org</a></li>
            <li>ID: <a target="c" href="<?= $media['descriptionshorturl'] ?>"><?= $media['pageid']; ?></a></li>
            <li>Filename: <b><?= Tools::stripPrefix($media['title']) ?></b></li>
            <?php
                $thumb = $this->smt->getThumbnail($media, 100);
            ?><li>Thumbnail: <a target="c" href="<?= $thumb['url']
                ?>"><?= $thumb['width'] ?>x<?= $thumb['height'] ?> pixels
                - <?= $media['thumbmime']; ?></a>
            </li>
            <li>Medium size: <a target="c" href="<?= $media['thumburl']
                ?>"><?= $media['thumbwidth'] ?>x<?= $media['thumbheight'] ?> pixels
                - <?= $media['thumbmime']; ?>
                </a></li>
            <li>Full size: <a target="c" href="<?= $media['url']
                ?>"><?= $media['width'] ?>x<?= $media['height'] ?> pixels
                - <?= $media['mime']; ?>
                - <?= number_format((float) $media['size']) ?> bytes
                </a></li>
        <?php
        if ($media['duration'] > 0) {
            print '<li>Duration: ' . Tools::secondsToTime($media['duration']) . '</li>';
        }
        ?>
        </ul>

        <p>
            <em>Licensing:</em>
            <ul>
            <li>Artist: <?= ($media['artist']
                        ? htmlentities(strip_tags($media['artist']))
                        : 'unknown')
                    ?></li>
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
                print '<li>License: <a target="license" href="'
                . $media['licenseuri'] . '">' . implode(', ', $lics)  . '</a></li>';
            } else {
                print '<li>License: ' . implode(', ', $lics) . '</li>';
            }
            if ($media['attributionrequired'] && $media['attributionrequired'] != 'false') {
                print '<li>Attribution Required</li>';
            }
            if ($media['restrictions'] && $media['restrictions'] != 'false') {
                print '<li>Restrictions: ' . $media['restrictions'] .'</li>';
            }
            ?>
            </ul>
        </p>
        <p>
            <style>li { margin-bottom:6px; }</style>
            <em>Media information:</em>
            <ul>
                <li>Original datetime: <?= $media['datetimeoriginal'] ?></li>
                <li>Upload datetime: <?= $media['timestamp'] ?></li>
                <li>Upload by: User:<?= $media['user'] ?></li>
                <li>SHA1: <small><?= $media['sha1'] ?></small></li>
                <li>Last refresh: <?= $media['updated'] ?> UTC</li>
            </ul>
        </p>
        <p>
            <em>Technical Categories:</em>
            <br />
            <small><?= $this->smt->displayCategories($pageid, true) ?></small>
        </p>
    </div>
</div>

