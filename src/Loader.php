<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;

/**
 * Route request, configure, and load Shared Media Tagger
 */
class Loader
{
    /** @var Router */
    private $router;

    /** @var bool */
    private $isAdmin = false;

    /**
     * Loader constructor.
     */
    public function __construct()
    {
        define('SHARED_MEDIA_TAGGER', '1.0.1');

        ob_start('ob_gzhandler');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->router = new Router();

        $this->setPublicRoutes();
        $this->show();

        $this->setAdminRoutes();
        $this->show();

        Tools::error404('Page Not Found');
    }

    /**
     * show
     */
    private function show()
    {
        $page = $this->router->match();
        if (!$page) {
            return;
        }

        if ($this->isAdmin) {
            $smt = new TaggerAdmin($this->router);
        } else {
            $smt = new Tagger($this->router);
        }

        $control = Config::$installDirectory . '/src/Controller/' . $page . '.php';
        if (is_readable($control)) {
            $class = 'Attogram\\SharedMedia\\Tagger\\Controller\\' . $page;
            if (class_exists($class)) {
                new $class($smt);
                Tools::shutdown();
            }
        }

        $view = Config::$installDirectory . '/src/View/' . $page . '.php';
        if (is_readable($view)) {
            /** @noinspection PhpIncludeInspection */
            include $view;
            Tools::shutdown();
        }

        Tools::error404('Page Not found');
    }

    /**
     * setPublicRoutes
     */
    private function setPublicRoutes()
    {
        $this->router->allow('/', 'Home');
        $this->router->allow('/b', 'Browse');
        $this->router->allow('/cc', 'Categories');
        $this->router->allow('/c/?', 'Category');
        $this->router->allow('/i/?', 'Info');
        $this->router->allow('/reviews', 'Reviews');
        $this->router->allow('/sitemap.xml', 'Sitemap');
        $this->router->allow('/tag', 'Tag');
        $this->router->allow('/login', 'Login');
        $this->router->allow('/logout', 'Logout');
    }

    /**
     * setAdminRoutes
     */
    private function setAdminRoutes()
    {
        $this->isAdmin = true;
        $this->router->allow('/admin/', 'AdminHome');
        $this->router->allow('/admin/category', 'AdminCategory');
        $this->router->allow('/admin/curate', 'AdminCurate');
        $this->router->allow('/admin/database', 'AdminDatabase');
        $this->router->allow('/admin/media', 'AdminMedia');
        $this->router->allow('/admin/media-blocked', 'AdminMediaBlocked');
        $this->router->allow('/admin/reports', 'AdminReports');
        $this->router->allow('/admin/site', 'AdminSite');
        $this->router->allow('/admin/sqladmin', 'AdminSqladmin');
        $this->router->allow('/admin/tag', 'AdminTag');
        $this->router->allow('/admin/user', 'AdminUser');
    }
}
