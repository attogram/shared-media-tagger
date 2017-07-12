<?php
// Shared Media Tagger
// Users

$init = __DIR__.'/smt.php'; 
if(!file_exists($init)||!is_readable($init)){ print 'Site down for maintenance'; exit; } require_once($init);

$smt = new smt();

$all_users = $smt->get_users();
$users = array();

foreach( $all_users as $user ) {
    $user['tag_count'] = $smt->get_user_tag_count( $user['id'] );
    $user['user_tagging'] = $smt->get_user_tagging( $user['id'] );
    $users[$user['id']] = $user;
}

//$smt->notice($users);

$user_id = FALSE;

if( isset($_GET['i']) ) {
    $user_id = $_GET['i'];
    if( !array_key_exists($user_id, $users) ) {
        $smt->fail404('404 User Ratings Not Found');
    }
}

$smt->include_header();
$smt->include_menu();
print '<div class="box white">';


if( !$users ) {
    print '<p>No users have reviewed files yet.</p>';
}

foreach( $users as $user ) {
    if( !$user['tag_count'] ) { continue; }
    print '<div style="display:inline-block; border:1px solid grey; padding:4px; margin:2px; ">'
    . '<h2><a href="' . $smt->url('users') . '?i=' . $user['id'] . '">'
    . '+' . $user['tag_count'] . '</h2>'
    . ' <small>user:' . $user['id'] . '</small>'
    . '</a>'
    . '</div>';
}
print '<hr />';


if( !$user_id ) {
    goto footer;
}

print '<p>+' . $smt->get_user_tag_count( $user_id ) . ' reviews by User:' . $user_id . '</p>';

foreach( $smt->get_user_tagging($user_id) as $media ) {
    print '<div style="display:inline-block;">'
    . '+' . $media['count'] . ' ' . $smt->get_tag_name_by_id($media['tag_id'])
    . '<br />'
    . $smt->display_thumbnail_box($media)
    . '</div>';
}


footer:
print '</div>';
$smt->include_footer();
