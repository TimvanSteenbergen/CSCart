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

use Tygh\Registry;
use Tygh\Tools\Url;

class Embedded
{
    /**
     * Checks if embedded mode is enabled
     * @return boolean true if enabled, false - otherwise
     */
    public static function isEnabled()
    {
        return !empty($_SESSION['embedded']['enabled']);
    }

    /**
     * Enables embedded mode
     */
    public static function enable()
    {
        $_SESSION['embedded']['enabled'] = true;
    }

    /**
     * Inits embedded mode session data
     */
    public static function init()
    {
        if (empty($_SESSION['embedded'])) {
            $_SESSION['embedded'] = array();
        }

        $_SESSION['embedded']['enabled'] = false;
    }

    /**
     * Gets URL of the page, store is embedded to
     * @return string URL
     */
    public static function getUrl()
    {
        return !empty($_SESSION['embedded']['url']) ? $_SESSION['embedded']['url'] : '';
    }

    /**
     * Sets URL of the page, store is embedded to
     *
     * @param string $url URL
     */
    public static function setUrl($url)
    {
        $_SESSION['embedded']['url'] = $url;

        $_purl = parse_url($url);

        if (!empty($_purl['query'])) {

            parse_str($_purl['query'], $params);
            self::setParams($params);

        } else {
            self::setParams(array());
        }
    }

    /**
     * Sets URL params of the page, store is embedded to
     *
     * @param array $params params
     */
    public static function setParams($params)
    {
        $_SESSION['embedded']['params'] = $params;
    }

    /**
     * Gets URL params of the page, store is embedded to
     * return array params
     */
    public static function getParams()
    {
        return !empty($_SESSION['embedded']['params']) ? $_SESSION['embedded']['params'] : array();
    }

    /**
     * Resolves URL to the format appropriate for widget mode
     *
     * @param string $url URL
     */
    public static function resolveUrl($url)
    {
        $url = str_replace('&amp;', '&', $url);

        if (parse_url($url, PHP_URL_SCHEME) == '' && strpos($url, '//') !== 0) {
            $url = Url::resolve($url, Registry::get('config.current_location'));
        }

        $path = Registry::get('config.current_host') . Registry::get('config.current_path');

        if (false !== ($pos = strpos($url, $path))) {

            $query = urlencode(substr($url, $pos + strlen($path)));

            $params = self::getParams();

            if (!empty($params['fb_app_id']) && !empty($params['fb_page_id'])) {
                $url = sprintf(
                    "https://www.facebook.com/pages/~/%s?sk=app_%s&app_data=%s",
                    $params['fb_page_id'],
                    $params['fb_app_id'],
                    $query
                );
            } else {
                $url = self::getUrl() . "#!$query";
            }
        }

        return $url;
    }

    /**
     * Processes payment form to make payment submit via non-embedded mode
     * @param string $submit_url payment submit URL
     * @param array $data payment data
     * @param array $payment_name payment name
     * @param boolean $exclude_empty_values flag to exclude empty values
     * @param string $method submit method
     * @return array data to submit form to host server
     */
    public static function processPaymentForm($submit_url, $data, $payment_name, $exclude_empty_values, $method)
    {
        $data = array(
            Session::getName() => Session::getId(),
            'data' => json_encode(array(
                'submit_url' => $submit_url,
                'data' => $data,
                'payment_name' => $payment_name,
                'method' => $method,
                'exclude_empty_values' => $exclude_empty_values
            )) 
        );

        $submit_url = fn_url('payment_notification.process_embedded');
        $method = 'post';
        $payment_name = '';

        return array(
            $submit_url,
            $data,
            $method,
            $payment_name
        );
    }

    public static function leave()
    {
        if (self::isEnabled()) {
            $_SESSION['embedded']['leave'] = true;
        }
    }

    public static function isLeft()
    {
        return !empty($_SESSION['embedded']['leave']) ? $_SESSION['embedded']['leave'] : false;
    }
}
