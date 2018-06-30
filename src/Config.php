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
     */
    public static function setup(Router $router)
    {
        self::$installDirectory = realpath(__DIR__ . '/..');
        self::$server = $_SERVER['SERVER_NAME'];
        self::$sizeMedium = 325;
        self::$sizeThumb = 100;
        self::$siteUrl = $router->getUriBase() . '/';
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
            'info'          => self::$siteUrl . 'i',
            'browse'        => self::$siteUrl . 'b',
            'categories'    => self::$siteUrl . 'cc',
            'category'      => self::$siteUrl . 'c',
            'reviews'       => self::$siteUrl . 'reviews',
            'login'         => self::$siteUrl . 'login',
            'logout'        => self::$siteUrl . 'logout',
            'admin'         => self::$siteUrl . 'admin/',
            'tag'           => self::$siteUrl . 'tag',
            'sitemap'       => self::$siteUrl . 'sitemap.xml',
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
                    'Demo',
                    'Welcome to the Shared Media Tagger!
                    Find out more at: <a href=\""
                    . Tools::url('github_smt') . "\">"
                    . Tools::url('github_smt') . "</a>'
                )",
            'default_tag1' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (1, 1, '😊 Best', '😊')",
            'default_tag2' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (2, 2, '🙂 Good', '🙂')",
            'default_tag3' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (3, 3, '😐 OK', '😐')",
            'default_tag4' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (4, 4, '🙁 Unsure', '🙁')",
            'default_tag5' =>
                "INSERT INTO tag (id, position, name, display_name) VALUES (5, 5, '☹️ Bad', '☹️')",

            'category1' =>
                "INSERT INTO category (
                    id, name, curated, pageid, files, subcats, local_files, curated_files, 
                    missing, hidden, force, updated
                ) VALUES (
                    1, 'Category:Test patterns', 0, 202140, 99, 3, 1, 0, 
                    0, 0, null, '1970-01-01 01:23:45'
                )",

            'media1' =>
                "INSERT INTO media (
                    pageid, curated, title, url, descriptionurl, descriptionshorturl, 
                    imagedescription, artist, datetimeoriginal, 
                    licenseuri, licensename, licenseshortname, usageterms, attributionrequired, restrictions, 
                    size, width, height, sha1, mime, thumburl, thumbwidth, thumbheight, thumbmime, 
                    user, userid, duration, timestamp, updated
                ) VALUES (
                    11108315, 
                    0, 
                    'File:Test card.png', 
                    'https://upload.wikimedia.org/wikipedia/commons/b/bf/Test_card.png', 
                    'https://commons.wikimedia.org/wiki/File:Test_card.png', 
                    'https://commons.wikimedia.org/w/Home.php?curid=11108315', 
                    '<p>Test card</p>', 
                    '<span lang=\"en\">Unknown</span>',
                    'Unknown date',
                    'https://creativecommons.org/publicdomain/mark/1.0/',
                    'Public Domain',
                    'Public domain',
                    'Public domain',
                    'false',
                    '',
                    26271,
                    640,
                    360,
                    '2e95a28d7449fea6a0b6b8610a43f89859153eee',
                    'image/png',
                    'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bf/Test_card.png/325px-Test_card.png',
                    325,
                    183,
                    'image/png',
                    'Galzigler',
                    1242770,
                    null,
                    '2010-08-06T21:59:56Z',
                    '1970-01-01 01:23:45'
                    )",

        'category2media1' =>
            "INSERT INTO category2media (
                id, category_id, media_pageid, updated
            ) VALUES (
                1, 1, 11108315, '1970-01-01 01:23:45'
            )",
        ];
    }
}
