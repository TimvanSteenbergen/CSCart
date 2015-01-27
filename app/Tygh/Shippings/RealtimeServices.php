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

namespace Tygh\Shippings;

use Tygh\Http;
use Tygh\Shippings\Services;

class RealtimeServices
{
    const SERVICE_NOT_CONFIGURED = 'shippings.service_not_configured';
    const SERVICE_NOT_FOUND = 'shippings.service_not_found';
    const SERVICE_NOT_ERROR = '';

    /**
     * Stack for registered shipping services
     *
     * @var array $_services_stack
     */
    private static $_services_stack = array();

    /**
     * Result rates
     *
     * @var array $_rates
     */
    private static $_rates = array();

    private static function _processErrorCode($code)
    {
        return __($code);
    }

    /**
     * Check if multireading is available on the server
     *
     * @return bool True if available, false otherwise
     */
    private static function _checkMultithreading()
    {
        if (function_exists('curl_multi_init') && Http::getCurlInfo() == '') {
            $allow_multithreading = true;
            $h_curl_multi = curl_multi_init();
            $threads = array();
        } else {
            $allow_multithreading = false;
        }

        return $allow_multithreading;
    }

    /**
     * Adds shipping service data to stack for future calculations
     *
     * @param  int   $shipping_key  Shipping service array position
     * @param  array $shipping_info Shipping service data
     * @return bool  true if information was added to stack, false otherwise
     */
    public static function register($shipping_key, $shipping_info)
    {
        if (empty($shipping_info['service_params'])) {
            return self::_processErrorCode(self::SERVICE_NOT_CONFIGURED);
        }

        $module = fn_camelize($shipping_info['module']);
        $module = 'Tygh\\Shippings\\Services\\' . $module;

        if (class_exists($module)) {
            $module_obj = new $module;
            $module_obj->prepareData($shipping_info);

            self::$_services_stack[$shipping_key] = $module_obj;
        } else {
            return self::_processErrorCode(self::SERVICE_NOT_FOUND);
        }

        return self::_processErrorCode(self::SERVICE_NOT_ERROR);
    }

    /**
     * Sends requests to real-time services and process responses.
     *
     * @return array Shipping method rates list
     */
    public static function getRates()
    {
        $_services = array(
            'multi' => array(),
            'simple' => array(),
        );

        if (empty(self::$_services_stack)) {
            return array();
        }

        if (self::_checkMultithreading()) {
            foreach (self::$_services_stack as $shipping_key => $service_object) {
                if ($service_object->allowMultithreading()) {
                    $key = 'multi';
                } else {
                    $key = 'simple';
                }

                $_services[$key][$shipping_key] = $service_object;
            }

        } else {
            $_services['simple'] = self::$_services_stack;
        }

        if (!empty($_services['multi'])) {
            foreach ($_services['multi'] as $shipping_key => $service_object) {
                $data = $service_object->getRequestData();

                $headers = empty($data['headers']) ? array() : $data['headers'];
                if ($data['method'] == 'post') {
                    Http::mpost($data['url'], $data['data'], array('callback' => array('\Tygh\Shippings\RealtimeServices::multithreadingCallback', $shipping_key), 'headers' => $headers));
                } else {
                    Http::mget($data['url'], $data['data'], array(
                        'callback' => array('\Tygh\Shippings\RealtimeServices::multithreadingCallback', $shipping_key),
                        'headers' => $headers));
                }
            }

            Http::processMultiRequest();
        }

        if (!empty($_services['simple'])) {
            foreach ($_services['simple'] as $shipping_key => $service_object) {
                $response = $service_object->getSimpleRates();
                self::multithreadingCallback($response, $shipping_key);
            }
        }

        return self::$_rates;
    }

    public static function multithreadingCallback($result, $shipping_key)
    {
        $object = self::$_services_stack[$shipping_key];

        $rate = $object->processResponse($result);

        self::$_rates[] = array(
            'price' => $rate['cost'],
            'error' => $rate['error'],
            'shipping_key' => $shipping_key,
            'delivery_time' => isset($rate['delivery_time']) ? $rate['delivery_time'] : false,
        );
    }

    /**
     * Clears shipping services stack
     */
    public static function clearStack()
    {
        self::$_services_stack = array();
        self::$_rates = array();
    }

    public function __construct()
    {

    }
}
