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
use Tygh\Registry;

/**
 * Canada POST shipping service
 */
class Can implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

    private function _getRates($response)
    {
        $doc = new \XMLDocument();
        $xp = new \XMLParser();
        $xp->setDocument($doc);
        $xp->parse($response);
        $doc = $xp->getDocument();
        $rates = array();
        $results = array();

        if (is_object($doc->root)) {
            $root = $doc->getRoot();

            if ($root->getElementByName('ratesAndServicesResponse')) {

            $service_rates = $root->getElementByName('ratesAndServicesResponse');
            $shipment = $service_rates->getElementsByName('product');

                $currencies = Registry::get('currencies');
                if (!empty($currencies['CAD'])) {
                    for ($i = 0; $i < count($shipment); $i++) {
                        $id = $shipment[$i]->getAttribute("id");

                        if (!empty($id) && $id > 0) {
                            $rates[$id] = array(
                                'rate' => floatval($shipment[$i]->getValueByPath("rate")) * $currencies['CAD']['coefficient']
                            );

                            if ($shipment[$i]->getValueByPath("deliveryDate") != '') {
                                $rates[$id]['delivery_time'] = $shipment[$i]->getValueByPath("deliveryDate");
                            }
                            unset($id);
                        }
                    }

                    $results['cost'] = $rates;

                } else {
                    $results['error'] = __('canada_post_activation_error');
                }

            } elseif ($root->getElementByName('error')) {
                $results['error'] = $root->getValueByPath('/error/statusMessage');
            }
        }

        return $results;
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
            'delivery_time' => false,
        );

        $rates = $this->_getRates($response);
        $service_code = $this->_shipping_info['service_code'];

        if (!empty($rates['cost'][$service_code])) {
            $return['cost'] = $rates['cost'][$service_code]['rate'];
            $return['delivery_time'] = $rates['cost'][$service_code]['delivery_time'];
        } else {
            $return['error'] = !empty($rates['error']) ? $rates['error'] : __('service_not_available');
        }

        return $return;
    }

    /**
     * Implementation does not need
     *
     * @return null
     */
    public function processErrors($response)
    {
        return null;
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

        $merchant_id = !empty($shipping_settings['merchant_id']) ? $shipping_settings['merchant_id'] : '';
        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : '0';
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : '0';
        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : '0';

        $origination_postal = $origination['zipcode'];
        $destination_postal = $location['zipcode'];
        $destination_country = $location['country'];
        $destination_city = $location['city'];
        $destination_state = $location['state'];

        $total_cost = $this->_shipping_info['package_info']['C'];
        $weight = $weight_data['full_pounds'] * 0.4536;
        $amount = '1';

        $lang = (CART_LANGUAGE == 'fr') ? 'fr' : 'en';

        $request = <<<XML
<?xml version="1.0" ?>
<eparcel>
    <language>$lang</language>
    <ratesAndServicesRequest>
        <merchantCPCID>$merchant_id</merchantCPCID>
        <fromPostalCode>$origination_postal</fromPostalCode>
        <turnAroundTime> 24 </turnAroundTime>
        <itemsPrice>$total_cost</itemsPrice>
        <lineItems>
            <item>
                <quantity>$amount</quantity>
                <weight>$weight</weight>
                <length>$length</length>
                <width>$width</width>
                <height>$height</height>
                <description>ggrtye</description>
                <readyToShip/>
            </item>
        </lineItems>
        <city>$destination_city</city>
        <provOrState>$destination_state</provOrState>
        <country>$destination_country</country>
        <postalCode>$destination_postal</postalCode>
    </ratesAndServicesRequest>
</eparcel>
XML;

        $request_data = array(
            'method' => 'post',
            'url' => 'http://sellonline.canadapost.ca:30000',
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
        $response = Http::post($data['url'], $data['data']);

        return $response;
    }
}
