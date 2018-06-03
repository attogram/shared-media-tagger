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
        define('SHARED_MEDIA_TAGGER', '1.0.0');

        ob_start('ob_gzhandler');

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

        Tools::error404('Page view not found');
    }

    /**
     * setPublicRoutes
     */
    private function setPublicRoutes()
    {
        $this->router->allow('/', 'Home');
        $this->router->allow('/about', 'About');
        $this->router->allow('/browse', 'Browse');
        $this->router->allow('/category', 'Category');
        $this->router->allow('/categories', 'Categories');
        $this->router->allow('/contact', 'Contact');
        $this->router->allow('/info', 'Info');
        $this->router->allow('/reviews', 'Reviews');
        $this->router->allow('/sitemap.xml', 'Sitemap');
        $this->router->allow('/tag', 'Tag');
        $this->router->allow('/users', 'Users');
    }

    /**
     * setAdminRoutes
     */
    private function setAdminRoutes()
    {
        $this->isAdmin = true;
        $this->router->allow('/admin/', 'admin/index');
        $this->router->allow('/admin/category.php', 'admin/category');
        $this->router->allow('/admin/create.php', 'admin/create');
        $this->router->allow('/admin/curate.php', 'admin/curate');
        $this->router->allow('/admin/database.php', 'admin/database');
        $this->router->allow('/admin/export.php', 'admin/export');
        $this->router->allow('/admin/media.php', 'admin/media');
        $this->router->allow('/admin/media-analysis.php', 'admin/media-analysis');
        $this->router->allow('/admin/media-blocked.php', 'admin/media-blocked');
        $this->router->allow('/admin/reports.php', 'admin/reports');
        $this->router->allow('/admin/site.php', 'AdminSite');
        $this->router->allow('/admin/sqladmin.php', 'admin/sqladmin');
        $this->router->allow('/admin/tag.php', 'admin/tag');
        $this->router->allow('/admin/user.php', 'admin/user');
        $this->router->allow('/admin/api-sandbox.php', 'admin/api-sandbox');
    }
}
