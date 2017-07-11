<?php
// Shared Media Tagger
// User Admin

$init = __DIR__.'/../smt.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);
$init = __DIR__.'/smt-admin.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt_admin('Admin');

$smt->include_header();
$smt->include_menu();
$smt->include_admin_menu();
print '<div class="box white"><p>User Admin</p>';

$users = $smt->get_users();


print '<table border="1">';
print '<tr>
<td>ID</td>
<td>Tags</td>
<td>Views</td>
<td>Last</td>
<td>IP</td>
<td>Host</td>
<td>User Agent</td>
</tr>';
foreach( $users as $user ) {
	print '<tr>'
	. '<td>' . $user['id'] . '</td>'
	. '<td>?</td>'
	. '<td>' . $user['page_views'] . '</td>'
	. '<td>' . $user['last'] . '</td>'
	. '<td>' . $user['ip'] . '</td>'
	. '<td>' . $user['host'] . '</td>'
	. '<td>' . $user['user_agent'] . '</td>'

	. '</tr>';
}
print '</table>';

print '</div>';

$smt->include_footer();
