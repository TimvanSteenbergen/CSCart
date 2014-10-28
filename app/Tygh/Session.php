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

use Tygh\Bootstrap;
use Tygh\Storage;

class Session
{
    private static $_session;
    private static $_name;

    protected static $ttl_online = SESSION_ONLINE;
    protected static $ttl_storage = SESSIONS_STORAGE_ALIVE_TIME;
    protected static $ttl = SESSION_ALIVE_TIME;

    /**
     * Generate session ID for different area
     *
     * @param string $sess_id session ID from cookie
     * @param string $area    session area
     *
     * @return string modified session ID
     */
    private static function _sid($sess_id, $area = AREA)
    {
        fn_set_hook('sid', $sess_id);

        return $sess_id . '_' . $area;
    }

    /**
     * Generates session id
     *
     * @return string new session ID
     */
    private static function _generateId()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(16, $cstrong);
            $session_hash   = bin2hex($bytes);

        } else {
            $generated = array();
            for ($i = 0; $i < 100; $i++) {
                $generated[] = mt_rand();
            }
            shuffle($generated);

            $session_hash = md5(serialize($generated));
        }

        return $session_hash;
    }

    /**
     * Start session (default action)
     *
     * @param string $save_path path for session storage
     * @param array  $sess_name session name
     */
    public static function open($save_path, $sess_name)
    {
    }

    /**
     * Close session (default action)
     *
     * @return boolean always true
     */
    public static function close()
    {
        return true;
    }

    /**
     * Read session from session storage (default action)
     *
     * @param string $sess_id session ID
     *
     * @return array session data
     */
    public static function read($sess_id)
    {
        return self::$_session->read($sess_id);
    }

    /**
     * Write session to session storage (default action)
     *
     * @param string $sess_id session ID
     * @param array  $data    session data
     *
     * @return boolean true if saved, false otherwise
     */
    public static function write($sess_id, $data)
    {
        return self::save($sess_id, $data);
    }

    /**
     * Save session to storage
     *
     * @param string $sess_id session ID
     * @param array  $data    session data
     * @param string $area    session area
     *
     * @return boolean true if saved, false otherwise
     */
    public static function save($sess_id, $data, $area = AREA)
    {
        if (empty(self::$_session)) {
            return false;
        }

        // if used not by standard session handler, can accept data in array, not in serialized array
        if (is_array($data)) {
            $data = self::encode($data);
        }

        $_data = array(
            'expiry' => TIME + self::$ttl,
            'data' => $data
        );

        return self::$_session->write($sess_id, $_data);
    }

    /**
     * Session serializer
     *
     * @param array $data session data
     *
     * @return string serialized data
     */
    public static function encode($data)
    {

        $raw = '' ;
        $line = 0 ;
        $keys = array_keys($data) ;

        foreach ($keys as $key) {
            $value = $data[$key] ;
            $line++;

            $raw .= $key . '|' . serialize($value);

        }

        return $raw ;

    }

    /**
     * Session unserializer
     *
     * @param string $string serialized session data
     *
     * @return array unserialized session data
     */
    public static function decode($string)
    {
        $data = array ();

        if (!empty($string)) {
            $vars = preg_split('/([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff^|]*)\|/', $string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            for ($i = 0; !empty($vars[$i]); $i++) {
                $data[$vars[$i++]] = unserialize($vars[$i]);
            }
        }

        return $data;
    }

    /**
     * Destroy session (default action)
     *
     * @param string $sess_id session ID
     *
     * @return boolean true if destroyed, false otherwise
     */
    public static function destroy($sess_id)
    {
        return self::$_session->delete($sess_id);
    }

    /**
     * Garbage collector (default action)
     *
     * @param int $max_lifetime session life time
     *
     * @return boolean always true
     */
    public static function gc($max_lifetime)
    {
        self::$_session->gc($max_lifetime);

        // Delete custom files (garbage) from unlogged customers
        $files = Storage::instance('custom_files')->getList('sess_data');

        if (!empty($files)) {
            foreach ($files as $file) {
                $fdate = fileatime(Storage::instance('custom_files')->getAbsolutePath('sess_data/' . $file));

                if ($fdate < (TIME - SESSIONS_STORAGE_ALIVE_TIME)) {
                    Storage::instance('custom_files')->delete('sess_data/' . $file);
                }
            }
        }

        return true;
    }

    /**
     * Get session variable name (default action)
     *
     * @return string session name
     */
    public static function getName()
    {
        return session_name();
    }

    /**
     * Get session ID (default action)
     *
     * @return string session ID
     */
    public static function getId()
    {
        return session_id();
    }

    /**
     * Set session ID
     *
     * @param string $sess_id      session ID
     * @param bool   $need_postfix Determines whether it is necessary to add company_id and area code to the end of the session_id value
     *
     * @return string new session ID
     */
    public static function setId($sess_id, $need_postfix = true)
    {
        return ($need_postfix) ? session_id(self::_sid($sess_id)) : session_id($sess_id);
    }

    /**
     * Regenerates session ID
     *
     * @return string new session ID
     */
    public static function regenerateId()
    {
        $old_id = self::getId();
        $new_id = self::_sid(self::_generateId());

        session_write_close();

        self::$_session->regenerate($old_id, $new_id);

        self::setId($new_id, false);
        $_COOKIE[self::$_name] = $new_id; // put new session to COOKIE to pass validation if start method
        self::start();

        // Update user_session_products
        db_query('UPDATE ?:user_session_products SET session_id = ?s WHERE session_id = ?s', $new_id, $old_id);

        return $new_id;
    }

    /**
     * Re-create session, returns new session ID
     *
     * @param string $sess_id session ID to start with
     *
     * @return string new session ID
     */
    public static function resetId($sess_id = null)
    {
        if ($sess_id == self::getId()) {
            return $sess_id;
        }

        session_destroy();
        // session_destroy kills our handlers,
        // http://bugs.php.net/bug.php?id=32330
        // so we set them again
        self::setHandlers();
        if (!empty($sess_id)) {
            self::setId($sess_id, false);
        }

        self::start();

        return self::getId();
    }

    /**
     * Set session handlers
     */
    public static function setHandlers()
    {
        session_set_save_handler(
            array('\\Tygh\\Session', 'open'),
            array('\\Tygh\\Session', 'close'),
            array('\\Tygh\\Session', 'read'),
            array('\\Tygh\\Session', 'write'),
            array('\\Tygh\\Session', 'destroy'),
            array('\\Tygh\\Session', 'gc')
        );
    }

    /**
     * Starts session
     * @param array $request Request data
     */
    public static function start($request = array())
    {
        // Force transfer session id to cookies if it passed via url
        if (!empty($request[self::$_name])) {
            self::setId($request[self::$_name], false);
        } elseif (empty($_COOKIE[self::$_name])) {
            self::setId(self::_generateId());
        }

        session_name(self::$_name);
        session_start();

        // Session checker (for external services, returns "OK" if session exists, empty - otherwise)
        if (!empty($request['check_session'])) {
            die(!empty($_SESSION) ? 'OK' : '');
        }

        // Validate session
        if (!defined('SKIP_SESSION_VALIDATION')) {
            $validator_data = self::getValidatorData();
            if (!isset($_SESSION['_validator_data'])) {
                $_SESSION['_validator_data'] = $validator_data;
            } else {
                if ($_SESSION['_validator_data'] != $validator_data) {
                    session_regenerate_id();
                    $_SESSION = array();
                }
            }
        }

        // _SESSION superglobal variable populates here, so remove it from global scope if needed
        if (Bootstrap::getIniParam('register_globals')) {
            Bootstrap::unregisterGlobals('_SESSION');
        }

    }

    /**
     * Set session params
     */
    public static function setParams()
    {
        $host = defined('HTTPS') ? Registry::get('config.https_host') : Registry::get('config.http_host');

        if (strpos($host, '.') !== false) {
            // Check if host has www, www2, www4 prefix and remove it
            $host = preg_replace('/^www[0-9]*\./i', '', $host);
            $host = strpos($host, '.') === 0 ? $host : '.' . $host;
        } else {
            // For local hosts set this to empty value
            $host = '';
        }

        ini_set('session.cookie_lifetime', SESSIONS_STORAGE_ALIVE_TIME);
        $cookie_domain = '';
        if (!preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $host, $matches)) {
            $cookie_domain = $host;
            ini_set('session.cookie_domain', $cookie_domain);
        }
        $current_path = Registry::get('config.current_path');
        $cookie_path = !empty($current_path) ? $current_path : '/';
        ini_set('session.cookie_path', $cookie_path);
        ini_set('session.gc_probability', 1);
        ini_set('session.gc_divisor', 10); // probability is 10% that garbage collector starts

        ini_set('session.hash_function', '0'); // use md5 128bits
        ini_set('session.hash_bits_per_character', 4); // 4 bits for character, so we'll have 128/4 = 32 bytes hash length

        // Secure session cookie with HTTPONLY parameter
        session_set_cookie_params(SESSIONS_STORAGE_ALIVE_TIME, $cookie_path, $cookie_domain, false, true);
    }

    /**
     * Get session validation data
     *
     * @return array validation data
     */
    public static function getValidatorData()
    {
        $data = array();

        if (defined('SESS_VALIDATE_IP')) {
            $ip = fn_get_ip();
            $data['ip'] = $ip['host'];
        }

        // FIXME: Chromeframe could not work with Ajax and cookies. Session will be re-inited every time.
        // Waiting for the CHROME fix.
        if (defined('SESS_VALIDATE_UA') && !preg_match('/chromeframe/i', $_SERVER['HTTP_USER_AGENT'])) {
            $data['ua'] = !empty($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        }

        return $data;
    }

    /**
     * Set session name
     *
     * @param $account_type - current account type
     * @return boolean always true
     */
    public static function setName($account_type = ACCOUNT_TYPE)
    {
        $sess_postfix = Registry::get('config.http_location');

        self::$_name = 'sid_' . $account_type . '_' . substr(md5($sess_postfix), 0, 5);

        return true;
    }

    /**
     * Init session
     *
     * @return boolean true if session was init correctly, false otherwise
     */
    public static function init($request)
    {
        if (!empty($request['no_session'])) {
            fn_define('NO_SESSION', true);
        }

        if (!defined('NO_SESSION')) {
            self::setName();
            self::setParams();
            self::setHandlers();

            if (empty(self::$_session)) {
                $_session_class = Registry::ifGet('config.session_backend', 'database');
                $_session_class = '\\Tygh\\Backend\\Session\\' . ucfirst($_session_class);
                self::$_session = new $_session_class(Registry::get('config'), array(
                    'ttl' => self::$ttl,
                    'ttl_storage' => self::$ttl_storage,
                    'ttl_online' => self::$ttl_online
                ));
            }

            if (!empty(self::$_session)) {
                self::start($request);
                register_shutdown_function(array('\\Tygh\\Session', 'shutdown'));

                return true;
            }
        }

        return false;
    }

    /**
     * Gets online sessions
     * @param  string $area session area
     * @return array  list of session IDs
     */
    public static function getOnline($area = AREA)
    {
        return self::$_session->getOnline($area);
    }

    /**
     * Calls session save handler
     */
    public static function shutdown()
    {
        // we don't need to register shutdown function if it is ajax request,
        // because ajax request session manipulations are done in ob_handler.
        // ajax ob_handlers are lauched AFTER session_close so all session changes by ajax
        // will be unsaved.
        // so we call session_write_close() directly in our ajax ob_handler
        if (!defined('AJAX_REQUEST')) {
            session_write_close();
        }
    }
}
