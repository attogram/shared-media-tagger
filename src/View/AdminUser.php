<?php
/**
 * Shared Media Tagger
 * User Admin
 *
 * @var Attogram\SharedMedia\Tagger\TaggerAdmin $smt
 */

declare(strict_types = 1);

use Attogram\SharedMedia\Tagger\Tools;

$smt->title = 'User Admin';
$smt->includeHeader();
$smt->includeMediumMenu();
$smt->includeAdminMenu();

$users = $smt->database->getUsers();

print '<div class="box white"><p>User Admin</p>
<table border="1">
<tr>
<td>ID</td>
<td>Tags</td>
<td>Last</td>
<td>IP/Host</td>
<td>User Agent</td>
</tr>';
foreach ($users as $user) {
    $iphost = $user['ip'];
    if ($user['ip'] != $user['host']) {
        $iphost .= '<br />' . $user['host'];
    }
    print '<tr>'
    . '<td>' . $user['id'] . '</td>'
    . '<td>+' . $smt->database->getUserTagCount($user['id']) . '</td>'
    . '<td class="nobr"><small>' . $user['last'] . '</small></td>'
    . '<td><small>' . $iphost . '</small></td>'
    . '<td><small>' . $user['user_agent'] . '</small></td>'
    . '</tr>';
}
print '</table>';
print '</div>';

$smt->includeFooter();
