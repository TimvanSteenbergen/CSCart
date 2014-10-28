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
class Ups implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

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
     * Gets shipping service rate
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return float  Shipping service rate of false of rate was not found
     */
    public function processRates($response)
    {
        $xml = @simplexml_load_string($response);
        $return = array();

        if (!empty($xml)) {
            $responseStatusCode = (string) $xml->Response->ResponseStatusCode;

            foreach ($xml->RatedShipment as $shipment) {
                $total_charge = 0;
                $service_code = (string) $shipment->Service->Code;

                // Try to get negotiated rates
                if (!empty($shipment->NegotiatedRates)) {
                    $total_charge = (string) $shipment->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                }

                if (empty($total_charge)) {
                    $total_charge = (string) $shipment->TotalCharges->MonetaryValue;
                }

                if (!($service_code && $total_charge)) {
                    continue;
                }

                if (!empty($total_charge)) {
                    $return[$service_code] = array(
                        'rate' => $total_charge
                    );

                    if (!empty($shipment->ScheduledDeliveryTime)) {
                        $return[$service_code]['delivery_time'] = (string) $shipment->ScheduledDeliveryTime;

                    } elseif (!empty($shipment->GuaranteedDaysToDelivery)) {
                        $return[$service_code]['delivery_time'] = (string) $shipment->GuaranteedDaysToDelivery;
                    }
                }
            }
        }

        return $return;
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
        $rates = $this->processRates($response);

        if (isset($rates[$code])) {
            $return['cost'] = $rates[$code]['rate'];

            if (isset($rates[$code]['delivery_time'])) {
                $return['delivery_time'] = $rates[$code]['delivery_time'];
            }
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
    public function processErrors($response)
    {
        // Parse XML message returned by the UPS post server.
        $xml = @simplexml_load_string($response);
        $return = '';

        if (!empty($xml)) {
            $status_code = (string) $xml->Response->ResponseStatusCode;

            if ($status_code != '1') {
                $return = (string) $xml->Response->Error->ErrorDescription;
                if (!empty($xml->Response->Error->ErrorDigest)) {
                    $return .= ' (' . (string) $xml->Response->Error->ErrorDigest . ').';
                }

                return $return;
            }
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
        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];

        if (!empty($shipping_settings['test_mode']) && $shipping_settings['test_mode'] == 'Y') {
            $url = "https://wwwcie.ups.com:443/ups.app/xml/Rate";
        } else {
            $url = "https://onlinetools.ups.com:443/ups.app/xml/Rate";
        }

        // Prepare data for UPS request
        $username = !empty($shipping_settings['username']) ? htmlspecialchars($shipping_settings['username']) : '';
        $password = !empty($shipping_settings['password']) ? htmlspecialchars($shipping_settings['password']) : '';
        $access_key = !empty($shipping_settings['access_key']) ? htmlspecialchars($shipping_settings['access_key']) : '';

        // Get shipper settings
        $shipper = '';
        $shipper_rate_information = '';
        if (isset($shipping_settings['negotiated_rates']) && $shipping_settings['negotiated_rates'] == 'Y') {
            $shipper = '<ShipperNumber>' . $shipping_settings['shipper_number'] . '</ShipperNumber>';
            $shipper_rate_information = '<RateInformation><NegotiatedRatesIndicator/></RateInformation>';
        }

        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        $origination_postal = $origination['zipcode'];
        $origination_country = $origination['country'];
        $origination_state = $origination['state'];

        $destination_postal = $location['zipcode'];
        $destination_country = $location['country'];
        $destination_state = $location['state'];

        $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : '0';
        $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : '0';
        $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : '0';

        $pickup_type = !empty($shipping_settings['pickup_type']) ? $shipping_settings['pickup_type'] : '';
        $package_type = !empty($shipping_settings['package_type']) ? $shipping_settings['package_type'] : '';

        // define weight unit and value
        $weight = $weight_data['full_pounds'];

        if (in_array($origination_country, array('US', 'DO','PR'))) {
            $weight_unit = 'LBS';
            $measure_unit = 'IN';
        } else {
            $weight_unit = 'KGS';
            $measure_unit = 'CM';
            $weight = $weight * 0.4536;
        }

        $customer_classification = '';
        if ($origination_country == 'US' && $pickup_type == '11') {
            $customer_classification=<<<EOT
        <CustomerClassification>
            <Code>04</Code>
        </CustomerClassification>
EOT;
        }

    if (empty($this->_shipping_info['package_info']['packages'])) {
    $packages =<<<EOT
        <Package>
            <PackagingType>
                <Code>$package_type</Code>
            </PackagingType>
                <Dimensions>
                    <UnitOfMeasurement>
                    <Code>$measure_unit</Code>
                    </UnitOfMeasurement>
                    <Length>$length</Length>
                    <Width>$width</Width>
                    <Height>$height</Height>
                </Dimensions>
            <PackageWeight>
                <UnitOfMeasurement>
                    <Code>$weight_unit</Code>
                </UnitOfMeasurement>
                <Weight>$weight</Weight>
            </PackageWeight>
        </Package>
EOT;
    } else {
        $packages = '';
        foreach ($this->_shipping_info['package_info']['packages'] as $package) {
            $package_length = empty($package['shipping_params']['box_length']) ? $length : $package['shipping_params']['box_length'];
            $package_width = empty($package['shipping_params']['box_width']) ? $width : $package['shipping_params']['box_width'];
            $package_height = empty($package['shipping_params']['box_height']) ? $height : $package['shipping_params']['box_height'];
            $weight_ar = fn_expand_weight($package['weight']);
            $weight = (!in_array($origination_country, array('US', 'DO','PR'))) ? ($weight_ar['full_pounds'] * 0.4536) : $weight_ar['full_pounds'];

    $packages .=<<<EOT
        <Package>
            <PackagingType>
                <Code>$package_type</Code>
            </PackagingType>
                <Dimensions>
                    <UnitOfMeasurement>
                    <Code>$measure_unit</Code>
                    </UnitOfMeasurement>
                    <Length>$package_length</Length>
                    <Width>$package_width</Width>
                    <Height>$package_height</Height>
                </Dimensions>
            <PackageWeight>
                <UnitOfMeasurement>
                    <Code>$weight_unit</Code>
                </UnitOfMeasurement>
                <Weight>$weight</Weight>
            </PackageWeight>
        </Package>
EOT;
        }
    }

    // Get the customer additional address
    $additional_address = '';
    if (!empty($location['address'])) {
        $additional_address .= '<AddressLine1>' . htmlspecialchars($location['address']) . '</AddressLine1>';
        if (!empty($location['address_2'])) {
            $additional_address .= "\n<AddressLine2>" . htmlspecialchars($location['address_2']) . '</AddressLine2>';
        }
    }

    $request=<<<EOT
    <?xml version="1.0"?>
    <AccessRequest xml:lang="en-US">
        <AccessLicenseNumber>$access_key</AccessLicenseNumber>
            <UserId>$username</UserId>
            <Password>$password</Password>
    </AccessRequest>
    <?xml version="1.0"?>
    <RatingServiceSelectionRequest xml:lang="en-US">
      <Request>
        <TransactionReference>
          <CustomerContext>Rate Request</CustomerContext>
          <XpciVersion>1.0</XpciVersion>
        </TransactionReference>
        <RequestAction>Rate</RequestAction>
        <RequestOption>shop</RequestOption>
      </Request>
        <PickupType>
        <Code>$pickup_type</Code>
      </PickupType>
      $customer_classification
      <Shipment>
        <Shipper>
            <Address>
                <PostalCode>$origination_postal</PostalCode>
                <CountryCode>$origination_country</CountryCode>
            </Address>
            $shipper
        </Shipper>
        <ShipTo>
            <Address>
                <StateProvinceCode>$destination_state</StateProvinceCode>
                <PostalCode>$destination_postal</PostalCode>
                <CountryCode>$destination_country</CountryCode>
                $additional_address
                <ResidentialAddressIndicator/>
            </Address>
        </ShipTo>
        <ShipFrom>
            <Address>
                <StateProvinceCode>$origination_state</StateProvinceCode>
                <PostalCode>$origination_postal</PostalCode>
                <CountryCode>$origination_country</CountryCode>
                <ResidentialAddressIndicator/>
            </Address>
        </ShipFrom>
        $packages
        $shipper_rate_information
      </Shipment>
    </RatingServiceSelectionRequest>
EOT;

        $request_data = array(
            'method' => 'post',
            'url' => $url,
            'data' => $request,
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
