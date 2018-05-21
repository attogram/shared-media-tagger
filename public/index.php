<?php
/**
 * Shared Media Tagger
 *
 *  Router
 */

declare(strict_types = 1);

use Attogram\Router\Router;
use Attogram\SharedMedia\Tagger\SharedMediaTagger;
use Attogram\SharedMedia\Tagger\SharedMediaTaggerAdmin;

define('__SMT__', '0.8.0');

ob_start('ob_gzhandler');

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_readable($autoload)) {
    error('404 Vendor Autoloader Not Found');
}
require_once $autoload;

$router = new Router();

$router = setPublicRoutes($router);
showView($router, false);

$router = setAdminRoutes($router);
showView($router, true);

error('404 Route Not Found');


/**
 * @param Router $router
 * @param bool $isAdmin
 */
function showView(Router $router, bool $isAdmin)
{
    $view = $router->match();
    if (!$view) {
        return;
    }

    $viewFile = '../views/' . $view . '.php';

    if (!file_exists($viewFile)) {
        error('404 control not found');
    }

    $setup = __DIR__ . '/../_setup.php'; // optional Site Setup
    if (is_readable($setup)) {
        include $setup;
    }

    if ($isAdmin) {
        $smt = new SharedMediaTaggerAdmin();
    } else {
        $smt = new SharedMediaTagger();
    }

    include $viewFile;

    shutdown();
}

/**
 * @param Router $router
 * @return Router
 */
function setPublicRoutes(Router $router)
{
    $router->allow('/', 'index');
    $router->allow('/about.php', 'about');
    $router->allow('/browse.php', 'browse');
    $router->allow('/category.php', 'category');
    $router->allow('/categories.php', 'categories');
    $router->allow('/contact.php', 'contact');
    $router->allow('/info.php', 'info');
    $router->allow('/reviews.php', 'reviews');
    $router->allow('/sitemap.php', 'sitemap');
    $router->allow('/tag.php', 'tag');
    $router->allow('/users.php', 'users');
    return $router;
}

/**
 * @param Router $router
 * @return Router
 */
function setAdminRoutes(Router $router)
{
    $router->allow('/admin/', 'admin/index');
    $router->allow('/admin/category.php', 'admin/category');
    $router->allow('/admin/create.php', 'admin/create');
    $router->allow('/admin/curate.php', 'admin/curate');
    $router->allow('/admin/database.php', 'admin/database');
    $router->allow('/admin/export.php', 'admin/export');
    $router->allow('/admin/media.php', 'admin/media');
    $router->allow('/admin/media-analysis.php', 'admin/media-analysis');
    $router->allow('/admin/media-blocked.php', 'admin/media-blocked');
    $router->allow('/admin/reports.php', 'admin/reports');
    $router->allow('/admin/site.php', 'admin/site');
    $router->allow('/admin/sqladmin.php', 'admin/sqladmin');
    $router->allow('/admin/tag.php', 'admin/tag');
    $router->allow('/admin/user.php', 'admin/user');
    return $router;
}

/**
 * @param string $message
 */
function error(string $message)
{
    header('HTTP/1.0 404 Not Found');
    print '<h1>' . $message . '</h1>';
    shutdown();
}

/**
 *
 */
function shutdown()
{
    exit;
}
