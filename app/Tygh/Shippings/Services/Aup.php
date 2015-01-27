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

namespace Tygh\Shippings\Services;

use Tygh\Shippings\IService;
use Tygh\Http;

/**
 * UPS shipping service
 */
class Aup implements IService
{
    const RPI_COEFFICIENT = 6;
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

    /**
     * Stack for errors occured during the preparing rates process
     *
     * @var array $_error_stack
     */
    private $_error_stack = array();

    private function _internalError($error)
    {
        $this->_error_stack[] = $error;
    }

    /**
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    /**
     * Sets data to internal class variable
     *
     * @param array $shipping_info
     */
    public function prepareData($shipping_info)
    {
        $this->_shipping_info = $shipping_info;
    }

     /**
     * Gets shipping cost and information about possible errors
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return array  Shipping cost and errors
     */
    public function processResponse($response)
    {
        $return = array(
            'cost' => false,
            'error' => false,
        );

        if (!empty($response)) {
            $response = explode("\n", $response);
            if (preg_match("/charge=([\d\.]+)/i", $response[0], $matches)) {
                $shipping_settings = $this->_shipping_info['service_params'];

                if (!empty($matches[1])) {
                    $cost = (double) trim($matches[1]);
                    if ($this->_shipping_info['service_code'] == 'RPI') {
                        $cost += (double) (!empty($shipping_settings['rpi_fee']) ? $shipping_settings['rpi_fee'] : self::RPI_COEFFICIENT);
                    }
                    if (!empty($shipping_settings['use_delivery_confirmation']) && $shipping_settings['use_delivery_confirmation'] == 'Y') {
                        $cost += ($this->_shipping_info['service_code'] == 'STANDARD' || $this->_shipping_info['service_code'] == 'EXPRESS')? (double) (!empty($shipping_settings['delivery_confirmation_cost']) ? $shipping_settings['delivery_confirmation_cost'] : 0) : (double) (!empty($shipping_settings['delivery_confirmation_international_cost']) ? $shipping_settings['delivery_confirmation_international_cost'] : 0);
                    }

                    $return['cost'] = $cost;
                } else {
                    $return['error'] = $this->processErrors($response);
                }
            }
        }

        return $return;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        $error = '';

        if (preg_match("/err_msg=([\w ]*)/i", $response[2], $matches)) {
            $error = $matches[1];
        }

        if (!empty($this->_error_stack)) {
            foreach ($this->_error_stack as $_error) {
                $error .= '; ' . $_error;
            }
        }

        return $error;
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        $weight = $weight_data['full_pounds'] * 453.6;

        $packages_count = 1;

        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : 0;
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : 0;
        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : 0;

        if (!empty($this->_shipping_info['package_info']['packages'])) {
            $packages = $this->_shipping_info['package_info']['packages'];
            $packages_count = count($packages);
            if ($packages_count > 0) {
                // Default to parameters of first box - all boxes may not be the same,
                // but this is best we can do if we can make only one call to Aus
                // Post API.
                $package = $packages[0];

                // If there is more than one package we need to adjust the weight
                // to a weight per package.
                if ($packages_count > 1) {
                    // divide total weight by number of packages.
                    $weight /= $packages_count;
                }

                if (!empty($package['shipping_params'])) {
                    $package_settings = $package['shipping_params'];
                    $length = empty($package_settings['box_length']) ? 0 : $package_settings['box_length'];
                    $width = empty($package_settings['box_width']) ? 0 : $package_settings['box_width'];
                    $height = empty($package_settings['box_height']) ? 0 : $package_settings['box_height'];
                }
            }
        }

        // Registered Post International: price as Air Mail, plus $6, weight limit of 2kg.
        if ($this->_shipping_info['service_code'] == 'RPI' && $weight > 2000) {
            $this->_internalError(__('illegal_item_weight'));
        }

        $request = array(
            'Pickup_Postcode' => $origination['zipcode'],
            'Destination_Postcode' => $location['zipcode'],
            'Country' => $location['country'],
            'Weight' => $weight,
            'Length' => $length * 10,
            'Width' => $width * 10,
            'Height' => $height * 10,
            'Service_type' => ($this->_shipping_info['service_code'] == 'RPI') ? 'AIR' : $this->_shipping_info['service_code'],
            'Quantity' => $packages_count,
        );

        $url = 'http://drc.edeliver.com.au/ratecalc.asp';

        $request_data = array(
            'method' => 'get',
            'url' => $url,
            'data' => $request,
        );

        return $request_data;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $data = $this->getRequestData();
        $response = Http::get($data['url'], $data['data']);

        return $response;
    }
}
