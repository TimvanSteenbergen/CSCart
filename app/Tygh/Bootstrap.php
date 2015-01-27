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

namespace Tygh;

use Tygh\Exceptions\InputException;

class Bootstrap
{
    /**
     * Sends headers
     * @param bool $is_https indicates current working mode - https or not
     */
    public static function sendHeaders($is_https = false)
    {
        // Prevent caching
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate("D, d M Y H:i:s") . ' GMT');

        // Click-jacking protection
        //header("X-Frame-Options: sameorigin");

        if ($is_https) {
            header('Cache-Control: private');
        } else {
            header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
            header('Expires: -1');
        }

        header("Content-Type: text/html; charset=" . CHARSET);
    }

    /**
     * sets PHP config options
     * @param string $dir_root root directory
     */
    public static function setConfigOptions($dir_root)
    {
        ini_set('magic_quotes_sybase', 0);
        ini_set('pcre.backtrack_limit', '1000000'); // this value php versions < 5.3.7 10 times less, so set it as in newer versions.
        ini_set('arg_separator.output', '&');
        ini_set('include_path', $dir_root . '/app/lib/pear/' . ini_get('include_path'));

        $session_id = session_id();
        if (empty($session_id)) {
            ini_set('session.use_trans_sid', 0);
        }

        if (!defined('DEVELOPMENT')) {
            ignore_user_abort(true);
        }
    }

    /**
     * Detects HTTPS mode
     * @param array $server SERVER superglobal array
     */
    public static function detectHTTPS($server)
    {
        if (isset($server['HTTPS']) && ($server['HTTPS'] == 'on' || $server['HTTPS'] == '1')) {
            define('HTTPS', true);
        } elseif (isset($server['HTTP_X_FORWARDED_SERVER']) && ($server['HTTP_X_FORWARDED_SERVER'] == 'secure' || $server['HTTP_X_FORWARDED_SERVER'] == 'ssl')) {
            define('HTTPS', true);
        } elseif (isset($server['SCRIPT_URI']) && (strpos($server['SCRIPT_URI'], 'https') === 0)) {
            define('HTTPS', true);
        } elseif (isset($server['HTTP_HOST']) && (strpos($server['HTTP_HOST'], ':443') !== false)) {
            define('HTTPS', true);
        } elseif (isset($server['HTTP_X_FORWARDED_PROTO']) && $server['HTTP_X_FORWARDED_PROTO'] == 'https') {
            define('HTTPS', true);
        }
    }

    /**
     * Fixes vars in SERVER superglobal array
     * @param  array $server SERVER array
     * @return array fixed SERVER array
     */
    public static function fixServerVars($server)
    {
        if (!isset($server['HTTP_HOST'])) {
            $server['HTTP_HOST'] = 'localhost';
        }

        if (isset($server['HTTP_X_REWRITE_URL'])) { // for isapi_rewrite
            $server['REQUEST_URI'] = $server['HTTP_X_REWRITE_URL'];
        }

        if (!empty($server['QUERY_STRING'])) {
            $server['QUERY_STRING'] = (defined('QUOTES_ENABLED')) ? stripslashes($server['QUERY_STRING']) : $server['QUERY_STRING'];
            $server['QUERY_STRING'] = str_replace(array('"', "'"), array('', ''), $server['QUERY_STRING']);
        }

        // resolve symbolic links
        if (!empty($server['SCRIPT_FILENAME'])) {
            $server['SCRIPT_FILENAME'] = realpath($server['SCRIPT_FILENAME']);
        }

        //PHP_AUTH_USER and PHP_AUTH_PW not available when using FastCGI (https://bugs.php.net/bug.php?id=35752)
        $http_auth = '';
        if (!empty($server['REDIRECT_HTTP_AUTHORIZATION'])) {
            $http_auth = base64_decode(substr($server['REDIRECT_HTTP_AUTHORIZATION'], 6));
        } elseif (!empty($server['HTTP_AUTHORIZATION'])) {
            $http_auth = base64_decode(substr($server['HTTP_AUTHORIZATION'], 6));
        }

        if (!empty($http_auth) && (empty($server['PHP_AUTH_USER']) || empty($server['PHP_AUTH_PW']))) {
            list($server['PHP_AUTH_USER'], $server['PHP_AUTH_PW']) = explode(':', $http_auth);
        }

        if (self::isWindows()) {
            foreach (array('PHP_SELF', 'SCRIPT_FILENAME', 'SCRIPT_NAME') as $var) {
                if (isset($server[$var])) {
                    $server[$var] = str_replace('\\', '/', $server[$var]);
                }
            }
        }

        return $server;
    }

    /**
     * Inits console mode
     * @param  array  $get      GET superglobal array
     * @param  array  $server   SERVER superglobal array
     * @param  string $dir_root root directory
     * @return array  list of filtered get and server arrays
     */
    public static function initConsoleMode($get, $server, $dir_root)
    {
        if (empty($server['REQUEST_METHOD'])) { // if we do not have $_SERVER['REQUEST_METHOD'], assume that we're in console mode
            define('CONSOLE', true);

            if (($get = self::parseCmdArgs($get, $server)) === false) {
                throw new InputException('Invalid parameters list');
            }

            $server['SERVER_SOFTWARE'] = 'Tygh';
            $server['REMOTE_ADDR'] = '127.0.0.1';
            $server['REQUEST_METHOD'] = 'GET';
            $server['HTTP_USER_AGENT'] = 'Console';

            chdir($dir_root);
            @set_time_limit(0); // the script, running in console mode has no time limits
        }

        return array($get, $server);
    }

    /**
     * Inits environment
     * @param  array  $get      GET superglobal array
     * @param  array  $post     POST subperglobal array
     * @param  array  $server   SERVER superglobal array
     * @param  string $dir_root root directory
     * @return array  combined and filtered GET/POST array
     */
    public static function initEnv($get, $post, $server, $dir_root)
    {
        date_default_timezone_set('UTC'); // setting temporary timezone to avoid php warnings

        $server = self::fixServerVars($server);

        self::detectHTTPS($server);
        self::setConstants($server, $dir_root);
        self::setConfigOptions($dir_root);
        
        list($get, $server) = self::initConsoleMode($get, $server, $dir_root);

        if (!defined('CONSOLE')) {
            self::sendHeaders(defined('HTTPS'));
        }

        return array(self::processRequest($get, $post), $server);
    }

    /**
     * Sets environment constants
     * @param array  $server   SERVER superglobal array
     * @param string $dir_root root directory
     */
    public static function setConstants($server, $dir_root)
    {
        define('TIME', time());
        define('MICROTIME', microtime(true));
        define('MIN_PHP_VERSION', '5.3.0');
        define('CHARSET', 'utf-8');
        define('BOOTSTRAP', true);

        if (get_magic_quotes_gpc()) {
            define('QUOTES_ENABLED', true);
        }

        if (self::isWindows()) {
            define('IS_WINDOWS', true);
            $dir_root = str_replace('\\', '/', $dir_root);
        }

        if (isset($server['HTTP_X_FORWARDED_HOST'])) {
            define('REAL_HOST', $server['HTTP_X_FORWARDED_HOST']);
        } else {
            define('REAL_HOST', $server['HTTP_HOST']);
        }

        define('REAL_URL', (defined('HTTPS') ? 'https://' : 'http://') . REAL_HOST . (!empty($server['REQUEST_URI']) ? $server['REQUEST_URI'] : ''));

        define('DIR_ROOT', $dir_root);

        if (!self::getIniParam('zlib.output_compression') && !empty($server['HTTP_ACCEPT_ENCODING']) && strpos($server['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
            ob_start('ob_gzhandler');
        }

        if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<')) {
            die('PHP version <b>' . MIN_PHP_VERSION . '</b> or greater is required. Your PHP is version <b>' . PHP_VERSION . '</b>, please ask your host to upgrade it.');
        }
    }

    /**
     * Processes request vars and combine them
     * @param  array $get  GET vars
     * @param  array $post POST vars
     * @return array combined filtered array with post and get vars
     */
    public static function processRequest($get, $post)
    {
        if (self::getIniParam('register_globals')) {
            self::unregisterGlobals();
        }

        return self::safeInput(array_merge($post, $get));
    }

    /**
     * Sanitizes input data
     *
     * @param  mixed $data data to filter
     * @return mixed filtered data
     */
    public static function safeInput($data)
    {
        if (defined('QUOTES_ENABLED')) {
            $data = self::stripSlashes($data);
        }

        return self::stripTags($data);
    }

    /**
     * Strips html tags from the data
     *
     * @param  mixed $var variable to strip tags from
     * @return mixed filtered variable
     */
    public static function stripTags(&$var)
    {
        if (!is_array($var)) {
            return (strip_tags($var));
        } else {
            $stripped = array();
            foreach ($var as $k => $v) {
                $sk = strip_tags($k);
                if (!is_array($v)) {
                    $sv = strip_tags($v);
                } else {
                    $sv = self::stripTags($v);
                }
                $stripped[$sk] = $sv;
            }

            return ($stripped);
        }
    }

    /**
     * Strips slashes
     *
     * @param  mixed $var variable to strip slashes from
     * @return mixed filtered variable
     */
    public static function stripSlashes($var)
    {
        if (is_array($var)) {
            $var = array_map(array('\\Tygh\\Bootstrap', 'stripSlashes'), $var);

            return $var;
        }

        return (strpos($var, '\\\'') !== false || strpos($var, '\\\\') !== false || strpos($var, '\\"') !== false) ? stripslashes($var) : $var;
    }

    /**
     * Retrieves parameter from php options
     *
     * @param  string  $param     parameter to get value for
     * @param  boolean $get_value if true, get value, otherwise return true if parameter enabled, false if disabled
     * @return mixed   parameter value
     */
    public static function getIniParam($param, $get_value = false)
    {
        $value = ini_get($param);

        if ($get_value == false) {
            $value = (intval($value) || !strcasecmp($value, 'on')) ? true : false;
        }

        return $value;
    }

    /**
     * Deletes request variables from the global scope
     *
     * @param  string  $key if passed, deletes data of this passed superglobal variable
     * @return boolean always true
     */
    public static function unregisterGlobals($key = NULL)
    {
        static $_vars = array('_GET', '_POST', '_FILES', '_ENV', '_COOKIE', '_SERVER');

        $vars = ($key) ? array($key) : $_vars;
        foreach ($vars as $var) {
            if (isset($GLOBALS[$var])) {
                foreach ($GLOBALS[$var] as $k => $v) {
                    unset($GLOBALS[$k]);
                }
            }
            if (isset($GLOBALS['HTTP' . $var . '_VARS'])) {
                unset($GLOBALS['HTTP' . $var . '_VARS']);
            }
        }

        return true;
    }

    /**
     * Parses command-line parameters and put them to _GET array
     *
     * @return boolean true if parameters parsed correctly, false - otherwise
     */
    private static function parseCmdArgs($get, $server)
    {
        while ($code = next($server['argv'])) {
            if (preg_match('/^-{2}([a-zA-Z0-9_]*)=?(.*)$/', $code, $matches)) {
                $get[$matches[1]] = $matches[2];

            } elseif (preg_match('/^-{1}([a-zA-Z0-9]*)$/', $code, $matches)) {
                if (!$value = next($server['argv'])) {
                    return false;
                }
                $get[$matches[1]] = $value;
            }
        }

        return $get;
    }

    /**
     * Checks if PHP OS is Windows
     * @return boolean true if it is Windows, false - otherwise
     */
    private static function isWindows()
    {
        return strtoupper(substr(PHP_OS, 0, 3)) == 'WIN';
    }
}
