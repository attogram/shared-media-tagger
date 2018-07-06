<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;

/**
 * Route request, configure, and load Shared Media Tagger
 */
class Loader
{
    /** @var array - Site Configuration */
    private $config = [];

    /** @var bool */
    private $isAdminRoute = false;

    /** @var Router */
    private $router;

    /**
     * Loader constructor.
     * @param array $config - optional configuration settings
     */
    public function __construct(array $config = [])
    {
        define('SHARED_MEDIA_TAGGER', '1.0.1');

        ob_start('ob_gzhandler');

        $this->config = $config;

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->router = new Router();

        $this->setPublicRoutes();
        $this->show();

        $this->isAdminRoute = true;
        $this->setAdminRoutes();
        $this->show();

        $this->redirects();

        Tools::error404('Page Not Found');
    }

    /**
     * Show a page if there is a match
     */
    private function show()
    {
        $page = $this->router->match();
        if (!$page) {
            return;
        }

        if ($this->isAdminRoute) {
            $smt = new TaggerAdmin($this->router, $this->config);
        } else {
            $smt = new Tagger($this->router, $this->config);
        }

        $control = Config::$sourceDirectory . '/Controller/' . $page . '.php';
        if (is_readable($control)) {
            $class = 'Attogram\\SharedMedia\\Tagger\\Controller\\' . $page;
            if (class_exists($class)) {
                new $class($smt);
                Tools::shutdown();
            }
        }

        $view = Config::$sourceDirectory . '/View/' . $page . '.php';
        if (is_readable($view)) {
            /** @noinspection PhpIncludeInspection */
            include $view;
            Tools::shutdown();
        }

        Tools::error404('Page Not found');
    }

    /**
     * Set Public Routes
     */
    private function setPublicRoutes()
    {
        $this->router->allow('/', 'Home');
        $this->router->allow('/b', 'Browse');
        $this->router->allow('/categories', 'Categories');
        $this->router->allow('/c/?', 'Category');
        $this->router->allow('/i/?', 'Info');
        $this->router->allow('/scores', 'Scores');
        $this->router->allow('/sitemap.xml', 'Sitemap');
        $this->router->allow('/tag', 'Tag');
        $this->router->allow('/login', 'Login');
        $this->router->allow('/logout', 'Logout');
    }

    /**
     * Set Admin Routes
     */
    private function setAdminRoutes()
    {
        $this->router->allow('/admin/', 'AdminHome');
        $this->router->allow('/admin/category', 'AdminCategory');
        $this->router->allow('/admin/curate', 'AdminCurate');
        $this->router->allow('/admin/database', 'AdminDatabase');
        $this->router->allow('/admin/media', 'AdminMedia');
        $this->router->allow('/admin/media-blocked', 'AdminMediaBlocked');
        $this->router->allow('/admin/reports', 'AdminReports');
        $this->router->allow('/admin/site', 'AdminSite');
        $this->router->allow('/admin/sqladmin', 'AdminSqlAdmin');
        $this->router->allow('/admin/tag', 'AdminTag');
        $this->router->allow('/admin/user', 'AdminUser');
    }

    /**
     * Redirect old v.0 urls
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
                $redirect = Tools::url('categories');
                break;
            case '/category.php':
                print "HI!";
                if (!empty($_GET['c'])) {
                    $redirect = Tools::url('category') . '/' . $_GET['c'];
                    break;
                }
                $redirect = Tools::url('categories');
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
