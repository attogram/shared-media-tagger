<?php
/**
 * Shared Media Tagger
 * Users
 *
 * @var Attogram\SharedMedia\Tagger\Tagger $smt
 */

use Attogram\SharedMedia\Tagger\Config;

$allUsers = $smt->getUsers();
$users = [];

foreach ($allUsers as $user) {
    $user['tag_count'] = $smt->database->getUserTagCount($user['id']);
    $user['user_tagging'] = $smt->getUserTagging($user['id']);
    $users[$user['id']] = $user;
}

$userId = false;

if (isset($_GET['i'])) {
    $userId = $_GET['i'];
    if (!array_key_exists($userId, $users)) {
        $smt->fail404('404 User Ratings Not Found');
    }
}

$smt->title = 'Users - ' . Config::$siteName;
if ($userId) {
    $smt->title = 'User:' . $userId . ' - ' . Config::$siteName;
}
$smt->includeHeader();
$smt->includeMediumMenu();
print '<div class="box white">';


if (!$users) {
    print '<p>No users have reviewed files yet.</p>';
}

foreach ($users as $user) {
    if (!$user['tag_count']) {
        continue;
    }
    print '<div style="display:inline-block; border:1px solid grey; padding:4px; margin:2px; ">'
    . '<h2><a href="' . $smt->url('users') . '?i=' . $user['id'] . '">'
    . '+' . $user['tag_count'] . '</h2>'
    . ' <small>user:' . $user['id'] . '</small>'
    . '</a>'
    . '</div>';
}
print '<hr />';

if ($userId) {
    print '<p>+' . $smt->database->getUserTagCount($userId) . ' reviews by User:' . $userId . '</p>';
    foreach ($smt->getUserTagging($userId) as $media) {
        print '<div style="display:inline-block;">'
            . '+' . $media['count'] . ' ' . $smt->getTagNameById($media['tag_id'])
            . '<br />' . $smt->displayThumbnailBox($media)
            . '</div>';
    }
}

print '</div>';
$smt->includeFooter();
