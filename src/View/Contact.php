<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Contact
 *
 * @var array $data
 */
?>

<div class="box white">
    <?= $data['headline'] ?>
    <form method="POST">
        <textarea name="c" rows="12" cols="60"><?= $data['innertext'] ?><?= $data['footer'] ?></textarea>
        <p>
            <input type="submit" value="           Send message          ">
        </p>
    </form>
    <?= isset($data['result']) ? $data['result'] : '' ?>
</div>
