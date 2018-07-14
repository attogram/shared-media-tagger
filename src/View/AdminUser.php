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
<div class="row bg-white">
    <div class="col-12 mb-4">
        <p>
            <b><?= count($users) ?></b> Users
        </p>
        <table class="table table-sm table-bordered table-hover table-responsive">
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
                <td class="text-center">
                    <?= $user['id'] ?>
                </td>
                <td class="text-right">
                    <?= $user['tagCount'] ?>
                </td>
                <td class="text-right text-nowrap">
                    <small><?= $user['lastActive'] ?></small>
                </td>
                <td class="text-right text-nowrap">
                    <small><?= $user['ipAndHost'] ?></small>
                </td>
                <td class="text-nowrap">
                    <small><?= $user['user_agent'] ?></small>
                </td>
            </tr>
            <?php } ?>
        </table>
        <p>
            <input type="submit" value="Delete selected users" />
        </p>
    </div>
</div>
</form>
