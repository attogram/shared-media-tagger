<?php

declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Class Tagger
 */
class Tagger
{
    /** @var string http: or https: */
    public $protocol;
    /** @var array array of [page_name] = page_url */
    public $links;
    public $userId;
    public $installDirectory;
    public $server;
    public $setup;
    public $site;
    public $siteInfo;
    public $siteName;
    public $siteUrl;
    public $sizeMedium;
    public $sizeThumb;
    public $tagId;
    public $tagName;
    public $totalFilesReviewedCount;
    /** @var string Page <title> */
    public $title;
    public $useBootstrap;
    public $useJquery;
    /** @var Database */
    public $database;

    /**
     * SharedMediaTagger constructor.
     */
    public function __construct()
    {
        global $router, $setup;

        $this->database = new Database();
        $this->setup = [];
        if (is_array($setup)) {
            $this->setup = $setup;
        }
        $this->installDirectory = realpath(__DIR__ . '/..');
        $this->sizeMedium = 325;
        $this->sizeThumb = 100;
        $this->server = $_SERVER['SERVER_NAME'];
        if (isset($this->setup['site_url'])) {
            $this->siteUrl = $setup['site_url'];
        } else {
            $this->siteUrl = $router->getUriBase() . '/';
        }
        $this->setSiteInfo();
        $this->links = [
            'home'       => $this->siteUrl . '',
            'css'        => $this->siteUrl . 'css.css',
            'jquery'        => $this->siteUrl . 'use/jquery.min.js',
            'bootstrap_js'  => $this->siteUrl . 'use/bootstrap/js/bootstrap.min.js',
            'bootstrap_css' => $this->siteUrl . 'use/bootstrap/css/bootstrap.min.css',
            'info'       => $this->siteUrl . 'info.php',
            'browse'     => $this->siteUrl . 'browse.php',
            'categories' => $this->siteUrl . 'categories.php',
            'category'   => $this->siteUrl . 'category.php',
            'about'      => $this->siteUrl . 'about.php',
            'reviews'    => $this->siteUrl . 'reviews.php',
            'admin'      => $this->siteUrl . 'admin/',
            'contact'    => $this->siteUrl . 'contact.php',
            'tag'        => $this->siteUrl . 'tag.php',
            'users'      => $this->siteUrl . 'users.php',
            'github_smt' => 'https://github.com/attogram/shared-media-tagger',
        ];
        $this->getUser();
        if (isset($_GET['logoff'])) {
            $this->adminLogoff();
        }
    }

    // SMT - Utils

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

        exit;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        if (isset($this->protocol)) {
            return $this->protocol;
        }
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            return $this->protocol = 'https:';
        }

        return $this->protocol = 'http:';
    }

    /**
     * @param string $link
     * @return bool|mixed
     */
    public function url($link = '')
    {
        if (!$link || !isset($this->links[$link])) {
            Tools::error("::url: Link Not Found: $link");

            return false;
        }

        return $this->links[$link];
    }

    // SMT - Media

    /**
     * @param $pageid
     * @return array|bool
     */
    public function getMedia($pageid)
    {
        if (!$pageid || !Tools::isPositiveNumber($pageid)) {
            Tools::error('getMedia: ERROR no id');
            return false;
        }
        $sql = 'SELECT * FROM media WHERE pageid = :pageid';

        if ($this->siteInfo['curation'] == 1 && !$this->isAdmin()) {
            $sql .= " AND curated = '1'";
        }
        return $this->database->queryAsArray($sql, [':pageid'=>$pageid]);
    }

    /**
     * @param string $media
     * @param string $thumbWidth
     * @return array
     */
    public function getThumbnail($media = '', $thumbWidth = '')
    {
        if (!$thumbWidth || !Tools::isPositiveNumber($thumbWidth)) {
            $thumbWidth = $this->sizeThumb;
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
            $width = $this->sizeThumb;
        }
        $height = !empty($media['height']) ? $media['height'] : null;
        if (!$height) {
            $height = $this->sizeThumb;
        }
        if ($thumbWidth >= $width) {
            return [
                'url' => !empty($media['thumburl']) ? $media['thumburl'] : null,
                'width' => !empty($width) ? $width : null,
                'height' => !empty($height) ? $height : null,
            ];
        }
        $mime = $media['mime'];
        $filename = $this->stripPrefix($media['title']);
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

    // SMT - Admin

    /**
     * @return bool
     */
    public function isAdmin()
    {
        if (isset($_COOKIE['admin']) && $_COOKIE['admin'] == 1) {
            return true;
        }
        return false;
    }

    /**
     *
     */
    public function adminLogoff()
    {
        if (!$this->isAdmin()) {
            return;
        }
        unset($_COOKIE['admin']);
        setcookie('admin', null, -1, '/');
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
        if (!$this->isAdmin()) {
            return '';
        }
        if (!Tools::isPositiveNumber($mediaId)) {
            return '';
        }
        return ''
        . '<div class="attribution left" style="display:inline-block; float:right;">'
        . '<a style="font-size:140%;" href="' . $this->url('admin') . 'media.php?dm=' . $mediaId
        . '" title="Delete" target="admin" onclick="return confirm(\'Confirm: Delete Media #'
        . $mediaId . ' ?\');">❌</a>'
        . '<input type="checkbox" name="media[]" value="' . $mediaId . '" />'
        . '<a style="font-size:170%;" href="' . $this->url('admin') . 'media.php?am=' . $mediaId
        . '" title="Refresh" target="admin" onclick="return confirm(\'Confirm: Refresh Media #'
        . $mediaId . ' ?\');">♻</a>'
        . ' <a style="font-size:140%;" href="' . $this->url('admin') . 'curate.php?i=' . $mediaId. '">C</a>'
        . '</div>';
    }

    /**
     * @param $categoryName
     * @return string
     */
    public function displayAdminCategoryFunctions($categoryName)
    {
        if (!$this->isAdmin()) {
            return '';
        }
        $category = $this->getCategory($categoryName);
        if (!$category) {
            return '<p>ADMIN: category not in database</p>';
        }
        $response = '<br clear="all" />'
        . '<div class="left pre white" style="display:inline-block; border:1px solid red; padding:10px;">'
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
}"
        . '</script>'
        . ' &nbsp; <a onclick="javascript:checkAll(\'media\', true);" href="javascript:void();">check all</a>'
        . ' &nbsp;&nbsp; <a onclick="javascript:checkAll(\'media\', false);" href="javascript:void();">uncheck all</a>'
        . '<br /><br /><a target="commons" href="https://commons.wikimedia.org/wiki/'
        . $this->categoryUrlencode($category['name']) . '">VIEW ON COMMONS</a>'
        . '<br /><br /><a href="' . $this->url('admin') . 'category.php/?c='
        . $this->categoryUrlencode($category['name']) . '">Get Category Info</a>'
        . '<br /><br /><a href="' . $this->url('admin') . 'category.php/?i='
        . $this->categoryUrlencode($category['name'])
        . '" onclick="return confirm(\'Confirm: Import Media To Category?\');">Import '
            . !empty($category['files']) ? $category['files'] : '?'
            . ' Files into Category</a>'
        . '<br /><br /><a href="' . $this->url('admin') . 'category.php/?sc='
        . $this->categoryUrlencode($category['name'])
        . '" onclick="return confirm(\'Confirm: Add Sub-Categories?\');">Add '
            . !empty($category['subcats']) ? $category['subcats'] : '?'
            . ' Sub-Categories</a>'
        . '<br /><br /><a href="' . $this->url('admin') . 'media.php?dc='
        . $this->categoryUrlencode($category['name'])
        . '" onclick="return confirm(\'Confirm: Clear Media from Category?\');">Clear Media from Category</a>'
        . '<br /><br /><a href="' . $this->url('admin') . 'category.php/?d=' . urlencode($category['id'])
        . '" onclick="return confirm(\'Confirm: Delete Category?\');">Delete Category</a>'
        . '<br /><pre>' . print_r($category, true) . '</pre>'
        . '</form>'
        . '</div><br /><br />';

        return $response;
    }

    // SMT - User

    /**
     * @param int $limit
     * @param string $orderby
     * @return array|bool
     */
    public function getUsers($limit = 100, $orderby = 'last DESC, page_views DESC')
    {
        $sql = 'SELECT * FROM user';
        $sql .= ' ORDER BY ' . $orderby;
        $sql .= ' LIMIT ' . $limit;
        $users = $this->database->queryAsArray($sql);
        if (isset($users[0])) {
            return $users;
        }

        return [];
    }

    /**
     * @param bool $createNew
     * @return bool
     */
    public function getUser($createNew = false)
    {
        $ipAddress = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        $host = !empty($_SERVER['REMOTE_HOST']) ? $_SERVER['REMOTE_HOST'] : null;
        if (!$host) {
            $host = $ipAddress;
        }
        $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        $user = $this->database->queryAsArray(
            'SELECT id FROM user WHERE ip = :ip_address AND host = :host AND user_agent = :user_agent',
            [':ip_address' => $ipAddress, ':host' => $host, ':user_agent' => $userAgent]
        );
        if (!isset($user[0]['id'])) {
            if ($createNew) {
                return $this->newUser($ipAddress, $host, $userAgent);
            }
            $this->userId = 0;

            return false;
        }
        $this->userId = $user[0]['id'];

        return true;
    }

    /**
     * @param $userId
     * @return array
     */
    public function getUserTagging($userId)
    {
        $tags = $this->database->queryAsArray(
            'SELECT m.*, ut.tag_id, ut.count
            FROM user_tagging AS ut, media AS m
            WHERE ut.user_id = :user_id
            AND ut.media_pageid = m.pageid
            ORDER BY ut.media_pageid
            LIMIT 100  -- TMP',
            [':user_id' => $userId]
        );
        if ($tags) {
            return $tags;
        }

        return [];
    }

    /**
     * @return bool
     */
    public function saveUserLastTagTime()
    {
        return $this->database->queryAsBool(
            'UPDATE user SET last = :last WHERE id = :user_id',
            [':user_id' => $this->userId, ':last' => gmdate('Y-m-d H:i:s')]
        );
    }

    /**
     * @return bool
     */
    public function saveUserView()
    {
        if (!$this->userId) {
            return false;
        }
        $view = $this->database->queryAsBool(
            'UPDATE user SET page_views = page_views + 1, last = :last WHERE id = :id',
            [':id' => $this->userId, ':last' => gmdate('Y-m-d H:i:s')]
        );
        if ($view) {
            return true;
        }

        return false;
    }

    /**
     * @param $ipAddress
     * @param $host
     * @param $userAgent
     * @return bool
     */
    public function newUser($ipAddress, $host, $userAgent)
    {
        if ($this->database->queryAsBool(
            'INSERT INTO user (
                ip, host, user_agent, page_views, last
            ) VALUES (
                :ip_address, :host, :user_agent, 0, :last
            )',
            [
                ':ip_address' => $ipAddress,
                ':host' => $host,
                ':user_agent' => $userAgent,
                ':last' => gmdate('Y-m-d H:i:s')
            ]
        )
        ) {
            $this->userId = $this->database->lastInsertId;

            return true;
        }
        $this->userId = 0;

        return false;
    }

    // SMT - Category

    /**
     * @param $mediaId
     * @return bool|string
     */
    public function displayCategories($mediaId)
    {
        if (!$mediaId || !Tools::isPositiveNumber($mediaId)) {
            return false;
        }
        $cats = $this->getImageCategories($mediaId);
        $response = '<div class="categories" style="width:' . $this->sizeMedium . 'px;">';
        if (!$cats) {
            return $response . '<em>Uncategorized</em></div>';
        }
        $hidden = [];
        foreach ($cats as $cat) {
            if ($this->isHiddenCategory($cat)) {
                $hidden[] = $cat;
                continue;
            }
            $response .= ''
            . '+<a href="' . $this->url('category')
            . '?c=' . $this->categoryUrlencode($this->stripPrefix($cat)) . '">'
            . $this->stripPrefix($cat) . '</a><br />';
        }
        if (!$hidden) {
            return $response . '</div>';
        }
        $response .= '<br /><div style="font-size:80%;">';
        foreach ($hidden as $hcat) {
            $response .= '+<a href="' . $this->url('category')
            . '?c=' . $this->categoryUrlencode($this->stripPrefix($hcat)) . '">'
            . $this->stripPrefix($hcat) . '</a><br />';
        }

        return $response . '</div></div>';
    }

    /**
     * @param $string
     * @return null|string|string[]
     */
    public function stripPrefix($string)
    {
        if (!$string || !is_string($string)) {
            return $string;
        }

        return preg_replace(['/^File:/', '/^Category:/'], '', $string);
    }

    /**
     * @param $category
     * @return mixed
     */
    public function categoryUrldecode($category)
    {
        return str_replace('_', ' ', urldecode($category));
    }

    /**
     * @param $category
     * @return mixed
     */
    public function categoryUrlencode($category)
    {
        return str_replace('+', '_', str_replace('%3A', ':', urlencode($category)));
    }

    /**
     * @param $name
     * @return array|mixed
     */
    public function getCategory($name)
    {
        $response = $this->database->queryAsArray(
            'SELECT * FROM category WHERE name = :name',
            [':name' => $name]
        );
        if (!isset($response[0]['id'])) {
            return [];
        }

        return $response[0];
    }

    /**
     * @param $categoryName
     * @return int
     */
    public function getCategorySize($categoryName)
    {
        $sql = 'SELECT count(c2m.id) AS size
                FROM category2media AS c2m, category AS c
                WHERE c.name = :name
                AND c2m.category_id = c.id';
        if ($this->siteInfo['curation'] == 1) {
            $sql = "SELECT count(c2m.id) AS size
                        FROM category2media AS c2m, category AS c, media as m
                        WHERE c.name = :name
                        AND c2m.category_id = c.id
                        AND m.pageid = c2m.media_pageid
                        AND m.curated = '1'";
        }
        $response = $this->database->queryAsArray($sql, [':name' => $categoryName]);
        if (isset($response[0]['size'])) {
            return $response[0]['size'];
        }
        Tools::error("getCategorySize($categoryName) ERROR: 0 size");

        return 0;
    }

    /**
     * @return array
     */
    public function getCategoryList()
    {
        $sql = 'SELECT name FROM category ORDER BY name';
        $response = $this->database->queryAsArray($sql);
        $return = [];
        if (!$response || !is_array($response)) {
            return $return;
        }
        foreach ($response as $name) {
            $return[] = $name['value']['name'];
        }

        return $return;
    }

    /**
     * @param $pageid
     * @return array
     */
    public function getImageCategories($pageid)
    {
        $error = ['Category database unavailable'];
        if (!$pageid|| !Tools::isPositiveNumber($pageid)) {
            return $error;
        }
        $response = $this->database->queryAsArray(
            'SELECT category.name
            FROM category, category2media
            WHERE category2media.category_id = category.id
            AND category2media.media_pageid = :pageid
            ORDER BY category.name',
            [':pageid' => $pageid]
        );
        if (!isset($response[0]['name'])) {
            return $error;
        }
        $cats = [];
        foreach ($response as $cat) {
            $cats[] = $cat['name'];
        }

        return $cats;
    }

    /**
     * @param $categoryName
     * @return int
     */
    public function getCategoryIdFromName($categoryName)
    {
        $response = $this->database->queryAsArray(
            'SELECT id FROM category WHERE name = :name',
            [':name' => $categoryName]
        );
        if (!isset($response[0]['id'])) {
            return 0;
        }

        return $response[0]['id'];
    }

    /**
     * @param $categoryName
     * @return array
     */
    public function getMediaInCategory($categoryName)
    {
        $categoryId = $this->getCategoryIdFromName($categoryName);
        if (!$categoryId) {
            Tools::error('::getMediaInCategory: No ID found for: ' . $categoryName);

            return [];
        }
        $sql = 'SELECT media_pageid
                FROM category2media
                WHERE category_id = :category_id
                ORDER BY media_pageid';
        $response = $this->database->queryAsArray($sql, [':category_id' => $categoryId]);
        if ($response === false) {
            Tools::error('ERROR: unable to access categor2media table.');

            return [];
        }
        if (!$response) {
            return [];
        }
        $return = [];
        foreach ($response as $media) {
            $return[] = $media['media_pageid'];
        }

        return $return;
    }

    /**
     * @param $categoryIdArray
     * @return int
     */
    public function getCountLocalFilesPerCategory($categoryIdArray)
    {
        if (!is_array($categoryIdArray)) {
            Tools::error('getCountLocalFilesPerCategory: invalid category array');

            return 0;
        }
        $locals = $this->database->queryAsArray(
            'SELECT count(category_id) AS count
            FROM category2media
            WHERE category_id IN ( :category_id )',
            [':category_id' => implode($categoryIdArray, ', ')]
        );
        if ($locals && isset($locals[0]['count'])) {
            return $locals[0]['count'];
        }

        return 0;
    }

    /**
     * @param $categoryName
     * @return bool
     */
    public function isHiddenCategory($categoryName)
    {
        if (!$categoryName) {
            return false;
        }
        $sql = 'SELECT id FROM category WHERE hidden = 1 AND name = :category_name';
        $bind = [':category_name' => $categoryName];
        if ($this->database->queryAsArray($sql, $bind)) {
            return true;
        }

        return false;
    }

    // SMT - Tag

    /**
     * @param $mediaId
     * @return string
     */
    public function displayTags($mediaId)
    {
        $tags = $this->getTags();
        $response = '<div class="nobr" style="display:block; margin:auto;">';
        foreach ($tags as $tag) {
            $response .=  ''
            . '<div class="tagbutton tag' . $tag['position'] . '">'
            . '<a href="' . $this->url('tag') . '?m=' . $mediaId
                . '&amp;t=' . $tag['id'] . '" title="' . $tag['name'] . '">'
            . $tag['display_name']
            . '</a></div>';
        }

        return $response . '</div>';
    }

    /**
     * @param $reviews
     * @return string
     */
    public function displayReviews($reviews)
    {
        if (!$reviews) {
            return '';
        }
        $response = '';
        foreach ($reviews as $review) {
            $response .= '+<a href="' . $this->url('reviews')
                . '?o=reviews.' . urlencode($review['name']) . '">'
                . $review['count'] . ' ' . $review['name'] . '</a><br />';
        }

        return $response;
    }

    /**
     * @param $name
     * @return int
     */
    public function getTagIdByName($name)
    {
        if (isset($this->tagId[$name])) {
            return $this->tagId[$name];
        }
        $tag = $this->database->queryAsArray(
            'SELECT id FROM tag WHERE name = :name LIMIT 1',
            [':name' => $name]
        );
        if (isset($tag[0]['id'])) {
            return $this->tagId[$name] = $tag[0]['id'];
        }

        return $this->tagId[$name] = 0;
    }

    /**
     * @param $tagId
     * @return mixed
     */
    public function getTagNameById($tagId)
    {
        if (isset($this->tagName[$tagId])) {
            return $this->tagName[$tagId];
        }
        $tag = $this->database->queryAsArray(
            'SELECT name FROM tag WHERE id = :id LIMIT 1',
            [':id' => $tagId]
        );
        if (isset($tag[0]['name'])) {
            return $this->tagName[$tagId] = $tag[0]['name'];
        }

        return $this->tagName[$tagId] = $tagId;
    }

    /**
     * @return array|bool
     */
    public function getTags()
    {
        if (isset($this->tags)) {
            reset($this->tags);

            return $this->tags;
        }
        $tags = $this->database->queryAsArray('SELECT * FROM tag ORDER BY position');
        if (!$tags) {
            return $this->tags = [];
        }

        return $this->tags = $tags;
    }

    /**
     * @param $pageid
     * @return string
     */
    public function getReviews($pageid)
    {
        $reviews = $this->database->queryAsArray(
            'SELECT t.tag_id, t.count, tag.*
            FROM tagging AS t, tag
            WHERE t.media_pageid = :media_pageid
            AND tag.id = t.tag_id
            AND t.count > 0
            ORDER BY tag.position',
            [':media_pageid'=>$pageid]
        );

        return $this->displayReviews($reviews);
    }

    /**
     * @param $categoryId
     * @return string
     */
    public function getReviewsPerCategory($categoryId)
    {
        return $this->displayReviews($this->getDbReviewsPerCategory($categoryId));
    }

    /**
     * @param $categoryId
     * @return array|bool
     */
    public function getDbReviewsPerCategory($categoryId)
    {
        $reviews = $this->database->queryAsArray(
            'SELECT SUM(t.count) AS count, tag.*
            FROM tagging AS t,
                 tag,
                 category2media AS c2m
            WHERE tag.id = t.tag_id
            AND c2m.media_pageid = t.media_pageid
            AND c2m.category_id = :category_id
            AND t.count > 0
            GROUP BY (tag.id)
            ORDER BY tag.position',
            [':category_id' => $categoryId]
        );

        return $reviews;
    }

    /**
     * @return int
     */
    public function getTotalFilesReviewedCount()
    {
        if (isset($this->totalFilesReviewedCount)) {
            return $this->totalFilesReviewedCount;
        }
        $response = $this->database->queryAsArray('SELECT COUNT( DISTINCT(media_pageid) ) AS total FROM tagging');
        if (isset($response[0]['total'])) {
            return $this->totalFilesReviewedCount = $response[0]['total'];
        }

        return $this->totalFilesReviewedCount = 0;
    }

    // SMT - Menus

    /**
     *
     */
    public function includeMenu()
    {
        $space = ' &nbsp; &nbsp; ';
        $countFiles = number_format((float) $this->database->getImageCount());
        $countCategories = number_format((float) $this->database->getCategoriesCount());
        $countReviews = number_format((float) $this->database->getTotalReviewCount());
        $countUsers = number_format((float) $this->database->getUserCount());
        print '<div class="menu" style="font-weight:bold;">'
        . '<span class="nobr"><a href="' . $this->url('home') . '">' . $this->siteName . '</a></span>' .  $space
        . '<a href="' . $this->url('browse') . '">🔎' . $countFiles . '&nbsp;Files' . '</a>' . $space
        . '<a href="' . $this->url('categories') . '">📂' . $countCategories . '&nbsp;Categories</a>' . $space
        . '<a href="' . $this->url('reviews') . '">🗳' . $countReviews . '&nbsp;Reviews</a>' . $space
        . '<a href="'. $this->url('users') . ($this->userId ? '?i=' . $this->userId : '') . '">'
            . $countUsers .'&nbsp;Users</a>' . $space
        . '<a href="' . $this->url('contact') . '">Contact</a>' . $space
        . '<a href="'. $this->url('about') . '">❔About</a>'
        . ($this->isAdmin() ? $space . '<a href="' . $this->url('admin') . '">🔧</a>' : '')
        . '</div>';
    }

    /**
     *
     */
    public function includeMediumMenu()
    {
        $space = ' &nbsp; &nbsp; ';
        print '<div class="menu" style="font-weight:bold;">'
        . '<span class="nobr"><a href="' . $this->url('home') . '">' . $this->siteName . '</a></span>' .  $space
        . '<a href="' . $this->url('browse') . '">🔎Files' . '</a>' . $space
        . '<a href="' . $this->url('categories') . '">📂Categories</a>' . $space
        . '<a href="' . $this->url('reviews') . '">🗳Reviews</a>' . $space
        . '<a href="'. $this->url('users') . ($this->userId ? '?i=' . $this->userId : '') . '">Users</a>' . $space
        . '<a href="' . $this->url('contact') . '">Contact</a>' . $space
        . '<a href="'. $this->url('about') . '">❔About</a>'
        . ($this->isAdmin() ? $space . '<a href="' . $this->url('admin') . '">🔧</a>' : '')
        . '</div>';
    }

    /**
     *
     */
    public function includeSmallMenu()
    {
        $space = ' ';
        print '<div class="menujcon">'
        . '<a style="font-weight:bold; font-size:85%;" href="' . $this->url('home') . '">' . $this->siteName . '</a>'
        . '<span style="float:right;">'
        . '<a class="menuj" title="Browse" href="' . $this->url('browse') . '">🔎</a>' . $space
        . '<a class="menuj" title="Categories" href="' . $this->url('categories') . '">📂</a>' . $space
        . '<a class="menuj" title="Reviews" href="' . $this->url('reviews') . '">🗳</a>' . $space
        . '<a class="menuj" title="About" href="' . $this->url('about') . '">❔</a>' . $space
        . ($this->isAdmin() ? '<a class="menuj" title="ADMIN" href="' . $this->url('admin') . '">🔧</a>' : '')
        . '</span>'
        . '</div><div style="clear:both;"></div>';
    }

    // SMT - Shared Media Tagger

    /**
     * @return bool
     */
    public function setSiteInfo()
    {
        $response = $this->database->queryAsArray('SELECT * FROM site WHERE id = 1');
        if (!$response || !isset($response[0]['id'])) {
            $this->siteName = 'Shared Media Tagger';
            $this->siteInfo = [];
            $this->siteInfo['curation'] = $this->database->curation = 0;

            return false;
        }
        $this->siteName = !empty($response[0]['name']) ? $response[0]['name'] : null;
        $this->siteInfo = $response[0];
        if (!isset($this->siteInfo['curation'])) {
            $this->siteInfo['curation'] = 0;
        }

        $this->database->curation = $this->siteInfo['curation'];
        return true;
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
            . '<a href="' .  $this->url('info') . '?i=' . $pageid . '">'
            . '<img src="' . $thumb['url'] . '"'
            . ' width="' . $thumb['width'] . '"'
            . ' height="' . $thumb['height'] . '"'
            . ' title="' . htmlentities($title) . '" /></a>'
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
            . $this->getReviews($media['pageid'])
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
        //$width = $media['thumbwidth'];
        $height = $media['thumbheight'];
        $poster = $media['thumburl'];

        //if (!$width || $width > $this->sizeMedium) {
        //    $height = $this->get_resized_height($width, $height, $this->sizeMedium); // @TODO find
        //}
        $divwidth = $width = $media['thumbwidth'];
        if ($divwidth < $this->sizeMedium) {
            $divwidth = $this->sizeMedium;
        }

        return '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<video width="'. $divwidth . '" height="' . $height . '" poster="' . $poster
        . '" onclick="this.paused ? this.play() : this.pause();" controls loop>'
        . '<source src="' . $url . '" type="' . $mime . '">'
        . '</video>'
        . $this->displayAttribution($media)
        . $this->displayAdminMediaFunctions($media['pageid'])
        . '</div>';
    }

    /**
     * @param $media
     * @return string
     */
    public function displayAudio($media)
    {
        $mime = $media['mime'];
        $url = $media['url'];
        //$width = $media['thumbwidth'];
        $height = $media['thumbheight'];
        $poster = $media['thumburl'];

        //if (!$width || $width > $this->sizeMedium) {
        //    $height = $this->get_resized_height($width, $height, $this->sizeMedium );
        //}
        $divwidth = $width = $media['thumbwidth'];
        if ($divwidth < $this->sizeMedium) {
            $divwidth = $this->sizeMedium;
        }

        return '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<audio width="'. $width . '" height="' . $height . '" poster="' . $poster
        . '" onclick="this.paused ? this.play() : this.pause();" controls loop>'
        . '<source src="' . $url . '" type="' . $mime . '">'
        . '</audio>'
        . $this->displayAttribution($media)
        . $this->displayAdminMediaFunctions($media['pageid'])
        . '</div>';
    }

    /**
     * @param string $media
     * @return bool|string
     */
    public function displayImage($media = '')
    {
        if (!$media || !is_array($media)) {
            Tools::error('displayImage: ERROR: no image array');

            return false;
        }
        $mime = !empty($media['mime']) ? $media['mime'] : null;
        $video = ['application/ogg','video/webm'];
        if (in_array($mime, $video)) {
            return $this->displayVideo($media);
        }

        $audio = ['audio/x-flac'];
        if (in_array($mime, $audio)) {
            return $this->displayAudio($media);
        }

        $url = $media['thumburl'];
        $height = $media['thumbheight'];
        $divwidth = $width = $media['thumbwidth'];
        if ($divwidth < $this->sizeMedium) {
            $divwidth = $this->sizeMedium;
        }
        $infourl =  $this->url('info') . '?i=' . $media['pageid'];

        return  '<div style="width:' . $divwidth . 'px; margin:auto;">'
        . '<a href="' . $infourl . '">'
        . '<img src="'. $url .'" height="'. $height .'" width="'. $width . '" alt=""></a>'
        . $this->displayAttribution($media)
        . $this->displayAdminMediaFunctions($media['pageid'])
        . '</div>';
    }

    /**
     * @param $media
     * @param int $artistTruncate
     * @return bool|string
     */
    public function displayLicensing($media, $artistTruncate = 42)
    {
        if (!$media || !is_array($media)) {
            Tools::error('::displayLicensing: Media Not Found');

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
     * @param $media
     * @param int $titleTruncate
     * @param int $artistTruncate
     * @return string
     */
    public function displayAttribution($media, $titleTruncate = 250, $artistTruncate = 48)
    {
        $infourl = $this->url('info') . '?i=' . $media['pageid'];
        $title = htmlspecialchars($this->stripPrefix($media['title']));

        return '<div class="mediatitle left">'
        . '<a href="' . $infourl . '" title="' . htmlentities($title) . '">'
        . Tools::truncate($title, $titleTruncate)
        . '</a></div>'
        . '<div class="attribution left">'
        . '<a href="' . $infourl . '">'
        . $this->displayLicensing($media, $artistTruncate)
        . '</a></div>';
    }

    /**
     *
     */
    public function displaySiteHeader()
    {
        print !empty($this->siteInfo['header']) ? $this->siteInfo['header'] : null;
    }

    /**
     *
     */
    public function displaySiteFooter()
    {
        print !empty($this->siteInfo['footer']) ? $this->siteInfo['footer'] : null;
    }

    /**
     * @param bool $showSiteHeader
     */
    public function includeHeader($showSiteHeader = true)
    {
        if (!$this->title) {
            $this->title = $this->siteName;
        }
        print "<!doctype html>\n"
        . '<html><head><title>' . $this->title . '</title>'
        . '<meta charset="utf-8" />'
        . '<meta name="viewport" content="initial-scale=1" />'
        . '<meta http-equiv="X-UA-Compatible" content="IE=edge" />';
        if ($this->useBootstrap) {
            print '<link rel="stylesheet" href="' . $this->url('bootstrap_css') . '" />'
            . '<meta name="viewport" content="width=device-width, initial-scale=1" />'
            . '<!--[if lt IE 9]>'
            . '<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>'
            . '<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>'
            . '<![endif]-->';
        }
        if ($this->useBootstrap || $this->useJquery) {
            print '<script src="' . $this->url('jquery') . '"></script>';
        }
        if ($this->useBootstrap) {
            print '<script src="' . $this->url('bootstrap_js') . '"></script>';
        }
        print '<link rel="stylesheet" type="text/css" href="' . $this->url('css') . '" />'
        . '<link rel="icon" type="image/png" href="' . $this->url('home') . 'favicon.ico" />'
        . '</head><body>';

        // Site headers
        if ($this->isAdmin() || get_class($this) == 'SharedMediaTaggerAdmin' || !$showSiteHeader) {
            return;
        }
        $this->displaySiteHeader();
    }

    /**
     * @param bool $showSiteFooter
     */
    public function includeFooter($showSiteFooter = true)
    {
        $this->includeMenu();
        print '<footer><div class="menu" style="line-height:2; font-size:80%;">';
        if (empty($this->setup['hide_hosted_by']) || !$this->setup['hide_hosted_by']) {
            $serverName = !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null;
            print '<span class="nobr">Hosted by <b><a href="//' . $serverName . '/">' . $serverName . '</a></b></span>';
        }
        print ' &nbsp; &nbsp; &nbsp; &nbsp; ';
        if (!empty($this->setup['hide_powered_by']) && $this->setup['hide_powered_by']) {
            print '<span class="nobr">Powered by <b>'
            . '<a target="commons" href="https://github.com/attogram/shared-media-tagger">'
            . 'Shared Media Tagger v' . __SMT__ . '</a></b></span>';
        }
        if ($this->isAdmin()) {
            print '<br /><br />'
            . '<div style="text-align:left; word-wrap:none; line-height:1.42; font-family:monospace; font-size:10pt;">'
            . '<a href="' . $this->url('home') . '?logoff">LOGOFF</a>'
            . '<br />' . gmdate('Y-m-d H:i:s') . ' UTC'
            . '<br />MEMORY usage: ' . number_format(memory_get_usage())
            . '<br />MEMORY peak : ' . number_format(memory_get_peak_usage())
            . '</div><br /><br /><br />';
        }

        print '</div></footer>';

        // Site footers
        if ($this->isAdmin() || get_class($this) == 'smt_admin' || !$showSiteFooter) {
            print '</body></html>';

            return;
        }

        $this->displaySiteFooter();
        print '</body></html>';
    }
}
