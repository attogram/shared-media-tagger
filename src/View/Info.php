<?php
declare(strict_types = 1);
/**
* Shared Media Tagger
* Media Info
*
* @var \Attogram\SharedMedia\Tagger\Tagger $smt
* @var array $media
*/

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="row">
    <div class="col-sm-7 text-center align-top bg-secondary">
        <?php
        $this->smt->includeTags($media['pageid']);

        if (in_array($media['mime'], Config::getMimeTypesVideo())) {
            print $this->smt->displayVideo($media);
        } elseif (in_array($media['mime'], Config::getMimeTypesAudio())) {
            print $this->smt->displayAudio($media);
        } else {
            $this->smt->includeTemplate('Image', $media);
        }
        ?>
        <?php $this->smt->includeAdminMediaFunctions($media['pageid']); ?>
    </div>
    <div class="col-sm-5 bg-white align-top">
        <?php if ($media['imagedescriptionRows'] > 4) { ?>
        <textarea class="h2" readonly rows="<?= $media['imagedescriptionRows'] ?>"
            style="width:100%;font-size:130%; font-weight:bold;"><?=
            $media['imagedescriptionSafe']
        ?></textarea>
        <?php } else { ?>
        <h2><?= $media['imagedescriptionSafe'] ?></h2>
        <?php } ?>
        <dl>
            <dt>Scoring:</dt>
            <dd><?= $this->smt->displayVotes($this->smt->database->getVotes($media['pageid'])) ?></dd>
        </dl>
        <p>
            <em>Topics:</em>
            <br />
            <?= $this->smt->displayTopics($media['pageid']) ?>
        </p>
        <em>Download:</em>
        <ul>
            <li>
                <small>Source:</small>
                <a target="c"
                   href="<?= $media['descriptionurl'] ?>">commons.wikimedia.org</a>
                    # <a target="c" href="<?= $media['descriptionshorturl'] ?>"><?= $media['pageid']; ?></a>
            </li>
            <li><small>Filename:</small> <b><?= Tools::stripPrefix($media['title']) ?></b></li>
            <?php
                $thumb = $this->smt->getThumbnail($media, 130);
            ?><li><small>Thumbnail:</small> <a target="c" href="<?= $thumb['url']
                ?>"><?= $thumb['width'] ?>x<?= $thumb['height'] ?> pixels
                - <?= $media['thumbmime']; ?></a>
            </li>
            <li><small>Preview:</small> <a target="c" href="<?= $media['thumburl']
                ?>"><?= $media['thumbwidth'] ?>x<?= $media['thumbheight'] ?> pixels
                - <?= $media['thumbmime']; ?>
                </a></li>
            <li><small>Full size:</small> <a target="c" href="<?= $media['url']
                ?>"><?= $media['width'] ?>x<?= $media['height'] ?> pixels
                - <?= $media['mime']; ?>
                - <?= number_format((float) $media['size']) ?> bytes
                </a></li>
        <?php
        if ($media['duration'] > 0) {
            print '<li><small>Duration:</small> ' . Tools::secondsToTime($media['duration']) . '</li>';
        }
        ?>
        </ul>
        <p>
            <em>Licensing:</em>
            <ul>
            <li><small>Artist:</small> <?= ($media['artist']
                        ? htmlentities(strip_tags($media['artist']))
                        : 'unknown')
                    ?></li>
            <?php
            if ($media['licenseuri'] && $media['licenseuri'] != 'false') {
                print '<li><small>License:</small> <a target="license" href="'
                . $media['licenseuri'] . '">' . implode(', ', $media['licensing'])  . '</a></li>';
            } else {
                print '<li><small>License:</small> ' . implode(', ', $media['licensing']) . '</li>';
            }
            if ($media['attributionrequired'] && $media['attributionrequired'] != 'false') {
                print '<li>Attribution Required</li>';
            }
            if ($media['restrictions'] && $media['restrictions'] != 'false') {
                print '<li><small>Restrictions:</small> ' . $media['restrictions'] . '</li>';
            }
            ?>
            </ul>
        </p>
        <p>
            <style>li { margin-bottom:6px; }</style>
            <em>Media information:</em>
            <ul>
                <li><small>Original datetime:</small> <?= $media['datetimeoriginal'] ?></li>
                <li><small>Upload datetime:</small> <?= $media['timestamp'] ?></li>
                <li><small>Uploader:</small> User:<?= $media['user'] ?></li>
                <li><small>SHA1:</small> <small><?= $media['sha1'] ?></small></li>
                <li><small>Refreshed:</small> <?= $media['updated'] ?> UTC</li>
            </ul>
        </p>
        <p>
            <em>Technical Topics:</em>
            <br />
            <small><?= $this->smt->displayTopics($media['pageid'], true) ?></small>
        </p>
    </div>
</div>
