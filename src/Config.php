<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Configuration for Shared Media Tagger
 */
class Config
{
    public static $adminConfigFile;
    public static $databaseDirectory;
    public static $links = [];
    public static $protocol;
    public static $publicDirectory;
    public static $server;
    public static $setup = [];
    public static $siteInfo;
    public static $siteName;
    public static $siteUrl;
    public static $sizeMedium;
    public static $sizeThumb;
    public static $sourceDirectory;
    public static $mimeTypesAudio = [
        'audio/mpeg',
        'audio/x-flac',
        'audio/midi',
        'audio/wav',
        'audio/webm',
    ];
    public static $mimeTypesImage = [
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
    public static $mimeTypesVideo = [
        'application/ogg',
        'video/webm',
    ];

    public static function setLinks()
    {
        self::$links = [
            'admin'         => self::$siteUrl . 'admin',
            'browse'        => self::$siteUrl . 'b',
            'topics'        => self::$siteUrl . 'topics',
            'topic'         => self::$siteUrl . 'c',
            'home'          => self::$siteUrl . '',
            'info'          => self::$siteUrl . 'i',
            'login'         => self::$siteUrl . 'login',
            'logout'        => self::$siteUrl . 'logout',
            'random'        => self::$siteUrl . 'random',
            'scores'        => self::$siteUrl . 'scores',
            'search'        => self::$siteUrl . 'search',
            'tag'           => self::$siteUrl . 'tag',
            'sitemap'       => self::$siteUrl . 'sitemap.xml',
            'github_smt'    => 'https://github.com/attogram/shared-media-tagger',
        ];
    }

    /**
     * @param array $config
     */
    public static function setup(array $config = [])
    {
        self::$server = $_SERVER['SERVER_NAME'];
        self::$publicDirectory = $config['publicDirectory'];

        if (empty($config['sourceDirectory'])) {
            self::$sourceDirectory = '../src';
        } else {
            self::$sourceDirectory = $config['sourceDirectory'];
        }

        if (empty($config['databaseDirectory'])) {
            self::$databaseDirectory = '../db';
        } else {
            self::$databaseDirectory = $config['databaseDirectory'];
        }

        if (empty($config['adminConfigFile'])) {
            self::$adminConfigFile = './config.admin.php';
        } else {
            self::$adminConfigFile = $config['adminConfigFile'];
        }

        if (empty($config['sizeMedium'])) {
            self::$sizeMedium = 640;
        } else {
            self::$sizeMedium = $config['sizeMedium'];
        }

        if (empty($config['sizeThumb'])) {
            self::$sizeThumb = 130;
        } else {
            self::$sizeThumb = $config['sizeThumb'];
        }

        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
        ) {
            self::$protocol = 'https:';
        } else {
            self::$protocol = 'http:';
        }

        self::setLinks();
    }

    /**
     * @param string $siteUrl
     */
    public static function setSiteUrl(string $siteUrl)
    {
        self::$siteUrl = $siteUrl;
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
     * @return array
     */
    public static function getMimeTypesAudio()
    {
        return [
            'audio/mpeg',
            'audio/x-flac',
            'audio/midi',
            'audio/wav',
            'audio/webm',
        ];
    }

    /**
     * @return array
     */
    public static function getMimeTypesImage()
    {
        return [
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
    }

    /**
     * @return array
     */
    public static function getMimeTypesVideo()
    {
        return [
            'application/ogg',
            'video/webm',
        ];
    }
}
