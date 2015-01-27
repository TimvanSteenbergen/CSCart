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

use Tygh\Exceptions\DeveloperException;
use Tygh\Registry;

class Http
{
    const TIMEOUT = 30;
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    public static $logging = true;
    private static $_curl_ssl_support = false;
    private static $_curl_followlocation_support = false;
    private static $_headers = '';
    private static $_error = '';
    private static $_pull = array();

    /**
     * Runs http GET method
     * @param  string $url   request URL
     * @param  mixed  $data  data to post to request
     * @param  array  $extra extra parameters
     * @return mixed  false on failure, string with returned content on success
     */
    public static function get($url, $data = array(), $extra = array())
    {
        return self::_request(self::GET, $url, $data, $extra);
    }

    /**
     * Runs http GET method and returns connection handler to run several threads using processMultiRequest method
     * @param  string $url   request URL
     * @param  mixed  $data  data to post to request
     * @param  array  $extra extra parameters
     * @return mixed  false on failure, string thread ID on success
     */
    public static function mget($url, $data = array(), $extra = array())
    {
         return self::_mrequest(self::GET, $url, $data, $extra);
    }

    /**
     * Runs http POST method
     * @param  string $url   request URL
     * @param  mixed  $data  data to post to request
     * @param  array  $extra extra parameters
     * @return mixed  false of failure, string with returned content on success
     */
    public static function post($url, $data, $extra = array())
    {
        return self::_request(self::POST, $url, $data, $extra);
    }

    /**
     * Runs http POST method and returns connection handler to run several threads using processMultiRequest method
     * @param  string $url   request URL
     * @param  mixed  $data  data to post to request
     * @param  array  $extra extra parameters
     * @return mixed  false on failure, string thread ID on success
     */
    public static function mpost($url, $data, $extra = array())
    {
        return self::_mrequest(self::POST, $url, $data, $extra);
    }

    /**
     * Runs http PUT method
     * @param  string $url   request URL
     * @param  mixed  $data  data to post to request
     * @param  array  $extra extra parameters
     * @return mixed  false of failure, string with returned content on success
     */
    public static function put($url, $data, $extra = array())
    {
        return self::_request(self::PUT, $url, $data, $extra);
    }

    /**
     * Runs http PUT method and returns connection handler to run several threads using processMultiRequest method
     * @param  string $url   request URL
     * @param  mixed  $data  data to post to request
     * @param  array  $extra extra parameters
     * @return mixed  false on failure, string thread ID on success
     */
    public static function mput($url, $data, $extra = array())
    {
        return self::_mrequest(self::PUT, $url, $data, $extra);
    }

    /**
     * Runs http DELETE method
     * @param  string $url   request URL
     * @param  array  $extra extra parameters
     * @return mixed  false of failure, string with returned content on success
     */
    public static function delete($url, $extra = array())
    {
        return self::_request(self::DELETE, $url, array(), $extra);
    }

    /**
     * Runs http DELETE method and returns connection handler to run several threads using processMultiRequest method
     * @param  string $url   request URL
     * @param  array  $extra extra parameters
     * @return mixed  false on failure, string thread ID on success
     */
    public static function mdelete($url, $extra = array())
    {
        return self::_mrequest(self::DELETE, $url, array(), $extra);
    }

    /**
     * Get curl information
     *
     * @param  string $object object name to generate message for is case of error
     * @return string true if no problems with curl, string error message if problems
     */
    public static function getCurlInfo($object = '')
    {
        if (!self::_curlExists()) {
            $msg = __('error_curl_not_exists', array(
                '[method]' => $object
            ));
        } elseif (self::$_curl_ssl_support == false) {
            $msg = __('error_curl_ssl_not_exists', array(
                '[method]' => $object
            ));
        }

        if (!empty($msg)) {
            return "<p>$msg</p><hr />";
        }

        return '';
    }

    /**
     * Get response headers
     * @return string headers
     */
    public static function getHeaders()
    {
        return self::$_headers;
    }

    /**
     * Get response error
     * @return string error message
     */
    public static function getError()
    {
        return self::$_error;
    }

    /**
     * Processes multi request
     * @param  array $threads key->value list to match thread_id to custom ID
     * @return array list of request or callback function responses
     */
    public static function processMultiRequest($threads = array())
    {
        $results = false;
        if (self::_supportsMultiRequests() && !empty(self::$_pull)) {
            $mh = curl_multi_init();

            foreach (self::$_pull as $thread) {
                curl_multi_add_handle($mh, $thread['ch']);
            }

            do {
                $status = curl_multi_exec($mh, $active);
                $info = curl_multi_info_read($mh);
            } while ($status === CURLM_CALL_MULTI_PERFORM || $active);

            $results = array();
            foreach (self::$_pull as $p_id => $thread) {
                $res = curl_multi_getcontent($thread['ch']);
                $contents = self::_parseContent($res);

                if (!empty($thread['params'])) {
                    list($method, $url, $data, $extra) = $thread['params'];
                    $contents = self::_processHeadersRedirect($method, $url, $extra, $contents);
                }
                if (self::$logging) {
                    $logging_data['response'] = $contents;
                    if (isset($url)) {
                        $logging_data['url'] = $url;
                    }
                    if (isset($data)) {
                        $logging_data['data'] = var_export($data, true);
                    }
                    fn_log_event('requests', 'http', $logging_data);
                }

                curl_multi_remove_handle($mh, $thread['ch']);
                curl_close($thread['ch']);
                unset(self::$_pull[$p_id]);

                $a_id = !empty($threads[$p_id]) ? $threads[$p_id] : $p_id;

                if (!empty($thread['callback'])) {
                    $results[$a_id] = self::_runCallback($contents, $thread['callback']);
                } else {
                    $results[$a_id] = $contents;
                }
            }

            curl_multi_close($mh);

        } elseif (!empty(self::$_pull)) {
            foreach (self::$_pull as $p_id => $thread) {
                $a_id = !empty($threads[$p_id]) ? $threads[$p_id] : $p_id;

                list($method, $url, $data, $extra) = $thread['params'];
                $contents = self::_request($method, $url, $data, $extra);

                if (!empty($extra['callback'])) {
                    $results[$a_id] = self::_runCallback($contents, $extra['callback']);
                } else {
                    $results[$a_id] = $contents;
                }
            }
        }

        return $results;
    }

    /**
     * Checks if curl supports multi exec
     * @return boolean true if supports, false otherwise
     */
    private static function _supportsMultiRequests()
    {
        return function_exists('curl_multi_exec');
    }

    /**
     * Sets response error
     * @param string $transport transport type (socket/curl)
     * @param string $error     error message
     * @param int    $errno     error number
     */
    private static function _setError($transport, $error, $errno)
    {
        self::$_error = "$transport ($errno): $error";
    }

    /**
     * Returns the proxy settings
     *
     * @return array settings
     */
    private static function _getSettings()
    {
        return array (
            'proxy_host' => Registry::get('settings.General.proxy_host'),
            'proxy_port' => Registry::get('settings.General.proxy_port'),
            'proxy_user' => Registry::get('settings.General.proxy_user'),
            'proxy_password' => Registry::get('settings.General.proxy_password'),
        );
    }

    /**
     * Parse response contents to split headers
     * @param  string $content response contents
     * @return string contents without headers
     */
    private static function _parseContent($content)
    {
        while (strpos(ltrim($content), 'HTTP/1') === 0) {
            list(self::$_headers, $content) = preg_split("/(\r?\n){2}/", $content, 2);
        }

        return $content;
    }

    /**
     * Checks if curl is supported
     * @return boolean true if supported, false if not
     */
    private static function _curlExists()
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $ver = curl_version();
        if (is_array($ver)) {
            self::$_curl_ssl_support = !empty($ver['ssl_version']);
        } else {
            self::$_curl_ssl_support = (strpos($ver, 'SSL') !== false);
        }

        self::$_curl_followlocation_support = !ini_get('open_basedir') && !ini_get('safe_mode');

        return true;
    }

    /**
     * Parses callback function arguments ans runs it
     * @param  string $content  response contents
     * @param  arary  $callback first arg - function name, second and others passes to function
     * @return mixed  function result
     */
    private static function _runCallback($content, $callback)
    {
        $func = array_shift($callback);
        array_unshift($callback, $content);

        return call_user_func_array($func, $callback);
    }

    /**
     * Executes request, if curl exists - via curl, if not - via socket
     * @param  string $method request method
     * @param  string $url    request url
     * @param  mixed  $data   request data
     * @param  array  $extra  extra settings
     * @return mixed  request response on success, false on failure
     */
    private static function _request($method, $url, $data, $extra = array())
    {
        list($url, $data) = self::_prepareData($method, $url, $data);

        if (self::_curlExists()) {
            $content = self::_curlRequest($method, $url, $data, $extra);
        } else {
            $content = self::_socketRequest($method, $url, $data, $extra);
        }

        if (self::$logging) {
            fn_log_event('requests', 'http', array(
                'url' => $url,
                'data' => var_export($data, true),
                'response' => $content,
            ));
        }

        return $content;
    }

    /**
     * Prepares multi-threaded request
     * @param  string $method request method
     * @param  string $url    request url
     * @param  mixed  $data   request data
     * @param  array  $extra  extra settings
     * @return string request thread ID
     */
    private static function _mrequest($method, $url, $data, $extra = array())
    {
        if (self::_supportsMultiRequests()) {
            list($url, $data) = self::_prepareData($method, $url, $data);

            $extra['return_handler'] = true;
            $ch = self::_curlRequest($method, $url, $data, $extra);
            $thread_id = md5((string) $ch);

            self::$_pull[$thread_id] = array(
                'callback' => !empty($extra['callback']) ? $extra['callback'] : '',
                'params' => array($method, $url, $data, $extra),
                'ch' => $ch
            );

        } else {
            $thread_id = uniqid();
            self::$_pull[$thread_id] = array(
                'params' => array($method, $url, $data, $extra)
            );
        }

        return $thread_id;
    }

    /**
     * Parses request URL to use in request
     * @param  string $method request method
     * @param  string $url    request URL
     * @param  mixed  $data   request data
     * @return array  parsed URL and URL-encoded data
     */
    private static function _prepareData($method, $url, $data)
    {
        $components = parse_url($url);

        $upass = '';
        if (!empty($components['user'])) {
            $upass = $components['user'] . (!empty($components['pass']) ? ':' . $components['pass'] : '') . '@';
        }

        if (empty($components['path'])) {
            $components['path'] = '/';
        }

        $port = empty($components['port']) ? '' : (':' . $components['port']);

        $url = $components['scheme'] . '://' . $upass . $components['host'] . $port . $components['path'];

        if (!empty($components['query'])) {
            if ($method == self::GET) {
                parse_str($components['query'], $args);

                if (!empty($data) && !is_array($data) && !empty($args)) {
                    throw new DeveloperException('Http: incompatible data type passed');
                }

                $data = fn_array_merge($args, $data);
            } else {
                $url .= '?' . $components['query'];
            }
        }

        return array($url, is_array($data) ? http_build_query($data) : $data);
    }

    /**
     * Executes request via socket
     * @param  string $method request method
     * @param  string $url    request URL
     * @param  string $data   request data (URL-encoded)
     * @param  array  $extra  extra parameters
     * @return mixed  string response or false if request failed
     */
    private static function _socketRequest($method, $url, $data, $extra)
    {
        $components = parse_url($url);

        // Set default http port (if not set)
        if (empty($components['port'])) {
            $components['port'] = 80;
        }

        // Proxy settings
        $req_settings = self::_getSettings();

        $sh = @fsockopen(
            empty($req_settings['proxy_host']) ? $components['host'] : $req_settings['proxy_host'],
            empty($req_settings['proxy_host']) ? $components['port'] : (empty($req_settings['proxy_port']) ? 3128 : $req_settings['proxy_port']),
            $errno,
            $error,
            !empty($extra['timeout']) ? $extra['timeout'] : self::TIMEOUT
        );

        if ($sh) {

            if ($method == self::GET) {

                if (empty($req_settings['proxy_host'])) {
                    $post_url = $components['path'] . '?' . $data;
                } else {
                    $post_url = $url . '?' . $data;
                }

            } else {
                $post_url = $components['path'] . (!empty($components['query']) ? '?' . $components['query'] : '');
            }

            fputs($sh, "$method $post_url HTTP/1.1\r\n");
            fputs($sh, "Host: $components[host]\r\n");

            if (!empty($req_settings['proxy_user'])) {
                fputs($sh, "Proxy-Authorization: Basic " . base64_encode($req_settings['proxy_user'] . ':' . $req_settings['proxy_password']) . "\r\n");
            }

            if (!empty($extra['referer'])) {
                fputs($sh, 'Referer: ' . $extra['referer'] . "\r\n");
            }
            if (!empty($extra['headers'])) {
                foreach ($extra['headers'] as $header) {
                    if (stripos($header, 'Content-type:') === 0) {
                        $content_type_set = true;
                    }
                    fputs($sh, $header . "\r\n");
                }
            }
            if (!empty($extra['basic_auth'])) {
                fputs($sh, "Authorization: Basic " . base64_encode(implode(':', $extra['basic_auth'])) . "\r\n");
            }
            if (!empty($extra['cookie'])) {
                fputs($sh, 'Cookie: ' . implode('; ', $extra['cookies']) . "\r\n");
            }

            if ($method == self::POST) {
                if (empty($content_type_set)) {
                    fputs($sh, 'Content-type: application/x-www-form-urlencoded' ."\r\n");
                }

                fputs($sh, 'Content-Length: ' . strlen($data) ."\r\n");
                fputs($sh, "\r\n");
                fputs($sh, $data);
            } else {
                fputs($sh, "\r\n");
            }

            $content = '';
            while (!feof($sh)) {
                $content .= fread($sh, 65536);
            }
            fclose($sh);

            if (!empty($content)) {
                $content = self::_parseContent($content);
            }
        } else {
            self::_setError('socket', $error, $errno);
            $content = false;
        }

        return $content;
    }

    /**
     * Executes request via curl
     * @param  string $method request method
     * @param  string $url    request URL
     * @param  string $data   request data (URL-encoded)
     * @param  array  $extra  extra parameters
     * @return mixed  string response or false if request failed
     */
    private static function _curlRequest($method, $url, $data, $extra)
    {
        $ch = curl_init();

        if (!empty($extra['basic_auth'])) {
            curl_setopt($ch, CURLOPT_USERPWD, implode(':', $extra['basic_auth']));
        }
        if (!empty($extra['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $extra['referer']);
        }
        if (!empty($extra['ssl_cert'])) {
            curl_setopt($ch, CURLOPT_SSLCERT, $extra['ssl_cert']);
            if (!empty($extra['ssl_key'])) {
                curl_setopt($ch, CURLOPT_SSLKEY, $extra['ssl_key']);
            }
        }
        if (!empty($extra['timeout'])) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $extra['timeout']);
        }
        if (!empty($extra['headers'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $extra['headers']);
        }
        if (!empty($extra['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, implode('; ', $extra['cookies']));
        }
        if (!empty($extra['binary_transfer'])) {
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        }

        if ($method == self::GET) {
            curl_setopt($ch, CURLOPT_HTTPGET, 1);
            $url .= '?' . $data;

        } elseif ($method == self::POST) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        } else {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if (self::$_curl_followlocation_support) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $req_settings = self::_getSettings();
        if (!empty($req_settings['proxy_host'])) {
            curl_setopt($ch, CURLOPT_PROXY, $req_settings['proxy_host'] . ':' . (empty($req_settings['proxy_port']) ? 3128 : $req_settings['proxy_port']));
            if (!empty($req_settings['proxy_user'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $req_settings['proxy_user'] . (empty($req_settings['proxy_password']) ? '' : ':' . $req_settings['proxy_password']));
            }
        }

        if (!empty($extra['return_handler'])) {
            return $ch;
        }

        $content = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);

        curl_close($ch);

        if (!empty($content)) {
            $content = self::_parseContent($content);
            $content = self::_processHeadersRedirect($method, $url, $extra, $content);
        }

        if (!empty($error)) {
            self::_setError('curl', $error, $errno);
        }

        return $content;
    }

    /**
     * Check headers redirect and process them manually if CURLOPT_FOLLOWLOCATION is disabled
     *
     * @param  string $method  request method
     * @param  string $url     request URL
     * @param  array  $extra   extra parameters
     * @param  mixed  $content request result
     * @return mixed  string response or false if request failed
     */
    private static function _processHeadersRedirect($method, $url, $extra, $content)
    {
        if (!self::$_curl_followlocation_support && preg_match("/\sLocation:\s([^\s]*)/", self::$_headers, $matches)) {
            // manually process redirects

            // If left redirecs amount was not passed leave 10 redirects
            $extra['redirects_left'] = isset($extra['redirects_left']) ? $extra['redirects_left'] : 10;

            if ($extra['redirects_left'] < 1) {
                self::_setError('curl', 'HTTPS: redirect limit exceeded', 0);
                $content = false;
            } else {
                $extra['redirects_left']--;
                $new_url = self::_completeUrl($matches[1], $url);

                // force run requests to process further redirects
                $extra['return_handler'] = false;

                $content = self::_curlRequest($method, $new_url, '', $extra);
            }
        }

        return $content;
    }

    /**
     * Completes url from base url if needed
     *
     * @param  string $url      url to be completed
     * @param  string $base_url basic url to complete checked urls
     * @return string completed url or false if url can not be completed
     */
    private static function _completeUrl($url, $base_url)
    {
        $result = false;
        $parts = parse_url($url);
        $base_parts = parse_url($base_url);

        if (!empty($parts['scheme']) && !empty($parts['host'])) {
            // url is already complete
            $result = $url;
        } elseif (!empty($parts['path']) && !empty($base_parts['scheme']) && !empty($base_parts['host'])) {
            $result = $base_parts['scheme'] . '://' . $base_parts['host'];

            if (!empty($base_parts['port'])) {
                $result .= ':' . $base_parts['port'];
            }

            if (strpos($parts['path'], '/') === 0) {
                // absolute path passed
                $result .= $parts['path'];
            } else {
                $pathinfo = fn_pathinfo($base_parts['path']);
                $result .= $pathinfo['dirname'] . '/' . $parts['path'];
            }

            if (!empty($parts['query'])) {
                $result .= '?' . $parts['query'];
            }
        }

        return $result;
    }
}
