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
 * DHL shipping service
 */
class Dhl implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

    /**
     * Type of request: International or local ('IntlShipment', 'Shipment')
     *
     * @var string $_request_type
     */
    private $_request_type = '';

    /**
     * Description
     *
     * @return type
     */
    private function _getRates($response, $request_type)
    {
        // Parse XML message returned by the UPS post server.
        $doc = new \XMLDocument();
        $xp = new \XMLParser();
        $xp->setDocument($doc);
        $xp->parse($response);
        $doc = $xp->getDocument();
        $return = array();

        if (is_object($doc->root)) {
            $root = $doc->getRoot();
            $shipment = $root->getElementsByName($request_type);

            for ($i = 0; $i < count($shipment); $i++) {
                $_charge = $shipment[$i]->getValueByPath("/EstimateDetail/RateEstimate/TotalChargeEstimate");
                if (!empty($_charge)) {
                    $_c = trim($shipment[$i]->getValueByPath("/EstimateDetail/Service/Code"));
                    $_d = trim($shipment[$i]->getValueByPath("/EstimateDetail/ServiceLevelCommitment/Desc"));
                    if ($_c == 'E' && !empty($_d)) {
                        if ($_d == 'Delivery on Saturday') {
                            $_c .= ":SAT";
                        } elseif ($_d == 'Next business day by 10:30 A.M.') {
                            $_c .= ":1030";
                        }
                    }
                    $return[$_c] = array(
                        'rate' => trim($_charge),
                        'delivery_time' => $_d,
                    );
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

        $rates = $this->_getRates($response, $this->_request_type);

        if (!empty($rates[$this->_shipping_info['service_code']])) {
            $return['cost'] = $rates[$this->_shipping_info['service_code']]['rate'];

            if (isset($rates[$this->_shipping_info['service_code']]['delivery_time'])) {
                $return['delivery_time'] = $rates[$this->_shipping_info['service_code']]['delivery_time'];
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
        $request_type = $this->_request_type;

        $doc = new \XMLDocument();
        $xp = new \XMLParser();
        $xp->setDocument($doc);
        $xp->parse($response);
        $doc = $xp->getDocument();
        $return = array();

        if (is_object($doc->root)) {
            $root = $doc->getRoot();
            $shipments = $root->getElementsByName($request_type);
            if ($shipments) {
                for ($k = 0; $k < count($shipments); $k++) {
                    $faults = $shipments[$k]->getElementByName("Faults");
                    if (!empty($faults)) {
                        $fault = $faults->getElementsByName("Fault");
                        for ($i = 0; $i < count($fault); $i++) {
                            $return[] = $fault[$i]->getValueByPath("/Desc") . (($fault[$i]->getElementByName("Context")) ? (' ('. trim($fault[$i]->getValueByPath("/Context")) .')') : '');
                        }
                    }
                }
            }
        }

        return implode(' / ', $return);
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
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        $this->_request_type = ($location['country'] != $origination['country']) ? 'IntlShipment' : 'Shipment';

        $username = !empty($shipping_settings['system_id']) ? $shipping_settings['system_id'] : '';
        $password = !empty($shipping_settings['password']) ? $shipping_settings['password'] : '';
        $account = !empty($shipping_settings['account_number']) ? $shipping_settings['account_number'] : '';
        $ship_key = ($this->_request_type == 'Shipment') ? (!empty($shipping_settings['ship_key']) ? $shipping_settings['ship_key'] : '') : (!empty($shipping_settings['intl_ship_key']) ? $shipping_settings['intl_ship_key'] : '');

        if (!empty($shipping_settings['test_mode']) && $shipping_settings['test_mode'] == 'Y') {
            $url = 'https://ecommerce.airborne.com:443/apilandingtest.asp';
        } else {
            $url = 'https://ecommerce.airborne.com:443/apilanding.asp';
        }

        $weight = intval($weight_data['full_pounds']);
        $total = !empty($_SESSION['cart']['subtotal']) ? (intval($_SESSION['cart']['subtotal']) + 1) : 1;

        // Package type (Package, Letter)
        $package = !empty($shipping_settings['shipment_type']) ? $shipping_settings['shipment_type'] : '';

        // Ship date
        $ship_date = date("Y-m-d", TIME + (date('w', TIME) == 0 ? 86400 : 0));

        //Shipping Billing Type FIXME!!! move to options (S - sender, R - receiver, 3  - 3rd party)
        $billing_type = 'S';

        if ($this->_request_type == 'Shipment') {
            $all_codes = db_get_fields("SELECT code FROM ?:shipping_services WHERE code = ?s", $this->_shipping_info['service_code']);
        } else {
            $all_codes = array('IE', 'IE:SAT'); // DHL has the only international service Intl Express
        }

        $ship_request = $bil_request = '';

        // International shipping is not dutiable and have no customs fee
        $dutiable = '';
        if ($this->_request_type == 'IntlShipment') {
            $dutiable = "<Dutiable><DutiableFlag>N</DutiableFlag><CustomsValue>$total</CustomsValue></Dutiable>";
            $content = $origination['name'];
            $ship_request .= "<ContentDesc><![CDATA[$content]]></ContentDesc>"; // FIXME!!!
        }

        // Additional protection
        $protection = !empty($shipping_settings['additional_protection']) ? $shipping_settings['additional_protection'] : '';
        if ($protection != 'NR') {
            $ship_request .= "<AdditionalProtection><Code>$protection</Code><Value>$total</Value></AdditionalProtection>";
        }

        // Cache-on-delivery payment
        if (!empty($shipping_settings['cod_payment']) && $shipping_settings['cod_payment'] == 'Y') {
            $cod_method = !empty($shipping_settings['cod_method']) ? $shipping_settings['cod_method'] : '';
            $cod_value = !empty($shipping_settings['cod_value']) ? $shipping_settings['cod_value'] : '';
            $bil_request .= "<CODPayment><Code>$cod_method</Code><Value>$cod_value</Value></CODPayment>";
        }

        if ($package != 'L') {
            $length = !empty($shipping_settings['length']) ? $shipping_settings['length'] : '0';
            $width = !empty($shipping_settings['width']) ? $shipping_settings['width'] : '0';
            $height = !empty($shipping_settings['height']) ? $shipping_settings['height'] : '0';
            $ship_request .= "<Weight>$weight</Weight><Dimensions><Width>$width</Width><Height>$height</Height><Length>$length</Length></Dimensions>";

            if (!empty($package_info['packages'])) {
                $ship_request .= '<ShipmentPieces>';
                foreach ($this->_shipping_info['package_info']['packages'] as $k => $package_item) {
                    $package_length = empty($package_item['shipping_params']['box_length']) ? $length : $package_item['shipping_params']['box_length'];
                    $package_width = empty($package_item['shipping_params']['box_width']) ? $width : $package_item['shipping_params']['box_width'];
                    $package_height = empty($package_item['shipping_params']['box_height']) ? $height : $package_item['shipping_params']['box_height'];
                    $package_weight_ar = fn_expand_weight($package['weight']);
                    $package_weight = $package_weight_ar['full_pounds'];
                    $package_piecenum = $k + 1;

                    $ship_request .= "<Piece><PieceNum>$package_piecenum</PieceNum><Weight>$package_weight</Weight><Dimensions><Width>$package_width</Width><Height>$package_height</Height><Length>$package_length</Length></Dimensions></Piece>";
                }
                $ship_request .= '</ShipmentPieces>';
            }
        }

        $shipment_request = '';

        foreach ($all_codes as $c_code) {
            $_code = explode(':', $c_code);
            $service_code = $_code[0];

            $special_request = '';
            $shipment_instructions = '';

            // Ship hazardous materials
            if (!empty($shipping_settings['ship_hazardous']) && $shipping_settings['ship_hazardous'] == 'Y') {
                $special_request .= "<SpecialService><Code>HAZ</Code></SpecialService>";
            }

            if (!empty($_code[1])) {
                if ($_code[1] == 'SAT' && date('w', TIME) != '5') {
                    $shipment_instructions = "<ShipmentProcessingInstructions><Overrides><Override><Code>ES</Code></Override></Overrides></ShipmentProcessingInstructions>";
                }
                $special_request .= "<SpecialService><Code>$_code[1]</Code></SpecialService>";
            }

            // ZipCode override
            //$shipment_instructions = "<ShipmentProcessingInstructions><Overrides><Override><Code>RP</Code></Override></Overrides></ShipmentProcessingInstructions>";

            if (!empty($special_request)) {
                $special_request = '<SpecialServices>' . $special_request . '</SpecialServices>';
            }

            $shipment_request .= <<<EOT
    <$this->_request_type action="RateEstimate" version="1.0">
        <ShippingCredentials>
            <ShippingKey>$ship_key</ShippingKey>
            <AccountNbr>$account</AccountNbr>
        </ShippingCredentials>
        <ShipmentDetail>
            <ShipDate>$ship_date</ShipDate>
            <Service>
                <Code>$service_code</Code>
            </Service>
            <ShipmentType>
            <Code>$package</Code>
            </ShipmentType>
            $ship_request
            $special_request
        </ShipmentDetail>
        <Billing>
            <Party>
                <Code>$billing_type</Code>
            </Party>
            $bil_request
            <AccountNbr>$account</AccountNbr>
        </Billing>
        <Receiver>
            <Address>
                <Street>{$location['address']}</Street>
                <City>{$location['city']}</City>
                <State>{$location['state']}</State>
                <PostalCode>{$location['zipcode']}</PostalCode>
                <Country>{$location['country']}</Country>
            </Address>
        </Receiver>
        $dutiable
        $shipment_instructions
    </$this->_request_type>

EOT;
        }

        $request = <<<EOT
<?xml version="1.0" encoding="UTF-8" ?>
<eCommerce action="Request" version="1.1">
    <Requestor>
        <ID>$username</ID>
        <Password>$password</Password>
    </Requestor>
$shipment_request
</eCommerce>
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
