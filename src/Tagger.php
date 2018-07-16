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
    /** @var Database */
    public $database;
    /** @var Router */
    public $router;
    /** @var array  */
    public $config;
    /** @var string */
    public $customSiteFooter;
    /** @var string */
    public $customSiteHeader;
    /** @var string */
    public $category;
    /** @var int|string */
    public $mediaId;
    /** @var array */
    public $media;
    /** @var array */
    public $tags;

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
        }

        Config::setSiteInfo($siteInfo);

        $this->database->getUser();
    }

    /**
     * @param array $media
     * @param string $thumbWidth
     * @return array
     */
    public function getThumbnail(array $media = [], $thumbWidth = '')
    {
        if (!$thumbWidth || !Tools::isPositiveNumber($thumbWidth)) {
            $thumbWidth = Config::$sizeThumb;
        }
        if (!$media) {
            return [
                'url' => 'data:image/gif;base64,R0lGOD lhCwAOAMQfAP////7+/vj4+Hh4eHd3d/v'
                    .'7+/Dw8HV1dfLy8ubm5vX19e3t7fr 6+nl5edra2nZ2dnx8fMHBwYODg/b29np6e'
                    . 'ujo6JGRkeHh4eTk5LCwsN3d3dfX 13Jycp2dnevr6////yH5BAEAAB8ALAAAAAA'
                    . 'LAA4AAAVq4NFw1DNAX/o9imAsB tKpxKRd1+YEWUoIiUoiEWEAApIDMLGoRCyWi'
                    . 'KThenkwDgeGMiggDLEXQkDoTh CKNLpQDgjeAsY7MHgECgx8YR8oHwNHfwADBACG'
                    . 'h4EDA4iGAYAEBAcQIg0Dk gcEIQA7',
                'width' => $thumbWidth,
                'height' => $thumbWidth
            ];
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
     * @param $mediaId
     */
    public function includeAdminMediaFunctions($mediaId)
    {
        if (!Tools::isAdmin() || !Tools::isPositiveNumber($mediaId)) {
            return;
        }
        $this->mediaId = $mediaId;
        $this->includeTemplate('AdminMediaFunctions');
    }

    /**
     * @param string $categoryName
     */
    public function includeAdminCategoryFunctions(string $categoryName)
    {
        if (!Tools::isAdmin()) {
            return;
        }
        $this->category = $this->database->getCategory($categoryName);
        if (!$this->category) {
            Tools::error('ADMIN: category not in database');

            return;
        }

        $this->includeTemplate('AdminCategoryFunctions');
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
        $cats = $this->database->getMediaCategories($mediaId, $onlyHidden);
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
     */
    public function includeTags($mediaId)
    {
        $this->tags = $this->database->getTags('DESC');
        foreach ($this->tags as $name => $tag) {
            $this->tags[$name]['link'] = Tools::url('tag')
                . '?m=' . $mediaId
                . '&amp;t=' . $tag['id'];
        }
        $this->includeTemplate('TagBar');
    }

    /**
     * @param array $votes
     * @return string
     */
    public function displayVotes(array $votes)
    {
        if (!$votes || !is_array($votes)) {
            return '- unreviewed';
        }
        $response = '';
        foreach ($votes as $vote) {
            $response .= '+ ' . $vote['count'] . ' ' . $vote['name'] . '</a><br />';
        }
        if (empty($response)) {
            $response = '- unreviewed';
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getUserScore()
    {
        $score = 0;
        $totalUserTags = 0;
        if (!empty($this->database->userId)) {
            $totalUserTags = $this->database->getUserTagCount($this->database->userId);
        }
        $countFiles = $this->database->getFileCount();
        if ($totalUserTags && $countFiles) {
            $score = round((($totalUserTags / $countFiles)) * 100, 2);
        }

        return $score;
    }

    /**
     * @param array $media
     */
    public function includeThumbnailBox(array $media)
    {
        $this->media = $media;
        $this->includeTemplate('Thumbnail');
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
        if (in_array($mime, Config::getMimeTypesVideo())) {
            return $this->displayVideo($media);
        }
        if (in_array($mime, Config::getMimeTypesAudio())) {
            return $this->displayAudio($media);
        }

        $url = $media['thumburl'];
        $height = $media['thumbheight'];
        $width = $media['thumbwidth'];

        $aspectRatio = 1;
        if ($width && $height) {
            $aspectRatio = $width / $height;
        }
        if ($aspectRatio < 1) { // Tall media
            $width = round($aspectRatio * 100);
        }
        if ($width > 100) {
            $width = 100;
        }
        $style = 'height:100%; width:' . $width . '%;';

        return  '<div>'
            . '<img src="' . $url .'" style="' . $style . '" alt="">'
            . $this->displayAttribution($media)
            . '</div>';
    }

    /**
     * @param array $media
     * @param int|string $truncate
     * @return string
     */
    public function getArtistName(array $media = [], $truncate = 42)
    {
        if (!$media || empty($media['artist'])) {
            return 'Unknown';
        }
        $media['artist'] = strip_tags($media['artist']);
        return Tools::truncate($media['artist'], $truncate);
    }

    /**
     * @param array $media
     * @param int $truncate
     * @return string
     */
    public function getLicenseName(array $media = [], $truncate = 42)
    {
        if (!$media || empty($media['licenseshortname'])) {
            return 'Unknown';
        }
        switch ($media['licenseshortname']) {
            case 'No restrictions':
            case 'Public domain':
                $media['licenseshortname'] = 'Public Domain';
                break;
        }

        return Tools::truncate($media['licenseshortname'], $truncate);
    }

    /**
     * @param array $media
     * @param int $truncate
     * @return string
     */
    public function getMediaName(array $media = [], $truncate = 42)
    {
        if (!$media || empty($media['title'])) {
            return 'Unknown';
        }
        $title = htmlspecialchars(Tools::stripPrefix($media['title']));
        return Tools::truncate($title, $truncate);
    }

    /**
     * @param array $media
     * @param int $artistTruncate
     * @return bool|string
     */
    public function getLicensing(array $media, $artistTruncate = 42)
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
     * @deprecated
     *
     * @param array $media
     * @param int $titleTruncate
     * @param int $artistTruncate
     * @return string
     */
    public function displayAttribution(array $media, $titleTruncate = 250, $artistTruncate = 48)
    {
        $infourl = Tools::url('info') . '/' . $media['pageid'];
        $title = htmlspecialchars(Tools::stripPrefix($media['title']));

        return '<div class="mediatitle center">'
        . '<a href="' . $infourl . '" title="' . htmlentities((string) $title) . '">'
        . Tools::truncate($title, $titleTruncate)
        . '</a></div>'
        . '<div class="attribution center">'
        . '<a href="' . $infourl . '">'
        . $this->getLicensing($media, $artistTruncate)
        . '</a></div>';
    }

    /**
     * @param bool $showCustomSiteHeader
     */
    public function includeHeader($showCustomSiteHeader = true)
    {
        if (!$this->title) {
            $this->title = Config::$siteName;
        }

        $this->customSiteHeader = '';
        if (!empty(Config::$siteInfo['header'])
            && $showCustomSiteHeader
            && !Tools::isAdmin()
        ) {
            $this->customSiteHeader = Config::$siteInfo['header'];
        }

        $this->includeTemplate('HtmlHeader');
    }

    /**
     * @param bool $customSiteFooter
     */
    public function includeFooter($customSiteFooter = true)
    {
        $this->customSiteFooter = '';
        if (!empty(Config::$siteInfo['header'])
            && $customSiteFooter
            && !Tools::isAdmin()
        ) {
            $this->customSiteFooter = Config::$siteInfo['footer'];
        }
        $this->includeTemplate('Menu');
        $this->includeTemplate('HtmlFooter');
    }

    /**
     * @param $name
     */
    public function includeTemplate($name)
    {
        $view = Config::$sourceDirectory . '/Template/' . $name . '.php';
        if (!is_readable($view)) {
            Tools::error('Template Not Found: ' . $name);

            return;
        }

        /** @noinspection PhpIncludeInspection */
        include($view);
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
        print '<div class="center" style="background-color:yellow; color:black;">'
            . '<h1>' . $message . '</h1>';
        if ($extra && is_string($extra)) {
            print '<br />' . $extra;
        }
        print '</div>';
        $this->includeFooter(false);

        Tools::shutdown();
    }
}
