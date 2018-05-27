<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Class Tools
 */
class Tools
{
    /**
     * @param string $link
     * @return string
     */
    public static function url($link = '')
    {
        if (!$link || !isset(Config::$links[$link])) {
            Tools::error("url: Link Not Found: $link");

            return '';
        }

        return Config::$links[$link];
    }

    /**
     * @return bool
     */
    public static function isAdmin()
    {
        if (isset($_COOKIE['admin']) && $_COOKIE['admin'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * adminLogoff
     */
    public static function adminLogoff()
    {
        if (!self::isAdmin()) {
            return;
        }
        unset($_COOKIE['admin']);
        setcookie('admin', '', -1, '/');
    }

    /**
     * @param string $number
     * @return bool
     */
    public static function isPositiveNumber($number = '')
    {
        if (preg_match('/^[0-9]*$/', (string) $number)) {
            return true;
        }

        return false;
    }

    /**
     * @param $one
     * @param $two
     * @return string
     */
    public static function isSelected($one, $two)
    {
        if ($one == $two) {
            return ' selected="selected"';
        }

        return '';
    }

    /**
     * @param $rawSeconds
     * @return string
     */
    public static function secondsToTime($rawSeconds)
    {
        if (!$rawSeconds) {
            return '0 seconds';
        }
        $hours = floor($rawSeconds / 3600);
        $minutes = floor(($rawSeconds / 60) % 60);
        $seconds = $rawSeconds % 60;
        $seconds += round($rawSeconds - floor($rawSeconds), 2);
        $response = [];
        if ($hours) {
            $response[] = $hours . ' hours';
        }
        if ($minutes) {
            $response[] = $minutes . ' minutes';
        }
        if ($seconds) {
            $response[] = $seconds . ' seconds';
        }
        return implode($response, ', ');
    }

    /**
     * @return false|string
     */
    public static function timeNow()
    {
        return gmdate('Y-m-d H:i:s');
    }

    /**
     * @param $string
     * @return null|string|string[]
     */
    public static function stripPrefix($string)
    {
        if (!$string || !is_string($string)) {
            return $string;
        }

        return preg_replace(['/^File:/', '/^Category:/'], '', $string);
    }

    /**
     * @param string $category
     * @return string
     */
    public static function categoryUrldecode($category)
    {
        return str_replace('_', ' ', urldecode($category));
    }

    /**
     * @param string $category
     * @return string
     */
    public static function categoryUrlencode($category)
    {
        return str_replace('+', '_', str_replace('%3A', ':', urlencode($category)));
    }

    /**
     * @param $string
     * @param int $length
     * @return string
     */
    public static function truncate($string, $length = 50)
    {
        if (strlen($string) <= $length) {
            return $string;
        }

        return substr($string, 0, $length-2) . '..';
    }

    /**
     * @param string $uri
     * @return string
     */
    public static function openContentLicenseName($uri)
    {
        // modified from: https://github.com/gbv/image-attribution - MIT License
        if ($uri == 'http://creativecommons.org/publicdomain/zero/1.0/') {
            return "CC0";
        } elseif ($uri == 'https://creativecommons.org/publicdomain/mark/1.0/') {
            return "Public Domain";
        } elseif (preg_match(
            '/^http:\/\/creativecommons.org\/licenses\/(((by|sa)-?)+)\/([0-9.]+)\/(([a-z]+)\/)?/',
            $uri,
            $match
        )
        ) {
            $license = "CC ".strtoupper($match[1])." ".$match[4];
            if (isset($match[6])) {
                $license .= " ".$match[6];
            }
            return $license;
        } else {
            return '';
        }
    }

    /**
     * @param string $license
     * @return string
     */
    public static function openContentLicenseUri($license)
    {
        // modified from: https://github.com/gbv/image-attribution - MIT License
        $license = strtolower(trim($license));

        if (preg_match('/^(cc0|cc[ -]zero)$/', $license)) {
            return 'http://creativecommons.org/publicdomain/zero/1.0/'; // CC Zero
        } elseif (preg_match('/^(cc )?(pd|pdm|public[ -]domain)( mark( 1\.0)?)?$/', $license)) {
            return 'https://creativecommons.org/publicdomain/mark/1.0/'; // Public Domain
        } elseif ($license == "no restrictions") {
            // No restrictions (for instance images imported from Flickr Commons)
            return 'https://creativecommons.org/publicdomain/mark/1.0/';
        } elseif (preg_match('/^cc([ -]by)?([ -]sa)?([ -]([1-4]\.0|2\.5))([ -]([a-z][a-z]))?$/', $license, $match)) {
            // CC licenses.
            // see <https://wiki.creativecommons.org/wiki/License_Versions>
            // See <https://wiki.creativecommons.org/wiki/Jurisdiction_Database>
            $byline = $match[1] ? 'by' : '';
            $sharealike = $match[2] ? 'sa' : '';
            $port = isset($match[6]) ? $match[6] : '';
            $version = $match[4];

            // just "CC" is not enough
            if (!($byline or $sharealike) or !$version) {
                return '';
            }

            // only 1.0 had pure SA-license without BY
            if ($version == "1.0" && !$byline) {
                $condition = "sa";
            } else {
                $condition = $sharealike ? "by-sa" : "by";
            }

            // ported versions only existed in 2.0, 2.5, and 3.0
            if ($port) {
                if ($version == "1.0" or $version == "4.0") {
                    return '';
                }
                # TODO: check whether port actually exists at given version, for instance 2.5 had less ports!
            }

            // build URI
            $uri = "http://creativecommons.org/licenses/$condition/$version/";
            if ($port) {
                $uri .= "$port/";
            }

            return $uri;
        } else {
            // TODO: GFLD and other licenses
            return '';
        }
    }

    // Logging

    /**
     * @param string $message
     */
    public static function debug($message = '')
    {
        self::logMessage($message, 'debug');
    }

    /**
     * @param string $message
     */
    public static function notice($message = '')
    {
        self::logMessage($message, 'notice');
    }

    /**
     * @param string $message
     */
    public static function error($message = '')
    {
        self::logMessage($message, 'error');
    }

    /**
     * @param string $message
     */
    public static function fail($message = '')
    {
        self::logMessage($message, 'fail');
        exit;
    }

    /**
     * @param $message
     * @param $type
     */
    public static function logMessage($message, $type)
    {
        switch ($type) {
            case 'debug':
                $class = 'debug';
                $head = '';
                break;
            case 'notice':
                $class = 'notice';
                $head = '';
                break;
            case 'error':
                $class = 'error';
                $head = 'ERROR:';
                break;
            case 'fail':
                $class = 'fail';
                $head = 'GURU MEDITATION FAILURE:';
                break;
            default:
                return;
        }
        if (is_array($message)) {
            $message = '<pre>' . htmlentities(print_r($message, true)) . '</pre>';
        }
        print '<div class="message ' . $class . '"><b>' . $head . '</b> ' . $message . '</div>';
    }
}
