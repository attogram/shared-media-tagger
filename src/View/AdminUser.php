<?php
/**
 * Shared Media Tagger
 * User Admin View
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 * @var array $users
 */
declare(strict_types = 1);

?>
<form method="POST">
<div class="box white">
    <p>
        <b><?= count($users) ?></b> Users
    </p>
    <table border="1">
        <tr>
            <td>&nbsp;</td>
            <td>ID</td>
            <td>#Tags</td>
            <td>Last Active</td>
            <td>IP/Host</td>
            <td>User Agent</td>
        </tr>
        <?php foreach ($users as $user) { ?>
        <tr>
            <td>
                <input type="checkbox" name="d<?= $user['id'] ?>" />
            </td>
            <td class="center">
                <?= $user['id'] ?>
            </td>
            <td class="right">
                <?= $user['tagCount'] ?>
            </td>
            <td class="right nobr">
                <small><?= $user['lastActive'] ?></small>
            </td>
            <td class="right nobr">
                <small><?= $user['ipAndHost'] ?></small>
            </td>
            <td>
                <small><?= $user['user_agent'] ?></small>
            </td>
        </tr>
        <?php } ?>
    </table>
    <p>
        <input type="submit" value="Delete selected users" />
    </p>
</div>
</form>
