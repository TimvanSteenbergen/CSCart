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
class Fedex implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

    /**
     * Stored shipping information
     *
     * @var array $_shipping_info
     */
    private $_shipping_info = array();

    /**
     * Adds shipping information to request data
     *
     * @param  array  $request          prepared request data
     * @param  array  $address          Address data (Zipcode, Country, State, etc)
     * @param  string $address_type_key Recipient or Shipper
     * @param  string $code             Service code (E.g.: SMART_POST)
     * @return array  Prepared request data with new address information
     */
    private function _prepareAddress($request, $address, $address_type_key, $code = '')
    {
        $request[$address_type_key]['Address']['StreetLines']  = !empty($address['address']) ? $address['address'] : '';
        preg_match_all("/[\d\w]/", $address['zipcode'], $matches);
        $request[$address_type_key]['Address']['PostalCode'] = !empty($matches[0]) ? implode('', $matches[0]) : '';
        $request[$address_type_key]['Address']['City'] = !empty($address['city']) ? $address['city'] : '';
        $request[$address_type_key]['Address']['StateOrProvinceCode'] = (strlen($address['state']) > 2) ? '' : $address['state'];
        $request[$address_type_key]['Address']['CountryCode'] = $address['country'];

        $request[$address_type_key]['Contact']['PersonName'] = isset($address['firstname']) ? ($address['firstname'] . ' ' . $address['lastname']) : $address['name'];

        $_phone = '8001234567';
        if (isset($address['phone'])) {
            preg_match_all("/[\d]/", $address['phone'], $matches);
            if (!empty($matches[0])) {
                $_phone = implode('', $matches[0]);
            }
        }
        $request[$address_type_key]['Contact']['PhoneNumber'] = (strlen($_phone) >= 10) ? substr($_phone, 0, 10) : str_pad($_phone, 10, '0');

        if ($address_type_key == 'Recipient' && ($code == 'GROUND_HOME_DELIVERY' || empty($address['address_type']) || (!empty($address['address_type']) && $address['address_type'] == 'residential'))) {
            $request[$address_type_key]['Address']['Residential'] = true;
        }

        if ($address_type_key == 'Recipient' && $code == 'FEDEX_GROUND') {
            $request[$address_type_key]['Address']['Residential'] = false;
        }

        return $request;
    }

    /**
     * Adds information about products to request
     *
     * @param  array $weight_data       Weight information about current package
     * @param  array $shipping_settings Shipping properties
     * @param  array $package_info      List of products in current package
     * @return array Prepared package information
     */
    private function _buildPackages($weight_data, $shipping_settings, $package_info)
    {
        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : 0;
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : 0;
        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : 0;
        if (empty($package_info['packages'])) {
            $packages =<<<EOT
                    <v9:PackageCount>1</v9:PackageCount>
                    <v9:PackageDetail>INDIVIDUAL_PACKAGES</v9:PackageDetail>

                    <v9:RequestedPackageLineItems>
                        <v9:Weight>
                            <v9:Units>LB</v9:Units>
                            <v9:Value>{$weight_data['full_pounds']}</v9:Value>
                        </v9:Weight>
                        <v9:Dimensions>
                            <v9:Length>{$length}</v9:Length>
                            <v9:Width>{$width}</v9:Width>
                            <v9:Height>{$height}</v9:Height>
                            <v9:Units>IN</v9:Units>
                        </v9:Dimensions>
                    </v9:RequestedPackageLineItems>
EOT;
        } else {
            $count = count($package_info['packages']);
            $packages =<<<EOT
                    <v9:PackageCount>{$count}</v9:PackageCount>
                    <v9:PackageDetail>INDIVIDUAL_PACKAGES</v9:PackageDetail>
EOT;
            foreach ($package_info['packages'] as $package) {
                $package_length = empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'];
                $package_width = empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'];
                $package_height = empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height'];
                $weight_ar = fn_expand_weight($package['weight']);
                $weight = $weight_ar['full_pounds'];

                $packages .=<<<EOT
                    <v9:RequestedPackageLineItems>
                        <v9:Weight>
                            <v9:Units>LB</v9:Units>
                            <v9:Value>{$weight}</v9:Value>
                        </v9:Weight>
                        <v9:Dimensions>
                            <v9:Length>{$package_length}</v9:Length>
                            <v9:Width>{$package_width}</v9:Width>
                            <v9:Height>{$package_height}</v9:Height>
                            <v9:Units>IN</v9:Units>
                        </v9:Dimensions>
                    </v9:RequestedPackageLineItems>
EOT;
            }
        }

        return $packages;
    }

    /**
     * Formats XML data from prepared request
     *
     * @param  array  $options Prepared request information
     * @return string XML request
     */
    private function _formatXml($options)
    {
        $xml_req = '<?xml version="1.0" encoding="UTF-8"?>
            <SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:v9="http://fedex.com/ws/rate/v9">
                <SOAP-ENV:Body>
                    <v9:RateRequest>
                        <v9:WebAuthenticationDetail>
                            <v9:UserCredential>
                                <v9:Key>' . $options['WebAuthenticationDetail']['UserCredential']['Key'] . '</v9:Key>
                                <v9:Password>' . $options['WebAuthenticationDetail']['UserCredential']['Password'] . '</v9:Password>
                            </v9:UserCredential>
                        </v9:WebAuthenticationDetail>
                        <v9:ClientDetail>
                            <v9:AccountNumber>' . $options['ClientDetail']['AccountNumber'] . '</v9:AccountNumber>
                            <v9:MeterNumber>' . $options['ClientDetail']['MeterNumber'] . '</v9:MeterNumber>
                        </v9:ClientDetail>
                        <v9:TransactionDetail>
                            <v9:CustomerTransactionId>Rates Request</v9:CustomerTransactionId>
                        </v9:TransactionDetail>
                        <v9:Version>
                            <v9:ServiceId>crs</v9:ServiceId>
                            <v9:Major>9</v9:Major>
                            <v9:Intermediate>0</v9:Intermediate>
                            <v9:Minor>0</v9:Minor>
                        </v9:Version>
                        <v9:RequestedShipment>
                            <v9:DropoffType>' . $options['DropoffType'] . '</v9:DropoffType>
                            <v9:PackagingType>' . $options['PackagingType'] . '</v9:PackagingType>
                            <v9:Shipper>
                                <v9:Address>
                                    <v9:StreetLines>' . htmlspecialchars($options['Shipper']['Address']['StreetLines']) . '</v9:StreetLines>
                                    <v9:City>' . htmlspecialchars($options['Shipper']['Address']['City']) . '</v9:City>
                                    <v9:StateOrProvinceCode>' . $options['Shipper']['Address']['StateOrProvinceCode'] . '</v9:StateOrProvinceCode>
                                    <v9:PostalCode>' . $options['Shipper']['Address']['PostalCode'] . '</v9:PostalCode>
                                    <v9:CountryCode>' . $options['Shipper']['Address']['CountryCode'] . '</v9:CountryCode>
                                </v9:Address>
                            </v9:Shipper>
                            <v9:Recipient>
                                <v9:Address>
                                    <v9:StreetLines>' . htmlspecialchars($options['Recipient']['Address']['StreetLines']) . '</v9:StreetLines>
                                    <v9:City>' . htmlspecialchars($options['Recipient']['Address']['City']) . '</v9:City>
                                    <v9:StateOrProvinceCode>' . $options['Recipient']['Address']['StateOrProvinceCode'] . '</v9:StateOrProvinceCode>
                                    <v9:PostalCode>' . $options['Recipient']['Address']['PostalCode'] . '</v9:PostalCode>
                                    <v9:CountryCode>' . $options['Recipient']['Address']['CountryCode'] . '</v9:CountryCode>';
                if (!empty($options['Recipient']['Address']['Residential'])) {
                    $xml_req .= '
                                    <v9:Residential>true</v9:Residential>';
                }
                $xml_req .= '
                                </v9:Address>
                            </v9:Recipient>
                            <v9:ShippingChargesPayment>
                                <v9:PaymentType>SENDER</v9:PaymentType>
                                <v9:Payor>
                                    <v9:AccountNumber>' . $options['ClientDetail']['AccountNumber'] . '</v9:AccountNumber>
                                    <v9:CountryCode>' . $options['Shipper']['Address']['CountryCode'] . '</v9:CountryCode>
                                </v9:Payor>
                            </v9:ShippingChargesPayment>';
                if (!empty($options['RequestedShipment']['SmartPostDetail'])) {
                    $xml_req .= '
                            <v9:SmartPostDetail>';
                    foreach ($options['RequestedShipment']['SmartPostDetail'] as $k => $v) {
                        $xml_req .= '
                                <v9:' . $k . '>' . $v . '</v9:' . $k . '>';
                    }
                    $xml_req .= '
                            </v9:SmartPostDetail>';
                }
                $xml_req .= '
                            <v9:RateRequestTypes>LIST</v9:RateRequestTypes>
            ';
                $xml_req .= $options['Packages'] . '

                        </v9:RequestedShipment>
                    </v9:RateRequest>
                </SOAP-ENV:Body>
            </SOAP-ENV:Envelope>';

        return $xml_req;
    }

    /**
     * Gets shipping service rate
     *
     * @param  string $result Reponse from Shipping service server
     * @return array  Shipping service rate
     */
    public function processRates($result)
    {
        $result = str_replace(array('<v9:', '<soapenv:', '<env:', '<SOAP-ENV:', '<ns:'), '<', $result);
        $result = str_replace(array('</v9:', '</soapenv:', '</env:', '</SOAP-ENV:', '</ns:'), '</', $result);
        $xml = @simplexml_load_string($result);

        $return = array();

        if ($xml && $xml->Body->RateReply->RateReplyDetails) {
            foreach ($xml->Body->RateReply->RateReplyDetails as $item) {
                $service_code = (string) $item->ServiceType;
                $total_charge = (string) $item->RatedShipmentDetails[0]->ShipmentRateDetail->TotalNetCharge->Amount;
                if (!empty($total_charge)) {
                    $return[$service_code] = $total_charge;
                }
            }
        }

        return $return;
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

        $code = $this->_shipping_info['service_code'];
        // FIXME: FexEx returned GROUND for international as "FEDEX_GROUND" and not INTERNATIONAL_GROUND
        // We sent a request to clarify this situation to FedEx.
        $intl_code = str_replace('INTERNATIONAL_', 'FEDEX_', $code);
        $rates = $this->processRates($response);

        if (isset($rates[$code]) || isset($rates[$intl_code])) {
            $return['cost'] = isset($rates[$code]) ? $rates[$code] : $rates[$intl_code];
        } else {
            $return['error'] = $this->processErrors($response);
        }

        return $return;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($result)
    {
        $error = array();

        $result = str_replace(array('<v9:', '<soapenv:', '<env:', '<SOAP-ENV:', '<ns:'), '<', $result);
        $result = str_replace(array('</v9:', '</soapenv:', '</env:', '</SOAP-ENV:', '</ns:'), '</', $result);
        $xml = @simplexml_load_string($result);

        if ($xml) {
            $rate_reply = $xml->Body->RateReply;
            if ($rate_reply) {
                if ((string) $rate_reply->HighestSeverity == 'SUCCESS') {
                    $error['type'] = 'ERROR';
                    $error['code'] = '';
                    $error['message'] = __('service_not_available');
                } else {
                    $error['type'] = (string) $rate_reply->HighestSeverity;
                    $error['code'] = (string) $rate_reply->Notifications->Code;
                    $error['message'] = (string) $rate_reply->Notifications->Message;
                }
            } else {
                $error = array('type' => 'ERROR', 'code' => '', 'message' => 'Unknown error');
            }

            return implode(' ', $error);
        }

        return false;
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
        $code = $this->_shipping_info['service_code'];
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];

        $fedex_options = array();

        if ($code == 'SMART_POST' && !empty($shipping_settings['hub_id']) && !empty($shipping_settings['indicia'])) {
            $fedex_options['RequestedShipment']['SmartPostDetail']['Indicia'] = $shipping_settings['indicia'];
            if (!empty($shipping_settings['ancillary_endorsement'])) {
                $fedex_options['RequestedShipment']['SmartPostDetail']['AncillaryEndorsement'] = $shipping_settings['ancillary_endorsement'];
            }
            if (!empty($shipping_settings['special_services']) && $shipping_settings['special_services'] == 'Y') {
                $fedex_options['RequestedShipment']['SmartPostDetail']['SpecialServices'] = 'USPS_DELIVERY_CONFIRMATION';
            }
            $fedex_options['RequestedShipment']['SmartPostDetail']['HubId'] = $shipping_settings['hub_id'];
            if (!empty($shipping_settings['customer_manifest_id'])) {
                $fedex_options['RequestedShipment']['SmartPostDetail']['CustomerManifestId'] = $shipping_settings['customer_manifest_id'];
            }
        }

        $fedex_options['WebAuthenticationDetail']['UserCredential']['Key'] = !empty($shipping_settings['user_key']) ? $shipping_settings['user_key'] : '';
        $fedex_options['WebAuthenticationDetail']['UserCredential']['Password'] = !empty($shipping_settings['user_key_password']) ? $shipping_settings['user_key_password'] : '';
        $fedex_options['ClientDetail']['AccountNumber'] = !empty($shipping_settings['account_number']) ? $shipping_settings['account_number'] : '';
        $fedex_options['ClientDetail']['MeterNumber'] = !empty($shipping_settings['meter_number']) ? $shipping_settings['meter_number'] : '';

        $fedex_options['PackagingType'] = !empty($shipping_settings['package_type']) ? $shipping_settings['package_type'] : '';
        $fedex_options['DropoffType'] = !empty($shipping_settings['drop_off_type']) ? $shipping_settings['drop_off_type'] : '';

        $fedex_options['Shipper'] = $fedex_options['Recipient'] = array();
        $fedex_options = $this->_prepareAddress($fedex_options, $this->_shipping_info['package_info']['origination'], 'Shipper');
        $fedex_options = $this->_prepareAddress($fedex_options, $this->_shipping_info['package_info']['location'], 'Recipient', $code);

        $fedex_options['Packages'] = $this->_buildPackages($weight_data, $shipping_settings, $this->_shipping_info['package_info']);

        $url = 'https://ws' . (!empty($shipping_settings['test_mode']) && $shipping_settings['test_mode'] == 'Y' ? 'beta' : '') .'.fedex.com:443/web-services';

        $xml_req = $this->_formatXml($fedex_options);

        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => $xml_req,
            'headers' => array(
                'Content-type: text/xml'
            )
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
        $response = Http::post($data['url'], $data['data'], array('headers' => 'Content-type: text/xml'));

        return $response;
    }
}
