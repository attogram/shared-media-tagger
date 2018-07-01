<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Logout
 */
class Logout extends ControllerBase
{
    protected function display()
    {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            0,
            $params['path'],
            $params['domain'],
            $params['secure'],
            isset($params['httponly'])
        );
        $_COOKIE = [];

        $_SESSION = [];
        session_destroy();
        session_write_close();

        session_start();
        session_regenerate_id(true);

        header('Location: ' . Tools::url('home'));
        Tools::shutdown();
    }
}
