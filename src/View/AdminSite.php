<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Admin Site View
 *
 * @var array $site
 */
?>
<form action="" method="POST">
<div class="row bg-white">
    <div class="col mb-4">
        <input type="hidden" name="id" value="<?= $site['id'] ?>">
        <input type="submit" value="           Save Site Setup           ">
        <br />
        <br / >
        Name:
        <br />
        <input name="name" type="text" size="30" value="<?= $site['name'] ?>">
        <br />
        <br />
        About:
        <br />
        <textarea name="about" rows="5" cols="70"><?= $site['about'] ?></textarea>
        <br />
        <br />
        <input type="checkbox" name="curation"<?=
        ($site['curation'] ? ' checked="checked"' : '')
        ?>/> Show only Curated Media
        <br />
        <br />
        Additional Header: <small>displayed for non-admins</small>
        <br />
        <textarea name="header" rows="5" cols="70"><?= $site['header'] ?></textarea>
        <br />
        <br />
        Additional Footer: <small>displayed for non-admins</small>
        <br />
        <textarea name="footer" rows="5" cols="70"><?= $site['footer'] ?></textarea>
        <br />
        <br />
        <input type="submit" value="           Save Site Setup           ">


        <small>
            <p class="mt-5">
                Last updated: <?= $site['updated'] ?>
            </p>
        </small>
    </div>
</div>
</form>
