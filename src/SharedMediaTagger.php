<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;

/**
 * Route request, configure, and load Shared Media Tagger
 */
class SharedMediaTagger
{
    /** @var array - Site Configuration */
    private $config = [];

    /** @var Router */
    private $router;

    /**
     * Loader constructor.
     * @param array $config - optional configuration settings
     */
    public function __construct(array $config = [])
    {
        define('SHARED_MEDIA_TAGGER', '1.1.10');

        ob_start('ob_gzhandler');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->router = new Router();
        $this->config = $config;

        $this->setPublicRoutes();
        $this->show();

        if (Tools::isAdmin()) {
            $this->setAdminRoutes();
            $this->show();
        }

        $this->redirects();

        Tools::error404('Page Not Found');
    }

    private function setPublicRoutes()
    {
        $this->router->allow('/', 'Home');
        $this->router->allow('/b', 'Browse');
        $this->router->allow('/c/?', 'Topic');
        $this->router->allow('/c/?/?', 'Topic');
        $this->router->allow('/c/?/?/?', 'Topic');
        $this->router->allow('/c/?/?/?/?', 'Topic');
        $this->router->allow('/topics', 'Topics');
        $this->router->allow('/i/?', 'Info');
        $this->router->allow('/login', 'Login');
        $this->router->allow('/logoff', 'Logout');
        $this->router->allow('/logout', 'Logout');
        $this->router->allow('/me', 'UserMe');
        $this->router->allow('/me/?', 'UserMe');
        $this->router->allow('/random', 'Random');
        $this->router->allow('/scores', 'Scores');
        $this->router->allow('/scores/?', 'Scores');
        $this->router->allow('/search', 'Search');
        $this->router->allow('/sitemap.xml', 'Sitemap');
        $this->router->allow('/tag', 'Tag');
    }

    private function setAdminRoutes()
    {
        $this->router->allow('/admin/', 'AdminHome');
        $this->router->allow('/admin/add', 'AdminAdd');
        $this->router->allow('/admin/topic', 'AdminTopic');
        $this->router->allow('/admin/topic/mass', 'AdminTopicMass');
        $this->router->allow('/admin/curate', 'AdminCurate');
        $this->router->allow('/admin/database', 'AdminDatabase');
        $this->router->allow('/admin/database/download', 'AdminDatabaseDownload');
        $this->router->allow('/admin/media', 'AdminMedia');
        $this->router->allow('/admin/media-blocked', 'AdminMediaBlocked');
        $this->router->allow('/admin/reports', 'AdminReports');
        $this->router->allow('/admin/site', 'AdminSite');
        $this->router->allow('/admin/tag', 'AdminTag');
        $this->router->allow('/admin/user', 'AdminUser');
    }

    private function show()
    {
        $page = $this->router->match();
        if (!$page) {
            return;
        }

        if (Tools::isAdmin()) {
            $smt = new TaggerAdmin($this->router, $this->config);
        } else {
            $smt = new Tagger($this->router, $this->config);
        }

        $control = Config::$sourceDirectory . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . $page . '.php';
        if (is_readable($control)) {
            $class = 'Attogram\\SharedMedia\\Tagger\\Controller\\' . $page;
            if (class_exists($class)) {
                new $class($smt);
                Tools::shutdown();
            }
        }

        $view = Config::$sourceDirectory . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . $page . '.php';
        if (is_readable($view)) {
            /** @noinspection PhpIncludeInspection */
            include $view;
            Tools::shutdown();
        }

        Tools::error404('Page Not Found');
    }

    /**
     * Redirect old urls
     */
    private function redirects()
    {
        Config::setSiteUrl($this->router->getUriBase() . '/');
        Config::setLinks();
        $redirect = false;
        switch ($this->router->getUriRelative()) {
            case '/info.php':
                if (!empty($_GET['i']) && Tools::isPositiveNumber($_GET['i'])) {
                    $redirect = Tools::url('info') . '/' . $_GET['i'];
                    break;
                }
                $redirect = Tools::url('home');
                break;
            case '/categories.php':
            case '/categories':
                $redirect = Tools::url('topics');
                break;
            case '/category.php':
            case '/category':
                if (!empty($_GET['c'])) {
                    $redirect = Tools::url('topic') . '/' . $_GET['c'];
                    break;
                }
                $redirect = Tools::url('topics');
                break;
            case '/contact.php':
                if (!empty($_GET['r']) && Tools::isPositiveNumber($_GET['r'])) {
                    $redirect = Tools::url('info') . '/' . $_GET['r'];
                    break;
                }
                $redirect = Tools::url('home');
                break;
            case '/about.php':
            case '/users.php':
                $redirect = Tools::url('home');
                break;
        }
        if ($redirect) {
            Tools::redirect301($redirect);
        }
    }
}
