<?php

declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger;

/**
 * Class Tools
 */
class Tools
{
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
     * @param $string
     * @param $length
     * @return string
     */
    public static function centerpad($string, $length)
    {
        if (!$length) {
            return $string;
        }
        if (strlen($string) >= $length) {
            return $string;
        }

        return str_pad($string, $length, ' ', STR_PAD_BOTH);
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
     * @param $category
     * @return mixed
     */
    public static function categoryUrldecode($category)
    {
        return str_replace('_', ' ', urldecode($category));
    }

    /**
     * @param $category
     * @return mixed
     */
    public static function categoryUrlencode($category)
    {
        return str_replace('+', '_', str_replace('%3A', ':', urlencode($category)));
    }

    // Admin

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
     *
     */
    public static function adminLogoff()
    {
        if (!self::isAdmin()) {
            return;
        }
        unset($_COOKIE['admin']);
        setcookie('admin', null, -1, '/');
    }
}
