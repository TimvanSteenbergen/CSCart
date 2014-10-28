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

use Tygh\Http;

class Pdf
{
    private static $_transaction_id;
    private static $_url = 'http://converter.cart-services.com';

    /**
     * Pushes HTML code to batch to render PDF later
     * @param  string  $html HTML code
     * @return boolean true if transaction created, false - otherwise
     */
    public static function batchAdd($html)
    {
        $transaction_id = Http::post(self::_action('/pdf/batch/add'),
            json_encode(array(
                'transaction_id' => !empty(self::$_transaction_id) ? self::$_transaction_id : '',
                'content' => self::_convertImages($html)
            )), array(
                'headers' => array(
                    'Content-type: application/json',
                    'Accept: application/json'
                ),
                'binary_transfer' => true
            ));

        if (!empty($transaction_id) && empty(self::$_transaction_id)) {
            self::$_transaction_id = json_decode($transaction_id);
        }

        return !empty($transaction_id);
    }

    /**
     * Renders PDF document by transaction ID
     *
     * @param  string  $filename filename to save PDF or name of attachment to download
     * @param  boolean $save     saves to file if true, outputs if not
     * @param  array   $params   params to post along with request
     * @return mixed   true if document saved, false on failure or outputs document
     */
    public static function batchRender($filename = '', $save = false, $params = array())
    {
        $default_params = array(
            'transaction_id' => self::$_transaction_id,
            'page_size' => 'A4'
        );

        $params = array_merge($default_params, $params);

        $content = Http::post(self::_action('/pdf/batch/render'), json_encode($params), array(
            'headers' => array(
                'Content-type: application/json',
                'Accept: application/pdf'
            ),
            'binary_transfer' => true
        ));

        self::$_transaction_id = null;

        if (!empty($content)) {
            return self::_output($content, $filename, $save);
        }

        return false;
    }

    /**
     * Render PDF document from HTML code
     * @param  string  $html     HTML code
     * @param  string  $filename filename to save PDF or name of attachment to download
     * @param  boolean $save     saves to file if true, outputs if not
     * @param  array   $params   params to post along with request
     * @return mixed   true if document saved, false on failure or outputs document
     */
    public static function render($html, $filename = '', $save = false, $params = array())
    {
        if (is_array($html)) {
            $html = implode("<div style='page-break-before: always;'>&nbsp;</div>", $html);
        }

        if (self::_isLocalIP(gethostbyname($_SERVER['HTTP_HOST']))) {
            $html = self::_convertImages($html);
        }

        $default_params = array(
            'content' => $html,
            'page_size' => 'A4'
        );

        $params = array_merge($default_params, $params);        

        $content = Http::post(self::_action('/pdf/render'), json_encode($params), array(
            'headers' => array(
                'Content-type: application/json',
                'Accept: application/pdf'
            ),
            'binary_transfer' => true
        ));

        if (!empty($content)) {
            return self::_output($content, $filename, $save);
        }

        return false;
    }

    /**
     * Generates service URL
     * @param  string $action action
     * @return string formed URL
     */
    private static function _action($action)
    {
        return self::$_url . $action;
    }

    /**
     * Saves PDF document or outputs it
     * @param  string  $content  PDF document
     * @param  string  $filename filename to save PDF or name of attachment to download
     * @param  boolean $save     saves to file if true, outputs if not
     * @return mixed   true if document saved, false on failure or outputs document
     */
    private static function _output($content, $filename = '', $save = false)
    {
        if (!empty($filename) && strpos($filename, '.pdf') === false) {
            $filename .= '.pdf';
        }

        if (!empty($filename) && $save == true) {
            return fn_put_contents($filename, $content);

        } else {
            if (!empty($filename)) {
                $filename = fn_basename($filename);
                header("Content-disposition: attachment; filename=\"$filename\"");
            }

            header('Content-type: application/pdf');
            fn_echo($content);
            exit;
        }

        return false;
    }

    /**
     * Converts images links to image:data attribute
     * @param  string $html html code
     * @return string html code with converted links
     */
    private static function _convertImages($html)
    {
        $http_location = Registry::get('config.http_location');
        $https_location = Registry::get('config.https_location');
        $http_path = Registry::get('config.http_path');
        $https_path = Registry::get('config.https_path');
        $files = array();

        if (preg_match_all("/(?<=\ssrc=|\sbackground=)('|\")(.*)\\1/SsUi", $html, $matches)) {
            $files = fn_array_merge($files, $matches[2], false);
        }

        if (preg_match_all("/(?<=\sstyle=)('|\").*url\(('|\"|\\\\\\1)(.*)\\2\).*\\1/SsUi", $html, $matches)) {
            $files = fn_array_merge($files, $matches[3], false);
        }

        if (empty($files)) {
            return $html;
        } else {
            $files = array_unique($files);

            foreach ($files as $k => $_path) {
                $path = str_replace('&amp;', '&', $_path);

                $real_path = '';
                // Replace url path with filesystem if this url is NOT dynamic
                if (strpos($path, '?') === false && strpos($path, '&') === false) {
                    if (($i = strpos($path, $http_location)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($http_location));
                    } elseif (($i = strpos($path, $https_location)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($https_location));
                    } elseif (!empty($http_path) && ($i = strpos($path, $http_path)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($http_path));
                    } elseif (!empty($https_path) && ($i = strpos($path, $https_path)) !== false) {
                        $real_path = substr_replace($path, Registry::get('config.dir.root'), $i, strlen($https_path));
                    }
                }

                if (empty($real_path)) {
                    $real_path = (strpos($path, '://') === false) ? $http_location .'/'. $path : $path;
                }

                list($width, $height, $mime_type) = fn_get_image_size($real_path);

                if (!empty($width)) {
                    $content = fn_get_contents($real_path);
                    $html = preg_replace("/(['\"])" . str_replace("/", "\/", preg_quote($_path)) . "(['\"])/Ss", "\\1data:$mime_type;base64," . base64_encode($content) . "\\2", $html);
                }
            }
        }

        return $html;
    }

    /**
     * Checks if server IP address is local
     * @param  string  $ip IP address
     * @return boolean true if IP is local, false - if public
     */
    private static function _isLocalIP($ip)
    {
        $ranges = array(
            '10' => array(
                'min' => ip2long('10.0.0.0'),
                'max' => ip2long('10.255.255.255')
            ),
            '192' => array(
                'min' => ip2long('192.168.0.0'),
                'max' => ip2long('192.168.255.255')
            ),
            '127' => array(
                'min' => ip2long('127.0.0.0'),
                'max' => ip2long('127.255.255.255')
            ),
            '172' => array(
                'min' => ip2long('172.16.0.0'),
                'max' => ip2long('172.31.255.255')
            ),
        );

        $ip = ip2long($ip);

        foreach ($ranges as $range) {
            if ($ip >= $range['min'] && $ip <= $range['max']) {
                return true;
            }
        }

        return false;
    }
}
