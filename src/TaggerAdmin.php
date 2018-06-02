<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;

/**
 * Class TaggerAdmin
 */
class TaggerAdmin extends Tagger
{
    /** @var Commons */
    public $commons;
    /** @var DatabaseAdmin */
    public $database;

    /**
     * TaggerAdmin constructor.
     * @param Router $router
     * @param array $setup
     */
    public function __construct(Router $router, array $setup = [])
    {
        parent::__construct($router, $setup);
        ini_set('user_agent', 'Shared Media Tagger v' . SHARED_MEDIA_TAGGER);
        $this->setAdminCookie();
        $this->commons = new Commons();
        $this->database = new DatabaseAdmin();
        $this->commons->setDatabase($this->database);
        $this->database->setCommons($this->commons);
    }

    /**
     * includeAdminMenu
     */
    public function includeAdminMenu()
    {
        $admin = Tools::url('admin');
        $space = ' &nbsp; &nbsp; ';
        print '<div class="menu admin">'
            . '<a href="' . $admin . '">ADMIN</a>'
            . $space . '<a href="' . $admin . 'site.php">SITE</a>'
            . $space . '<a href="' . $admin . 'tag.php">TAG</a>'
            . $space . '<a href="' . $admin . 'category.php">CATEGORY</a>'
            . $space . '<a href="' . $admin . 'media.php">MEDIA</a>'
            . $space . '<a href="' . $admin . 'curate.php">CURATE</a>'
            . $space . '<a href="' . $admin . 'user.php">USER</a>'
            . $space . '<a href="' . $admin . 'create.php">CREATE</a>'
            . $space . '<a href="' . $admin . 'export.php">EXPORT</a>'
            . $space . '<a href="' . $admin . 'database.php">DATABASE</a>'
            . $space . '<a href="' . $admin . 'api-sandbox.php">api-sandbox</a>'
            . '</div>';
    }

    /**
     * setAdminCookie
     */
    public function setAdminCookie()
    {
        if (isset($_COOKIE['admin']) && $_COOKIE['admin'] == '1') {
            return;
        }
        setcookie('admin', '1', time()+28800, '/'); // 8 hour admin cookie
    }

    /**
     * @return string
     */
    public function checkRobotstxt()
    {
        $robotstxt = Config::$installDirectory . '/robots.txt';
        $tagUrl = str_replace('//'.Config::$server, '', Tools::url('tag'));
        $sitemapUrl = Config::$protocol . Tools::url('home') . 'sitemap.php';
        $reportUrl = str_replace('//'.Config::$server, '', Tools::url('contact')) . '?r=*';
        $response = $robotstxt;
        if (!file_exists($robotstxt)) {
            return '<br />❌file not found: ' . $robotstxt
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tagUrl
            . '<br />❌rule not found: disallow: ' . $reportUrl
            . '<br />❌rule not found: sitemap: ' . $sitemapUrl
            ;
        }
        $response .= '<br />✔️exists';
        $content = file($robotstxt);
        if (!is_array($content)) {
            return $response . ''
            . '<br />❌rule not found: user-agent: *'
            . '<br />❌rule not found: disallow: ' . $tagUrl
            . '<br />❌rule not found: disallow: ' . $reportUrl
            . '<br />❌rule not found: sitemap: ' . $sitemapUrl
            ;
        }

        $userAgentStar = false;
        $tagDisallow = false;
        $sitemap = false;
        $reportDisallow = false;

        foreach ($content as $line) {
            if (strtolower(trim($line)) == 'sitemap: ' . $sitemapUrl) {
                $sitemap = true;
                $response .= '<br />✔️rule ok: sitemap: ' . $sitemapUrl;
                continue;
            }
            if (strtolower(trim($line)) == 'user-agent: *') {
                $userAgentStar = true;
                $response .= '<br />✔️rule ok: user-agent: *';
                continue;
            }
            if (!$userAgentStar) {
                continue;
            }
            if (strtolower(trim($line)) == 'disallow: ' . $tagUrl) {
                $tagDisallow = true;
                $response .= '<br />✔️rule ok: disallow: ' . $tagUrl;
                continue;
            }
            if (strtolower(trim($line)) == 'disallow: ' . $reportUrl) {
                $reportDisallow = true;
                $response .= '<br />✔️rule ok: disallow: ' . $reportUrl;
                continue;
            }
        }
        if (!$sitemap) {
            $response .= '<br />❌rule not found: sitemap: ' . $sitemapUrl;
        }
        if (!$userAgentStar) {
            $response .= '<br />❌rule not found: user-agent: *';
        }
        if (!$tagDisallow) {
            $response .= '<br />❌rule not found: disallow: ' . $tagUrl;
        }
        if (!$reportDisallow) {
            $response .= '<br />❌rule not found: disallow: ' . $reportUrl;
        }

        return $response;
    }

    /**
     * @param $pageid
     * @return bool|string
     */
    public function addMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('add_media: Invalid PageID');

            return false;
        }

        $response = '<div style="background-color:lightgreen; padding:10px;">'
        . '<p>Add Media: pageid: <b>' . $pageid . '</b></p>';

        // Get media info from API
        $media = $this->commons->getApiImageinfo([$pageid], 0);
        if (!$media) {
            return $response . '<p>ERROR: failed to get media info from API</p></div>';
        }
        $response .= '<p>OK: media: <b>'
            . !empty($media[$pageid]['title']) ? $media[$pageid]['title'] : '?'
            . '</b></p>';

        // Save media
        if (!$this->database->saveMediaToDatabase($media)) {
            return $response . '<p>ERROR: failed to save media to database</p></div>';
        }
        $response .= '<p>OK: Saved media: <b><a href="' . Tools::url('info')
        . '?i=' . $pageid . '">info.php?i=' . $pageid . '</a></b></p>';

        if (!$this->commons->categories) {
            return $response . '<p>No Categories Found</p></div>';
        }
        foreach ($this->commons->categories as $category) {
            $response .= '+'
            . '<a href="' . Tools::url('category')
            . '?c=' . Tools::categoryUrlencode(Tools::stripPrefix($category['title']))
            . '">' . Tools::stripPrefix($category['title']) . '</a><br />';
        }
        $response .= '</div>';

        return $response;
    }

    /**
     * @param $categoryNameArray
     */
    public function importCategories($categoryNameArray)
    {
        Tools::notice("import_categories( category_name_array )");
        $this->database->beginTransaction();
        foreach ($categoryNameArray as $categoryName) {
            $categoryName = Tools::categoryUrldecode($categoryName);
            $this->database->insertCategory($categoryName);
        }
        $this->database->commit();
        $this->database->vacuum();
    }
}
