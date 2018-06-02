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

    public function __construct()
    {
        define('SHARED_MEDIA_TAGGER', '0.8.0');

        ob_start('ob_gzhandler');

        $this->router = new Router();

        $this->setPublicRoutes();
        $this->showView(false);

        $this->setAdminRoutes();
        $this->showView(true);

        Tools::error404('Page Not Found');
    }

    /**
     * @param bool $isAdmin
     */
    private function showView(bool $isAdmin = false)
    {
        $view = $this->router->match();
        if (!$view) {
            return;
        }

        $viewFile = '../views/' . $view . '.php';

        if (!is_readable($viewFile)) {
            Tools::error404('Control not found');
        }

        $setup = self::getSetupFromFile();

        if ($isAdmin) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $smt = new TaggerAdmin($this->router, $setup);
        } else {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $smt = new Tagger($this->router, $setup);
        }

        /** @noinspection PhpIncludeInspection */
        include $viewFile;

        Tools::shutdown();
    }

    /**
     * @return array
     */
    private function getSetupFromFile()
    {
        $optionalSetupFile = __DIR__ . '/../_setup.php';

        if (!is_readable($optionalSetupFile)) {
            return [];
        }

        /** @noinspection PhpIncludeInspection */
        include $optionalSetupFile;

        if (isset($setup) && is_array($setup) && $setup) {
            return $setup;
        }

        return [];
    }

    /**
     * setPublicRoutes
     */
    private function setPublicRoutes()
    {
        $this->router->allow('/', 'index');
        $this->router->allow('/about.php', 'about');
        $this->router->allow('/browse.php', 'browse');
        $this->router->allow('/category.php', 'category');
        $this->router->allow('/categories.php', 'categories');
        $this->router->allow('/contact.php', 'contact');
        $this->router->allow('/info.php', 'info');
        $this->router->allow('/reviews.php', 'reviews');
        $this->router->allow('/sitemap.php', 'sitemap');
        $this->router->allow('/tag.php', 'tag');
        $this->router->allow('/users.php', 'users');
    }

    /**
     * setAdminRoutes
     */
    private function setAdminRoutes()
    {
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
        $this->router->allow('/admin/site.php', 'admin/site');
        $this->router->allow('/admin/sqladmin.php', 'admin/sqladmin');
        $this->router->allow('/admin/tag.php', 'admin/tag');
        $this->router->allow('/admin/user.php', 'admin/user');
        $this->router->allow('/admin/api-sandbox.php', 'admin/api-sandbox');
    }
}
