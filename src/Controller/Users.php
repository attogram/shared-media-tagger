<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;

/**
 * Class Users
 */
class Users extends ControllerBase
{
    protected function display()
    {
        $view = $this->getView('Users');

        $allUsers = $this->smt->database->getUsers();
        $users = [];

        foreach ($allUsers as $user) {
            $user['tag_count'] = $this->smt->database->getUserTagCount($user['id']);
            $user['user_tagging'] = $this->smt->database->getUserTagging($user['id']);
            $users[$user['id']] = $user;
        }

        $userId = false;

        if (isset($_GET['i'])) {
            $userId = $_GET['i'];
            if (!array_key_exists($userId, $users)) {
                $this->smt->fail404('404 User Ratings Not Found');
            }
        }

        $this->smt->title = 'Users - ' . Config::$siteName;
        if ($userId) {
            $this->smt->title = 'User:' . $userId . ' - ' . Config::$siteName;
        }
        $this->smt->includeHeader();
        $this->smt->includeMediumMenu();

        /** @noinspection PhpIncludeInspection */
        include($view);
    }
}
