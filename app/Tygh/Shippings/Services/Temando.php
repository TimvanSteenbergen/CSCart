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
use Tygh\Registry;
use Tygh\Http;

/**
 * Temando shipping service
 */

class Temando implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    /**
     * Stored shipping information
     *
     * @var array $_shipping_info
     */
    private $_shipping_info = array();

    private function _prepareAnythings()
    {
        $things = array();

        if (!empty($this->_shipping_info['package_info']['packages'])) {
            foreach ($this->_shipping_info['package_info']['packages'] as $key => $data) {
                $things[] = $this->_preparePackage($data);
            }
        }

        return $things;
    }

    private function _preparePackage($data)
    {
        $shipping_settings = $this->_shipping_info['service_params'];
        $package = array(
            'class' => 'General Goods',
            'subclass' => $shipping_settings['temando_subclass'],
            'packaging' => $shipping_settings['temando_package'],
            'palletType' => 'Not Required',
            'palletNature' => 'Not Required',
            'qualifierFreightGeneralFragile' => $shipping_settings['temando_fragile'],
            'length' => (!empty($data['shipping_params']['box_length'])) ? $data['shipping_params']['box_length'] : $shipping_settings['temando_length'],
            'width' => (!empty($data['shipping_params']['box_width'])) ? $data['shipping_params']['box_width'] : $shipping_settings['temando_width'],
            'height' => (!empty($data['shipping_params']['box_height'])) ? $data['shipping_params']['box_height'] : $shipping_settings['temando_height'],
            'weight' => $data['weight'],
            'distanceMeasurementType' => $shipping_settings['temando_measurement'],
            'weightMeasurementType' => $shipping_settings['temando_weight'],
            'quantity' => $data['amount'],
        );

        return $package;
    }

    private function _prepareAnywhere()
    {
        $company_suburb = Registry::get('settings.Company.company_suburb');
        $code = $this->_shipping_info['service_params']['temando_method'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        if ($code != 'Door to Door') {
            $x = array (
                'originState' => $origination['state'],
                'originCity' => $origination['city'],
                'destinationState' => $location['state'],
                'destinationCity' => $location['city']
            );
        } else {
            $x = array (
                'originSuburb' => (!empty($company_suburb)) ? $company_suburb : $origination['city'],
                'destinationSuburb' => $location['suburb']
            );
        }
        $where = array(
            'itemNature' => 'Domestic',
            'itemMethod' => $code,
            'originCountry' => 'AU',
            'originCode' => $origination['zipcode'],
            'destinationCountry' => 'AU',
            'destinationCode' => $location['zipcode'],
            'destinationIs' => 'Residence',
            'originIs' => 'Business'
        );

        return fn_array_merge($where, $x);
    }

    private function _temandoConnect($connect_url, $username, $password)
    {
        ini_set("soap.wsdl_cache_enabled", "1");
        $wsse_url = 'wsse:http://schemas.xmlsoap.org/ws/2002/04/secext';
        $soap_client = new \SoapClient($connect_url, array('soap_version' => SOAP_1_2));
        $headerSecurityStr = '<Security><UsernameToken><Username>' . $username . '</Username><Password>' . $password . '</Password></UsernameToken></Security>';
        $headerSecurityVar = new \SoapVar($headerSecurityStr, XSD_ANYXML);
        $soapHeader = new \SoapHeader($wsse_url, 'soapenv:Header', $headerSecurityVar);
        $soap_client->__setSoapHeaders(array($soapHeader));

        return $soap_client;
    }

    private function _prepageServiceCode($data)
    {
        return $data->carrier->id . '_' . strtoupper(trim(str_replace(' ', '', $data->deliveryMethod)));
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
     * Checks if shipping service allows to use multithreading
     *
     * @return bool true if allow
     */
    public function allowMultithreading()
    {
        return $this->_allow_multithreading;
    }

    /**
     * Prepare request information
     *
     * @return array Prepared data
     */
    public function getRequestData()
    {
        $shipping_settings = $this->_shipping_info['service_params'];
        $username = !empty($shipping_settings['username']) ? $shipping_settings['username'] : '';
        $password = !empty($shipping_settings['password']) ? $shipping_settings['password'] : '';
        $readydate = date("Y-m-d", TIME() + $shipping_settings['temando_readydate'] * 86400);

        $request_data['data'] = array (
            'anythings' => $this->_prepareAnythings(),
            'anytime' => array('readyDate' => $readydate, 'readyTime' => 'AM'),
            'anywhere' => $this->_prepareAnywhere(),
            'general' => array('goodsValue' => $this->_shipping_info['package_info']['C'])
        );

        $url = (!empty($shipping_settings['test_mode']) && $shipping_settings['test_mode'] == 'Y') ? 'https://training-api.temando.com/schema/2009_06/server.wsdl' : 'https://api.temando.com/schema/2009_06/server.wsdl';
        $request_data['client'] = $this->_temandoConnect($url, $username, $password);

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

        try {
            $response = $data['client']->getQuotesByRequest($data['data']);
        } catch (\SoapFault $exception) {
            $response = $exception;
        }

        return $response;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($result)
    {
        $err_message = strval($result->faultcode . ': ' . $result->faultstring);
        fn_log_event('general', 'runtime', array(
            'function' => 'getQuotesByRequest',
            'message' => __('temando_system') . ': ' . $err_message,
        ));

        return $err_message;
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

        $code = $this->_shipping_info['service_code'];
        $rates = $this->processRates($response);

        if (isset($rates[$code])) {
            $return['cost'] = $rates[$code];
        } else {
            $return['error'] = $this->processErrors($response);
        }

        return $return;
    }

    /**
     * Gets shipping service rate
     *
     * @param  string $result Reponse from Shipping service server
     * @return array  Shipping service rate
     */
    public function processRates($result)
    {
        if (!isset($result->quote)) {
            return array();
        }

        $rates = array();
        foreach ($result->quote as $k => $data) {
            $key = $this->_prepageServiceCode($data);
            $rates[$key] = $data->totalPrice;
        }

        return $rates;
    }
}
