<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

namespace Tygh\Tools;

class Url
{
    const PUNYCODE_PREFIX = 'xn--';
    /**
     * Normalize URL to pass it to parse_url function
     * @param  string $url URL
     * @return string normalized URL
     */
    private static function fix($url)
    {
        $url = trim($url);
        $url = preg_replace('/^(http[s]?:\/\/|\/\/)/', '', $url);

        if (!empty($url)) {
            $url = 'http://' . $url;
        }

        return $url;
    }

    /**
     * Cleans up URL, leaving domain and path only
     * @param  string $url URL
     * @return string cleaned up URL
     */
    public static function clean($url)
    {
        $url = self::fix($url);
        if ($url) {
            $domain = self::normalizeDomain($url);
            $path = parse_url($url, PHP_URL_PATH);

            return $domain . rtrim($path, '/');
        }

        return '';
    }

    /**
     * Normalizes domain name and punycode's it
     * @param  string $url URL
     * @return mixed  string with normalized domain on success, boolean false otherwise
     */
    public static function normalizeDomain($url)
    {
        $url = self::fix($url);
        if ($url) {
            $domain = parse_url($url, PHP_URL_HOST);
            $port = parse_url($url, PHP_URL_PORT);
            if (!empty($port)) {
                $domain .= ':' . $port;
            }
            if (strpos($domain, self::PUNYCODE_PREFIX) !== 0) {
                $idn = new \Net_IDNA2();
                $domain = $idn->encode($domain);
            }

            return $domain;
        }

        return false;
    }

    /**
     * Decodes punycoded'd URL
     * @param  string $url URL
     * @return mixed  string with decoded URL on success, boolean false otherwise
     */
    public static function decode($url)
    {
        $url = self::fix($url);
        if ($url) {
            $components = parse_url($url);
            $host = $components['host'] . (empty($components['port']) ? '' : ':' . $components['port']);

            if (strpos($host, self::PUNYCODE_PREFIX) !== false) {
                $idn = new \Net_IDNA2();
                $host = $idn->decode($host);
            }

            $path = !empty($components['path']) ? $components['path'] : '';

            return $host . rtrim($path, '/');
        }

        return false;
    }

    /**
     * Resolves relative url
     *
     * @param string $url  relative url
     * @param string $base url base
     *
     * @return string $url resolved url
     */
    public static function resolve($url, $base)
    {
        if ($url[0] == '/') {
            $_pbase = parse_url(self::fix($base));
            $url = $_pbase['protocol'] . '://' . $_pbase['host'] . $url;
        } else {
            $url = $base . '/' . $url;
        }

        return $url;
    }
}
