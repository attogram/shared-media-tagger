<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;
use Attogram\SharedMedia\Tagger\Database\Database;
use Attogram\SharedMedia\Tagger\Database\DatabaseUpdater;

/**
 * Class Tagger
 */
class Tagger
{
    /** @var string */
    public $protocol;
    /** @var string */
    public $site;
    /** @var string */
    public $title;
    /** @var bool */
    public $useBootstrap;
    /** @var bool */
    public $useJquery;
    /** @var Database */
    public $database;
    /** @var Router */
    public $router;
    /** @var array  */
    public $config;

    public $testss = ['a' => 'b'];
    /**
     * Tagger constructor.
     * @param Router $router
     * @param array $config
     */
    public function __construct(Router $router, array $config = [])
    {
        $this->router = $router;
        $this->config = $config;

        Config::setSiteUrl($router->getUriBase() . '/');
        Config::setup($this->config);

        $this->database = new Database();

        $siteInfo = $this->database->queryAsArray('SELECT * FROM site WHERE id = 1');
        if (!$siteInfo) {
            $databaseUpdater = new DatabaseUpdater();
            $databaseUpdater->setDatabase($this->database);
            $databaseUpdater->createTables();
            $databaseUpdater->seedDemo();
            Tools::debug('New site database created and seeded.');
        }

        Config::setSiteInfo($siteInfo);

        $this->database->getUser();
    }

    /**
     * @param string $message
     * @param string $extra
     */
    public function fail404($message = '', $extra = '')
    {
        header('HTTP/1.0 404 Not Found');
        $this->includeHeader(false);
        if (!$message || !is_string($message)) {
            $message = '404 Not Found';
        }
        print '<div class="box center" style="background-color:yellow; color:black;">'
            . '<h1>' . $message . '</h1>';
        if ($extra && is_string($extra)) {
            print '<br />' . $extra;
        }
        print '</div>';
        $this->includeFooter(false);

        Tools::shutdown();
    }

    /**
     * @param array $media
     * @param string $thumbWidth
     * @return array
     */
    public function getThumbnail(array $media, $thumbWidth = '')
    {
        if (!$thumbWidth || !Tools::isPositiveNumber($thumbWidth)) {
            $thumbWidth = Config::$sizeThumb;
        }
        $default = [
            'url' => 'data:image/gif;base64,R0lGOD lhCwAOAMQfAP////7+/vj4+Hh4eHd3d/v'
                    .'7+/Dw8HV1dfLy8ubm5vX19e3t7fr 6+nl5edra2nZ2dnx8fMHBwYODg/b29np6e'
                    . 'ujo6JGRkeHh4eTk5LCwsN3d3dfX 13Jycp2dnevr6////yH5BAEAAB8ALAAAAAA'
                    . 'LAA4AAAVq4NFw1DNAX/o9imAsB tKpxKRd1+YEWUoIiUoiEWEAApIDMLGoRCyWi'
                    . 'KThenkwDgeGMiggDLEXQkDoTh CKNLpQDgjeAsY7MHgECgx8YR8oHwNHfwADBACG'
                    . 'h4EDA4iGAYAEBAcQIg0Dk gcEIQA7',
            'width' => $thumbWidth,
            'height' => $thumbWidth
        ];
        if (!$media || !is_array($media)) {
            return $default;
        }
        $width = !empty($media['width']) ? $media['width'] : null;
        if (!$width) {
            $width = Config::$sizeThumb;
        }
        $height = !empty($media['height']) ? $media['height'] : null;
        if (!$height) {
            $height = Config::$sizeThumb;
        }
        if ($thumbWidth >= $width) {
            return [
                'url' => !empty($media['thumburl']) ? $media['thumburl'] : null,
                'width' => !empty($width) ? $width : null,
                'height' => !empty($height) ? $height : null,
            ];
        }
        $mime = $media['mime'];
        $filename = Tools::stripPrefix($media['title']);
        $filename = str_replace(' ', '_', $filename);
        $md5 = md5($filename);
        $thumbUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb'
            . '/' . $md5[0]
            . '/' . $md5[0] . $md5[1]
            . '/' . urlencode($filename)
            . '/' . $thumbWidth . 'px-' . urlencode($filename);
        $ratio = $width / $height;
        $thumbHeight = round($thumbWidth / $ratio);
        switch ($mime) {
            case 'application/ogg':
                $thumbUrl = str_replace('px-', 'px--', $thumbUrl);
                $thumbUrl .= '.jpg';
                break;
            case 'video/webm':
                $thumbUrl = str_replace('px-', 'px--', $thumbUrl);
                $thumbUrl .= '.jpg';
                break;
            case 'image/svg+xml':
                $thumbUrl .= '.png';
                break;
        }
        return ['url'=>$thumbUrl, 'width'=>$thumbWidth, 'height'=>$thumbHeight];
    }

    /**
     * @return string
     */
    public function displayAdminMediaListFunctions()
    {
        return
        '<div class="left pre white" style="display:inline-block; border:1px solid red; margin:2px; padding:2px;">'
        . '<input type="submit" value="Delete selected media">'
        . '<script type="text/javascript" language="javascript">'
        . "
function checkAll(formname, checktoggle) { 
    var checkboxes = new Array();
    checkboxes = document[formname].getElementsByTagName('input');
    for (var i=0; i<checkboxes.length; i++) {
        if (checkboxes[i].type == 'checkbox') { 
           checkboxes[i].checked = checktoggle; 
        } 
    } 
}
        </script>"
        . ' &nbsp; <a onclick="javascript:checkAll(\'media\', true);" href="javascript:void();">check all</a>'
        . ' &nbsp; <a onclick="javascript:checkAll(\'media\', false);" href="javascript:void();">uncheck all</a>'
        . '</div>';
    }

    /**
     * @param $mediaId
     * @return string
     */
    public function displayAdminMediaFunctions($mediaId)
    {
        if (!Tools::isAdmin() || !Tools::isPositiveNumber($mediaId)) {
            return '';
        }
        return '<div class="attribution left" style="display:inline-block;">'
        . '<a style="font-size:140%;" href="' . Tools::url('admin') . 'media?dm=' . $mediaId
        . '" title="Delete" target="admin" onclick="return confirm(\'Confirm: Delete Media #'
        . $mediaId . ' ?\');">❌</a> &nbsp; '
        . '<input type="checkbox" name="media[]" value="' . $mediaId . '" />  &nbsp; '
        . '<a style="font-size:170%;" href="' . Tools::url('admin') . 'media?am=' . $mediaId
        . '" title="Refresh" target="admin" onclick="return confirm(\'Confirm: Refresh Media #'
        . $mediaId . ' ?\');">♻</a>  &nbsp; '
        . ' <a style="font-size:140%;" href="' . Tools::url('admin') . 'curate?i=' . $mediaId. '">C</a>'
        . '</div>';
    }

    /**
     * @param string $categoryName
     * @return string
     */
    public function displayAdminCategoryFunctions(string $categoryName)
    {
        if (!Tools::isAdmin()) {
            return '';
        }
        $category = $this->database->getCategory($categoryName);
        if (!$category) {
            return '<p>ADMIN: category not in database</p>';
        }

        return '<br clear="all" />'
        . '<div class="left pre white" style="display:inline-block; border:1px solid red; padding:10px;">'
        . '<input type="submit" value="Delete selected media">'
        . '<script type="text/javascript" language="javascript">'
        . "function checkAll(formname, checktoggle) {
            var checkboxes = new Array();
            checkboxes = document[formname].getElementsByTagName('input');
            for (var i=0; i<checkboxes.length; i++) {
                if (checkboxes[i].type == 'checkbox') {
                    checkboxes[i].checked = checktoggle;
                }
            }
        }"
        . '</script>'
        . ' &nbsp; <a onclick="javascript:checkAll(\'media\', true);" href="javascript:void();">check all</a>'
        . ' &nbsp;&nbsp; <a onclick="javascript:checkAll(\'media\', false);" href="javascript:void();">uncheck all</a>'
        . '<br /><br /><a target="c" href="https://commons.wikimedia.org/wiki/'
        . Tools::categoryUrlencode($category['name']) . '">VIEW ON COMMONS</a>'
        . '<br /><br /><a href="' . Tools::url('admin') . 'category/?c='
        . Tools::categoryUrlencode($category['name']) . '">Get Category Info</a>'
        . '<br /><br /><a href="' . Tools::url('admin') . 'category/?i='
        . Tools::categoryUrlencode($category['name'])
        . '" onclick="return confirm(\'Confirm: Import Media To Category?\');">Import '
            . (!empty($category['files']) ? $category['files'] : '?')
            . ' Files into Category</a>'
        . '<br /><br /><a href="' . Tools::url('admin') . 'category/?sc='
        . Tools::categoryUrlencode($category['name'])
        . '" onclick="return confirm(\'Confirm: Add Sub-Categories?\');">Add '
            . (!empty($category['subcats']) ? $category['subcats'] : '?')
            . ' Sub-Categories</a>'
        . '<br /><br /><a href="' . Tools::url('admin') . 'media?dc='
        . Tools::categoryUrlencode($category['name'])
        . '" onclick="return confirm(\'Confirm: Clear Media from Category?\');">Clear Media from Category</a>'
        . '<br /><br /><a href="' . Tools::url('admin') . 'category/?d=' . urlencode($category['id'])
        . '" onclick="return confirm(\'Confirm: Delete Category?\');">Delete Category</a>'
        . '<br /><pre>' . print_r($category, true) . '</pre>'
        . '</form>'
        . '</div>'
        . '<br /><br />';
    }

    /**
     * @param $mediaId
     * @param bool $onlyHidden
     * @return string
     */
    public function displayCategories($mediaId, $onlyHidden = false)
    {
        if (!$mediaId || !Tools::isPositiveNumber($mediaId)) {
            return '';
        }
        $cats = $this->database->getImageCategories($mediaId, $onlyHidden);
        //$response = '<div class="xxcategories" style="width:' . Config::$sizeMedium . 'px;">';
        if (!$cats) {
            return '';
        }
        $response = '';
        foreach ($cats as $cat) {
            $response .= ''
            . '&nbsp;&nbsp; + <a href="' . Tools::url('category')
            . '/' . Tools::categoryUrlencode(Tools::stripPrefix($cat)) . '">'
            . Tools::stripPrefix($cat) . '</a><br />';
        }

        return $response; // . '</div>';
    }

    /**
     * @param int|string $mediaId
     * @return string
     */
    public function displayTags($mediaId)
    {
        $tags = $this->database->getTags('DESC');
        $response = '<div class="nobr" style="display:block; margin:auto;">';
        foreach ($tags as $tag) {
            $response .=  ''
            . '<div class="tagbutton tag' . $tag['position'] . '">'
            . '<a href="' . Tools::url('tag') . '?m=' . $mediaId
                . '&amp;t=' . $tag['id'] . '" title="' . $tag['name'] . '">'
            . $tag['display_name']
            . '</a></div>';
        }

        return $response . '</div>';
    }

    /**
     * @param array $reviews
     * @return string
     */
    public function displayReviews(array $reviews)
    {
        if (!$reviews || !is_array($reviews)) {
            return '';
        }
        $response = '';
        foreach ($reviews as $review) {
            $response .= '+<a href="' . Tools::url('tags')
                . '?o=' . $review['id'] . '">'
                . $review['count'] . ' ' . $review['name'] . '</a><br />';
        }

        return $response;
    }

    /**
     * @param int|string $categoryId
     * @return string
     */
    public function getReviewsPerCategory($categoryId)
    {
        return $this->displayReviews($this->database->getDbReviewsPerCategory($categoryId));
    }

    /**
     * includeMenu
     */
    public function includeMenu()
    {
        $space = ' &nbsp; &nbsp; ';
        $countFiles = number_format((float) $this->database->getImageCount());
        $countCategories = number_format((float) $this->database->getCategoriesCount());
        $countReviews = number_format((float) $this->database->getTotalReviewCount());
        print '<div class="menu" style="font-weight:bold;">'
        . '<span class="nobr"><a href="' . Tools::url('home') . '">' . Config::$siteName . '</a></span>' .  $space
        . '<a href="' . Tools::url('browse') . '">🔎' . $countFiles . '&nbsp;Files' . '</a>' . $space
        . '<a href="' . Tools::url('categories') . '">📂' . $countCategories . '&nbsp;Categories</a>' . $space
        . '<a href="' . Tools::url('tags') . '">🗳️' . $countReviews . '&nbsp;Tags</a>' . $space
        . '</div>';
    }

    /**
     * includeMediumMenu
     */
    public function includeMediumMenu()
    {
        $space = ' &nbsp; &nbsp; ';
        print '<div class="menu" style="font-weight:bold;">'
        . '<span class="nobr"><a href="' . Tools::url('home') . '">' . Config::$siteName . '</a></span>' .  $space
        . '<a href="' . Tools::url('browse') . '">🔎Files' . '</a>' . $space
        . '<a href="' . Tools::url('categories') . '">📂Categories</a>' . $space
        . '<a href="' . Tools::url('tags') . '">🗳️Tags</a>' . $space
        . (Tools::isAdmin() ? '<a href="' . Tools::url('admin') . '">🔧</a>' : '')
        . '</div>';
    }

    /**
     * includeSmallMenu
     */
    public function includeSmallMenu()
    {
        $space = ' ';
        print '<div class="menujcon">'
        . '<a style="font-weight:bold; font-size:85%;" href="' . Tools::url('home') . '">' . Config::$siteName . '</a>'
        . '<span style="float:right;">'
        . '<a class="menuj" title="Browse" href="' . Tools::url('browse') . '">🔎</a>' . $space
        . '<a class="menuj" title="Categories" href="' . Tools::url('categories') . '">📂</a>' . $space
        . '<a class="menuj" title="Tags" href="' . Tools::url('tags') . '">🗳️</a>' . $space
        . (Tools::isAdmin() ? '<a class="menuj" title="ADMIN" href="' . Tools::url('admin') . '">🔧</a>' : '')
        . '</span>'
        . '</div><div style="clear:both;"></div>';
    }

    /**
     * @param array $media
     * @return string
     */
    public function displayThumbnail(array $media)
    {
        $thumb = $this->getThumbnail($media);
        $pageid = !empty($media['pageid']) ? $media['pageid'] : null;
        $title = !empty($media['title']) ? $media['title'] : null;
        return '<div style="display:inline-block;text-align:center;">'
            . '<a href="' .  Tools::url('info') . '/' . $pageid . '">'
            . '<img src="' . $thumb['url'] . '"'
            . ' width="' . $thumb['width'] . '"'
            . ' height="' . $thumb['height'] . '"'
            . ' title="' . htmlentities((string) $title) . '" /></a>'
            . '</div>';
    }

    /**
     * @param array $media
     * @return string
     */
    public function displayThumbnailBox(array $media)
    {
        return '<div class="thumbnail_box">'
            . $this->displayThumbnail($media)
            . str_replace(
                ' / ',
                '<br />',
                $this->displayAttribution($media, 17, 21)
            )
            . $this->displayAdminMediaFunctions($media['pageid'])
            . '<div class="thumbnail_reviews left">'
            . $this->displayReviews($this->database->getReviews($media['pageid']))
            . '</div>'
            . '</div>';
    }

    /**
     * @param array $media
     * @return string
     */
    public function displayVideo(array $media)
    {
        $mime = $media['mime'];
        $url = $media['url'];
        $height = $media['thumbheight'];
        $poster = $media['thumburl'];

        $divwidth = $width = $media['thumbwidth'];
        if ($divwidth < Config::$sizeMedium) {
            $divwidth = Config::$sizeMedium;
        }

        return '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<video width="'. $divwidth . '" height="' . $height . '" poster="' . $poster
        . '" onclick="this.paused ? this.play() : this.pause();" controls loop>'
        . '<source src="' . $url . '" type="' . $mime . '">'
        . '</video>'
        . $this->displayAttribution($media)
        . '</div>';
    }

    /**
     * @param array $media
     * @return string
     */
    public function displayAudio(array $media)
    {
        $mime = $media['mime'];
        $url = $media['url'];
        $height = $media['thumbheight'];
        $poster = $media['thumburl'];

        $divwidth = $width = $media['thumbwidth'];
        if ($divwidth < Config::$sizeMedium) {
            $divwidth = Config::$sizeMedium;
        }

        return '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<audio width="'. $width . '" height="' . $height . '" poster="' . $poster
        . '" onclick="this.paused ? this.play() : this.pause();" controls loop>'
        . '<source src="' . $url . '" type="' . $mime . '">'
        . '</audio>'
        . $this->displayAttribution($media)
        . '</div>';
    }

    /**
     * @param array $media
     * @return bool|string
     */
    public function displayMedia(array $media)
    {
        if (!$media || !is_array($media)) {
            Tools::error('displayImage: ERROR: no image array');

            return false;
        }
        $mime = !empty($media['mime']) ? $media['mime'] : null;
        if (in_array($mime, Config::$mimeTypesVideo)) {
            return $this->displayVideo($media);
        }
        if (in_array($mime, Config::$mimeTypesAudio)) {
            return $this->displayAudio($media);
        }

        $url = $media['thumburl'];
        $height = $media['thumbheight'];
        $divwidth = $width = $media['thumbwidth'];
        if ($divwidth < Config::$sizeMedium) {
            $divwidth = Config::$sizeMedium;
        }
        $infourl =  Tools::url('info') . '/' . $media['pageid'];

        return  '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<a href="' . $infourl . '">'
        . '<img src="'. $url .'" height="'. $height .'" width="'. $width . '" alt=""></a>'
        . $this->displayAttribution($media)
        . '</div>';
    }

    /**
     * @param array $media
     * @param int $artistTruncate
     * @return bool|string
     */
    public function displayLicensing(array $media, $artistTruncate = 42)
    {
        if (!$media || !is_array($media)) {
            Tools::error('displayLicensing: Media Not Found');

            return false;
        }
        $artist = !empty($media['artist']) ? $media['artist'] : null;
        if (!$artist) {
            $artist = 'Unknown';
            $copyright = '';
        } else {
            $artist = Tools::truncate(strip_tags($artist), $artistTruncate);
            $copyright = '&copy; ';
        }
        $licenseshortname = !empty($media['licenseshortname']) ? $media['licenseshortname'] : null;
        switch ($licenseshortname) {
            case 'No restrictions':
            case 'Public domain':
                $licenseshortname = 'Public Domain';
                $copyright = '';
                break;
        }

        return "$copyright $artist / $licenseshortname";
    }

    /**
     * @param array $media
     * @param int $titleTruncate
     * @param int $artistTruncate
     * @return string
     */
    public function displayAttribution(array $media, $titleTruncate = 250, $artistTruncate = 48)
    {
        $infourl = Tools::url('info') . '/' . $media['pageid'];
        $title = htmlspecialchars(Tools::stripPrefix($media['title']));

        return '<div class="mediatitle left">'
        . '<a href="' . $infourl . '" title="' . htmlentities((string) $title) . '">'
        . Tools::truncate($title, $titleTruncate)
        . '</a></div>'
        . '<div class="attribution left">'
        . '<a href="' . $infourl . '">'
        . $this->displayLicensing($media, $artistTruncate)
        . '</a></div>';
    }

    /**
     * displaySiteHeader
     */
    public function displaySiteHeader()
    {
        print !empty(Config::$siteInfo['header']) ? Config::$siteInfo['header'] : null;
    }

    /**
     * displaySiteFooter
     */
    public function displaySiteFooter()
    {
        print !empty(Config::$siteInfo['footer']) ? Config::$siteInfo['footer'] : null;
    }

    /**
     * @param bool $showSiteHeader
     */
    public function includeHeader($showSiteHeader = true)
    {
        if (!$this->title) {
            $this->title = Config::$siteName;
        }
        print "<!doctype html>\n"
        . '<html><head><title>' . $this->title . '</title>'
        . '<meta charset="utf-8" />'
        . '<meta name="viewport" content="initial-scale=1" />'
        . '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
        if ($this->useBootstrap) {
            print '<link rel="stylesheet" href="' . Tools::url('bootstrap_css') . '" />'
            . '<meta name="viewport" content="width=device-width, initial-scale=1" />'
            . '<!--[if lt IE 9]>'
            . '<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>'
            . '<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>'
            . '<![endif]-->';
        }
        if ($this->useBootstrap || $this->useJquery) {
            print '<script src="' . Tools::url('jquery') . '"></script>';
        }
        if ($this->useBootstrap) {
            print '<script src="' . Tools::url('bootstrap_js') . '"></script>';
        }
        print '<link rel="stylesheet" type="text/css" href="' . Tools::url('css') . '" />'
        . '<link rel="icon" type="image/png" href="' . Tools::url('home') . 'favicon.ico" />'
        . '</head><body>';

        if ($showSiteHeader) {
            $this->displaySiteHeader();
        }
    }

    /**
     * @param bool $showSiteFooter
     */
    public function includeFooter($showSiteFooter = true)
    {
        $this->includeMenu();
        print '<footer><div class="menu" style="line-height:2; font-size:80%;">';
        if (empty(Config::$setup['hide_hosted_by']) || !Config::$setup['hide_hosted_by']) {
            $serverName = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
            print '<span class="nobr">Hosted by <b><a href="//' . $serverName . '/">' . $serverName . '</a></b></span>';
        }
        print ' &nbsp; &nbsp; &nbsp; &nbsp; ';

        print '<span class="nobr">Powered by <b>'
        . '<a target="c" href="' . Tools::url('github_smt') . '">'
        . 'Shared Media Tagger v' . SHARED_MEDIA_TAGGER . '</a></b></span>';

        if (!empty($_SESSION['user'])) {
            print '<br /><a href="'
                . Tools::url('admin')
                . '">🔧 Admin: <b>' . $_SESSION['user'] . '</b></a>'
                . '  &nbsp; &nbsp; '
                . '<a href="' . Tools::url('logout') . '">Logout</a>';
        }

        print '</div></footer>';

        if ($showSiteFooter) {
            $this->displaySiteFooter();
        }

        print '</body></html>';
    }
}
