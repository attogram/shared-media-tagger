<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

use Attogram\Router\Router;

/**
 * Configuration for Shared Media Tagger
 */
class Config
{
    public static $installDirectory;
    public static $links;
    public static $protocol;
    public static $mimeTypesAudio;
    public static $mimeTypesImage;
    public static $mimeTypesVideo;
    public static $server;
    public static $setup = [];
    public static $siteInfo;
    public static $siteName;
    public static $siteUrl;
    public static $sizeMedium;
    public static $sizeThumb;

    /**
     * @param Router $router
     * @param array $setup
     */
    public static function setup(Router $router, array $setup = [])
    {
        self::$installDirectory = realpath(__DIR__ . '/..');
        self::$server = $_SERVER['SERVER_NAME'];
        self::$sizeMedium = 325;
        self::$sizeThumb = 100;

        self::$setup = [];
        if ($setup) { // if optional setup
            self::$setup = $setup;
        }
        if (isset(self::$setup['site_url'])) {
            self::$siteUrl = $setup['site_url'];
        } else {
            self::$siteUrl = $router->getUriBase() . '/';
        }

        self::setLinks();
        self::setProtocol();
        self::setMimeTypes();
    }

    /**
     * setLinks
     */
    public static function setLinks()
    {
        self::$links = [
            'home'          => self::$siteUrl . '',
            'css'           => self::$siteUrl . 'css.css',
            'info'          => self::$siteUrl . 'info.php',
            'browse'        => self::$siteUrl . 'browse.php',
            'categories'    => self::$siteUrl . 'categories.php',
            'category'      => self::$siteUrl . 'category.php',
            'about'         => self::$siteUrl . 'about.php',
            'reviews'       => self::$siteUrl . 'reviews.php',
            'admin'         => self::$siteUrl . 'admin/',
            'contact'       => self::$siteUrl . 'contact.php',
            'tag'           => self::$siteUrl . 'tag.php',
            'users'         => self::$siteUrl . 'users.php',
            'jquery'        => self::$siteUrl . 'use/jquery.min.js',
            'bootstrap_js'  => self::$siteUrl . 'use/bootstrap/js/bootstrap.min.js',
            'bootstrap_css' => self::$siteUrl . 'use/bootstrap/css/bootstrap.min.css',
            'github_smt'    => 'https://github.com/attogram/shared-media-tagger',
        ];
    }

    /**
     * setMimeTypes
     */
    private static function setMimeTypes()
    {
        self::$mimeTypesAudio = [
            'audio/mpeg',
            'audio/x-flac',
            'audio/midi',
            'audio/wav',
            'audio/webm',
        ];
        self::$mimeTypesImage = [
            'image/jpeg',
            'image/png',
            'image/svg+xml',
            'image/tiff',
            'image/gif',
            'image/vnd.djvu',
            'image/x-xcf',
            'image/webp',
            'application/pdf',
        ];
        self::$mimeTypesVideo = [
            'application/ogg',
            'video/webm',
        ];
    }

    /**
     * @param array $siteInfo
     * @return void
     */
    public static function setSiteInfo(array $siteInfo = [])
    {
        if (!$siteInfo || !isset($siteInfo[0]['id'])) {
            self::$siteName = 'Shared Media Tagger';
            self::$siteInfo = [];
            self::$siteInfo['curation'] = 0;

            return;
        }
        self::$siteName = !empty($siteInfo[0]['name']) ? $siteInfo[0]['name'] : null;
        self::$siteInfo = $siteInfo[0];
        if (!isset(self::$siteInfo['curation'])) {
            self::$siteInfo['curation'] = 0;
        }
    }

    /**
     * setProtocol
     */
    private static function setProtocol()
    {
        self::$protocol = 'http:';
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            self::$protocol = 'https:';
        }
    }

    /**
     * @return array
     */
    public static function getDatabaseTables()
    {
        return [
            'site' =>
                "CREATE TABLE IF NOT EXISTS 'site' (
                'id' INTEGER PRIMARY KEY,
                'name' TEXT,
                'about' TEXT,
                'header' TEXT,
                'footer' TEXT,
                'use_cdn' BOOLEAN NOT NULL DEFAULT '0',
                'curation' BOOLEAN NOT NULL DEFAULT '0',
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT su UNIQUE (name) )",
            'tag' =>
                "CREATE TABLE IF NOT EXISTS 'tag' (
                'id' INTEGER PRIMARY KEY,
                'position' INTEGER,
                'name' TEXT,
                'display_name' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP )",
            'tagging' =>
                "CREATE TABLE IF NOT EXISTS 'tagging' (
                'id' INTEGER PRIMARY KEY,
                'tag_id' INTEGER,
                'media_pageid' INTEGER,
                'count' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT tmu UNIQUE (tag_id, media_pageid) )",
            'category' =>
                "CREATE TABLE IF NOT EXISTS 'category' (
                'id' INTEGER PRIMARY KEY,
                'name' TEXT,
                'curated' BOOLEAN NOT NULL DEFAULT '0',
                'pageid' INTEGER,
                'files' INTEGER,
                'subcats' INTEGER,
                'local_files' INTEGER DEFAULT '0',
                'curated_files' INTEGER DEFAULT '0',
                'missing' INTEGER DEFAULT '0',
                'hidden' INTEGER DEFAULT '0',
                'force' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT cu UNIQUE (name) )",
            'category2media' =>
                "CREATE TABLE IF NOT EXISTS 'category2media' (
                'id' INTEGER PRIMARY KEY,
                'category_id' INTEGER,
                'media_pageid' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT tmu UNIQUE (category_id, media_pageid) )",
            'media' =>
                "CREATE TABLE IF NOT EXISTS 'media' (
                'pageid' INTEGER PRIMARY KEY,
                'curated' BOOLEAN NOT NULL DEFAULT '0',
                'title' TEXT,
                'url' TEXT,
                'descriptionurl' TEXT,
                'descriptionshorturl' TEXT,
                'imagedescription' TEXT,
                'artist' TEXT,
                'datetimeoriginal' TEXT,
                'licenseuri' TEXT,
                'licensename' TEXT,
                'licenseshortname' TEXT,
                'usageterms' TEXT,
                'attributionrequired' TEXT,
                'restrictions' TEXT,
                'size' INTEGER,
                'width' INTEGER,
                'height' INTEGER,
                'sha1' TEXT,
                'mime' TEXT,
                'thumburl' TEXT,
                'thumbwidth' INTEGER,
                'thumbheight' INTEGER,
                'thumbmime' TEXT,
                'user' TEXT,
                'userid' INTEGER,
                'duration' REAL,
                'timestamp' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP )",
            'contact' =>
                "CREATE TABLE IF NOT EXISTS 'contact' (
                'id' INTEGER PRIMARY KEY,
                'comment' TEXT,
                'datetime' TEXT,
                'ip' TEXT )",
            'block' =>
                "CREATE TABLE IF NOT EXISTS 'block' (
                'pageid' INTEGER PRIMARY KEY,
                'title' TEXT,
                'thumb' TEXT,
                'ns' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP )",
            'user' =>
                "CREATE TABLE IF NOT EXISTS 'user' (
                'id' INTEGER PRIMARY KEY,
                'ip' TEXT,
                'host' TEXT,
                'user_agent' TEXT,
                'page_views' INTEGER,
                'last' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT uc UNIQUE (ip, host, user_agent) )",
            'user_tagging' =>
                "CREATE TABLE IF NOT EXISTS 'user_tagging' (
                'id' INTEGER PRIMARY KEY,
                'user_id' INTEGER,
                'tag_id' INTEGER,
                'media_pageid' INTEGER,
                'count' INTEGER,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT utu UNIQUE (user_id, tag_id, media_pageid) )",
            'network' =>
                "CREATE TABLE IF NOT EXISTS 'network' (
                'id' INTEGER PRIMARY KEY,
                'site_id' INTEGER NOT NULL,
                'ns' INTEGER NOT NULL,
                'pageid' INTEGER,
                'name' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT nu UNIQUE (ns, pageid) )",
            'network_site' =>
                "CREATE TABLE IF NOT EXISTS 'network_site' (
                'id' INTEGER PRIMARY KEY,
                'url' TEXT,
                'name' TEXT,
                'updated' TEXT DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT nsu UNIQUE (url) )",
        ];
    }

    /**
     * @return array
     */
    public static function getSeedDemoSetup()
    {
        return [
            'default_site' =>
                "INSERT INTO site (
                    id, name, about
                ) VALUES (
                    1,
                    'Shared Media Tagger Demo',
                    'This is a demonstration of the Shared Media Tagger software.'
                )",
            'default_tag1' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (1, 1, '☹️ Worst',  '☹️')",
            'default_tag2' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (2, 2, '🙁 Bad',    '🙁')",
            'default_tag3' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (3, 3, '😐 Unsure', '😐')",
            'default_tag4' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (4, 4, '🙂 Good',   '🙂')",
            'default_tag5' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (5, 5, '😊 Best',   '😊')",
        ];
    }
}
