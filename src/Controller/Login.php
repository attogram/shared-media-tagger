<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Login
 */
class Login extends ControllerBase
{
    /** @var array */
    private $admins = [];

    /** @var string */
    private $realm;

    protected function display()
    {
        $this->loadAdminConfig();
        $this->realm = session_id();
        if ($this->isAuthenticated()) {
            header('Location: ' . Tools::url('admin'));
            Tools::shutdown();
        }
        $this->authenticate();
    }

    private function loadAdminConfig()
    {
        if (!is_readable(Config::$adminConfigFile)) {
            $this->smt->fail404('Login System Offline');
        }
        /** @noinspection PhpIncludeInspection */
        include(Config::$adminConfigFile);
        if (empty($admins) || !is_array($admins)) {
            $this->smt->fail404('No Admin Users Configured');
        }
        /** @noinspection PhpUndefinedVariableInspection */
        $this->admins = $admins;
    }
    /**
     * @return bool
     */
    private function isAuthenticated()
    {
        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            return false;
        }
        $data = $this->httpDigestParse($_SERVER['PHP_AUTH_DIGEST']);
        if (empty($data['username'])) {
            return false;
        }
        if (empty($this->admins[$data['username']])) {
            return false;
        }
        $validResponse = md5(
            md5(
                $data['username']
                . ':' . $this->realm
                . ':' . $this->admins[$data['username']]
            )
            . ':' . $data['nonce']
            . ':' . $data['nc']
            . ':' . $data['cnonce']
            . ':' . $data['qop']
            . ':' . md5(
                $_SERVER['REQUEST_METHOD']
                . ':' . $data['uri']
            )
        );
        if ($data['response'] != $validResponse) {
            return false;
        }
        $_SESSION['user'] = $data['username'];
        return true;
    }

    private function authenticate()
    {
        header('HTTP/1.1 401 Unauthorized');
        header(
            'WWW-Authenticate: Digest realm="'
            . $this->realm
            . '",qop="auth",nonce="'
            . uniqid()
            . '",opaque="'
            . md5($this->realm)
            . '"'
        );
        $this->smt->title = 'Login Failed';
        $this->smt->includeHeader();
        $this->smt->includeTemplate('Menu');
        ?>
        <div class="white">
            <h1>
                Login Failed
            </h1>
            <p>
                <a href="<?= Tools::url('login') ?>">Login again</a>
            </p>
        </div>
        <?php
        $this->smt->includeFooter();
        Tools::shutdown();
    }

    /**
     * @param string $text
     * @return array
     */
    private function httpDigestParse(string $text)
    {
        $required = [
            'cnonce'   => 1,
            'nonce'    => 1,
            'nc'       => 1,
            'qop'      => 1,
            'response' => 1,
            'uri'      => 1,
            'username' => 1,
        ];
        $data = [];
        $keys = implode('|', array_keys($required));
        preg_match_all(
            '@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@',
            $text,
            $matches,
            PREG_SET_ORDER
        );
        foreach ($matches as $match) {
            $data[$match[1]] = $match[3] ? $match[3] : $match[4];
            unset($required[$match[1]]);
        }

        return $required ? [] : $data;
    }
}
