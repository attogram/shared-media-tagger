<?php
declare(strict_types = 1);
/**
 * Shared Media Tagger
 * Users
 *
 * @var array $users
 * @var int|string $userId
 */

use Attogram\SharedMedia\Tagger\Tools;

?>
<div class="box white">
<?php
if (!$users) {
    print '<p>No users have reviewed files yet.</p>';
}

foreach ($users as $user) {
    if (!$user['tag_count']) {
        continue;
    }
    print '<div style="display:inline-block; border:1px solid grey; padding:4px; margin:2px; ">'
    . '<h2><a href="' . Tools::url('users') . '?i=' . $user['id'] . '">'
    . '+' . $user['tag_count'] . '</h2>'
    . ' <small>user:' . $user['id'] . '</small>'
    . '</a>'
    . '</div>';
}
print '<hr />';

if ($userId) {
    print '<p>+' . $this->smt->database->getUserTagCount($userId) . ' reviews by User:' . $userId . '</p>';
    foreach ($this->smt->database->getUserTagging($userId) as $media) {
        print '<div style="display:inline-block;">'
            . '+' . $media['count'] . ' ' . $this->smt->database->getTagNameById($media['tag_id'])
            . '<br />' . $this->smt->displayThumbnailBox($media)
            . '</div>';
    }
}

?>
</div>
