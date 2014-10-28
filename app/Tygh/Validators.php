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
use Tygh\Http;

class Validators
{
    /**
     * Email validator
     *
     * @param  array $email
     * @return bool  true if email is valid
     */
    public function isEmailValid($email)
    {
        if (!fn_validate_email($email)) {
            return false;
        }

        return true;
    }

    /**
     * Check if json_encode/decode functions are exist
     *
     * @return bool true if exist
     */
    public function isJsonAvailable()
    {
        if (!function_exists('json_decode') && !function_exists('json_encode')) {
            return false;
        }

        return true;
    }

    /**
     * Check if mod_rewrite is available
     *
     * @return bool true if available
     */
    public function isModRewriteEnabled()
    {
        if (defined('PRODUCT_EDITION') && PRODUCT_EDITION == 'ULTIMATE') {
            // IIS Web-Servers fix
            if (!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

                if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] != '') {
                    $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
                }
            }

            $url =  'http://' . $_SERVER['HTTP_HOST'] . str_replace('index.php', '', $_SERVER['REQUEST_URI']);
            $url .= 'mod_rewrite';

            Http::get($url);
            $headers = Http::getHeaders();

            if (strpos($headers, '200 OK') === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if register_globals disabled
     *
     * @return bool true if exist
     */
    public function isGlobalsDisabled()
    {
        $checking_result = (Bootstrap::getIniParam('register_globals') == true) ? false : true;

        return $checking_result;
    }

    /**
     * Check if mbstring.func_overload does not equal 2
     *
     * @return bool true if exist
     */
    public function isFuncOverloadAcceptable()
    {
        $checking_result = (Bootstrap::getIniParam('mbstring.func_overload', true) >= 2) ? false : true;

        return $checking_result;
    }

    /**
     * Check if necessary MySQL version is supported by server
     *
     * @return bool true if supported
     */
    public function isMysqlSupported()
    {
        $exts  = get_loaded_extensions();
        $mysqli_support = in_array('mysqli', $exts) ? true : false;
        $pdo_support = in_array('pdo_mysql', $exts) ? true : false;

        $checking_result = $mysqli_support || $pdo_support ? true : false;

        return $checking_result;
    }

    /**
     * Check if necessary cUrl version is supported by server
     *
     * @return bool true if supported
     */
    public function isCurlSupported()
    {
        $checking_result = function_exists('curl_init') ? true : false;

        return $checking_result;
    }

    /**
     * Check if SafeMode is disabled
     *
     * @return bool true if disabled
     */
    public function isSafeModeDisabled()
    {
        $checking_result = (Bootstrap::getIniParam('safe_mode') == true) ? false : true;

        return $checking_result;
    }

    /**
     * Check if cart can upload files to server
     *
     * @return bool true if can
     */
    public function isFileUploadsSupported()
    {
        $checking_result = (Bootstrap::getIniParam('file_uploads') == true) ? true : false;

        return $checking_result;
    }

    public function isPharDataAvailable()
    {
        $result = class_exists('PharData');

        return $result;
    }

    public function isZipArchiveAvailable()
    {
        $result = class_exists('ZipArchive');

        return $result;
    }

    /**
     * Check if ModeSecurity is disabled
     *
     * @return bool true if disabled
     */
    public function isModeSecurityDisabled()
    {
        $checking_result = true;

        ob_start();
        phpinfo(INFO_MODULES);
        $_info = ob_get_contents();
        ob_end_clean();

        if (strpos($_info, 'mod_security') !== false) {
            $checking_result = false;
        }

        return $checking_result;
    }

    /**
     * Check if session.autostart is disabled
     *
     * @return bool true if disabled
     */
    public function isSessionAutostartDisabled()
    {
        $checking_result = (Bootstrap::getIniParam('session.auto_start') == true) ? false : true;

        return $checking_result;
    }

}
