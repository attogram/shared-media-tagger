<?php
// Shared Media Tagger
// User Admin

////////////////////////////////////////////////////////////////////
$init = __DIR__.'/../smt.php'; // Shared Media Tagger Main Class
if( !is_readable($init) ) {
    print 'ERROR: not readable: ' . $init;
    return;
}
require_once($init);
$init = __DIR__.'/smt-admin.php'; // Shared Media Tagger Admin Class
if( !is_readable($init) ) {
    print 'ERROR: not readable: ' . $init;
    return;
}
require_once($init);
$smt = new smt_admin(); // The Shared Media Tagger Admin Object
/////////////////////////////////////////////////////////////////////

$smt->title = 'User Admin';
$smt->include_header();
$smt->include_medium_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>User Admin</p>';

$users = $smt->get_users();


print '<table border="1">';
print '<tr>
<td>ID</td>
<td>Tags</td>
<td>Last</td>
<td>IP/Host</td>
<td>User Agent</td>
</tr>';
foreach( $users as $user ) {
    $iphost = $user['ip'];
    if( $user['ip'] != $user['host'] ) {
        $iphost .= '<br />'.$user['host'];
    }
    print '<tr>'
    . '<td>' . $user['id'] . '</td>'
    . '<td><a href="' . $smt->url('users') .'?i=' . $user['id'] . '">+'
    . $smt->get_user_tag_count( $user['id'] ) . '</a></td>'
    //. '<td>' . $user['page_views'] . '</td>'
    . '<td class="nobr"><small>' . $user['last'] . '</small></td>'
    . '<td><small>' . $iphost . '</small></td>'
    . '<td><small>' . $user['user_agent'] . '</small></td>'

    . '</tr>';
}
print '</table>';

print '</div>';

$smt->include_footer();
