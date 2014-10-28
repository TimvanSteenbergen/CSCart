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

/**
 * UPS shipping service
 */
class Swisspost implements IService
{
    const ZONE_5 = 5;
    const ZONE_2 = 2;

    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = false;

    private function _getZones($zone)
    {
        static $zones = array (
            self::ZONE_5 => array ('AC' => '4', 'AD' => '2', 'AE' => '4', 'AF' => '4', 'AG' => '5', 'AI' => '5', 'AL' => '2', 'AM' => '4', 'AN' => '5', 'AO' => '4', 'AR' => '5', 'AT' => '1', 'AU' => '5', 'AW' => '5', 'AZ' => '4', 'BA' => '2', 'BB' => '5', 'BD' => '4', 'BE' => '1', 'BF' => '4', 'BG' => '2', 'BH' => '4', 'BI' => '4', 'BJ' => '4', 'BM' => '5', 'BN' => '5', 'BO' => '5', 'BR' => '5', 'BS' => '5', 'BT' => '4', 'BW' => '4', 'BY' => '2', 'BZ' => '5', 'CA' => '3', 'CD' => '4', 'CF' => '4', 'CG' => '4', 'CI' => '4', 'CK' => '5', 'CL' => '5', 'CM' => '4', 'CN' => '4', 'CO' => '5', 'CR' => '5', 'CU' => '5', 'CV' => '4', 'CX' => '5', 'CY' => '2', 'CZ' => '2', 'DE' => '1', 'DJ' => '4', 'DK' => '2', 'DM' => '5', 'DO' => '5', 'DZ' => '3', 'EC' => '5', 'EE' => '2', 'EG' => '3', 'ER' => '4', 'ES' => '2', 'ET' => '4', 'FI' => '2', 'FJ' => '5', 'FK' => '5', 'FO' => '2', 'FR' => '1', 'GA' => '4', 'GB' => '2', 'GD' => '5', 'GE' => '4', 'GF' => '5', 'GG' => '2', 'GH' => '4', 'GI' => '2', 'GL' => '2', 'GM' => '4', 'GN' => '4', 'GP' => '5', 'GQ' => '4', 'GR' => '2', 'GT' => '5', 'GW' => '4', 'GY' => '5', 'HK' => '4', 'HN' => '5', 'HR' => '2', 'HT' => '5', 'HU' => '2', 'ID' => '5', 'IE' => '2', 'IL' => '3', 'IM' => '2', 'IN' => '4', 'IQ' => '4', 'IR' => '4', 'IS' => '2', 'IT' => '1', 'JE' => '2', 'JM' => '5', 'JO' => '3', 'JP' => '4', 'KE' => '4', 'KG' => '4', 'KH' => '4', 'KI' => '5', 'KM' => '4', 'KN' => '5', 'KP' => '4', 'KR' => '4', 'KW' => '4', 'KY' => '5', 'KZ' => '4', 'LA' => '4', 'LB' => '3', 'LC' => '5', 'LK' => '4', 'LR' => '4', 'LS' => '4', 'LT' => '2', 'LU' => '1', 'LV' => '2', 'LY' => '3', 'MA' => '3', 'MC' => '1', 'MD' => '2', 'ME' => '2', 'MG' => '4', 'MK' => '2', 'ML' => '4', 'MM' => '4', 'MN' => '4', 'MO' => '4', 'MQ' => '5', 'MR' => '4', 'MS' => '5', 'MT' => '2', 'MU' => '4', 'MV' => '4', 'MW' => '4', 'MX' => '3', 'MY' => '4', 'MZ' => '4', 'NA' => '4', 'NC' => '5', 'NE' => '4', 'NF' => '5', 'NG' => '4', 'NI' => '5', 'NL' => '1', 'NO' => '2', 'NP' => '4', 'NR' => '5', 'NZ' => '5', 'OM' => '4', 'PA' => '5', 'PE' => '5', 'PF' => '5', 'PG' => '5', 'PH' => '5', 'PK' => '4', 'PL' => '2', 'PM' => '3', 'PN' => '5', 'PT' => '2', 'PY' => '5', 'QA' => '4', 'RE' => '4', 'RO' => '2', 'RS' => '2', 'RU' => '2', 'RW' => '4', 'SA' => '4', 'SB' => '5', 'SC' => '4', 'SD' => '4', 'SE' => '2', 'SG' => '4', 'SH' => '4', 'SI' => '2', 'SK' => '2', 'SL' => '4', 'SM' => '1', 'SN' => '4', 'SO' => '4', 'SR' => '5', 'ST' => '4', 'SV' => '5', 'SY' => '3', 'SZ' => '4', 'TA' => '4', 'TC' => '5', 'TD' => '4', 'TG' => '4', 'TH' => '4', 'TJ' => '4', 'TL' => '5', 'TM' => '4', 'TN' => '3', 'TO' => '5', 'TR' => '2', 'TT' => '5', 'TV' => '5', 'TW' => '4', 'TZ' => '4', 'UA' => '2', 'UG' => '4', 'US' => '3', 'UY' => '5', 'UZ' => '4', 'VA' => '1', 'VC' => '5', 'VE' => '5', 'VI' => '5', 'VN' => '4', 'VU' => '5', 'WF' => '5', 'WS' => '5', 'YE' => '4', 'YT' => '4', 'ZA' => '4', 'ZM' => '4', 'ZW' => '4', ),
            self::ZONE_2 => array ('AC' => '2', 'AD' => '1', 'AE' => '2', 'AF' => '2', 'AG' => '2', 'AI' => '2', 'AL' => '1', 'AM' => '2', 'AN' => '2', 'AO' => '2', 'AR' => '2', 'AT' => '1', 'AU' => '2', 'AW' => '2', 'AZ' => '2', 'BA' => '1', 'BB' => '2', 'BD' => '2', 'BE' => '1', 'BF' => '2', 'BG' => '1', 'BH' => '2', 'BI' => '2', 'BJ' => '2', 'BM' => '2', 'BN' => '2', 'BO' => '2', 'BR' => '2', 'BS' => '2', 'BT' => '2', 'BW' => '2', 'BY' => '1', 'BZ' => '2', 'CA' => '2', 'CD' => '2', 'CF' => '2', 'CG' => '2', 'CI' => '2', 'CK' => '2', 'CL' => '2', 'CM' => '2', 'CN' => '2', 'CO' => '2', 'CR' => '2', 'CU' => '2', 'CV' => '2', 'CX' => '2', 'CY' => '2', 'CZ' => '1', 'DE' => '1', 'DJ' => '2', 'DK' => '1', 'DM' => '2', 'DO' => '2', 'DZ' => '2', 'EC' => '2', 'EE' => '1', 'EG' => '2', 'ER' => '2', 'ES' => '1', 'ET' => '2', 'FI' => '1', 'FJ' => '2', 'FK' => '2', 'FO' => '1', 'FR' => '1', 'GA' => '2', 'GB' => '1', 'GD' => '2', 'GE' => '2', 'GF' => '2', 'GG' => '2', 'GH' => '2', 'GI' => '1', 'GL' => '1', 'GM' => '2', 'GN' => '2', 'GP' => '2', 'GQ' => '2', 'GR' => '1', 'GT' => '2', 'GW' => '2', 'GY' => '1', 'HK' => '2', 'HN' => '2', 'HR' => '1', 'HT' => '2', 'HU' => '1', 'ID' => '2', 'IE' => '1', 'IL' => '2', 'IM' => '2', 'IN' => '2', 'IQ' => '2', 'IR' => '2', 'IS' => '1', 'IT' => '1', 'JE' => '2', 'JM' => '2', 'JO' => '2', 'JP' => '2', 'KE' => '2', 'KG' => '2', 'KH' => '2', 'KI' => '2', 'KM' => '2', 'KN' => '2', 'KP' => '2', 'KR' => '2', 'KW' => '2', 'KY' => '2', 'KZ' => '2', 'LA' => '2', 'LB' => '2', 'LC' => '2', 'LK' => '2', 'LR' => '2', 'LS' => '2', 'LT' => '1', 'LU' => '1', 'LV' => '1', 'LY' => '2', 'MA' => '2', 'MC' => '1', 'MD' => '1', 'ME' => '1', 'MG' => '2', 'MK' => '1', 'ML' => '2', 'MM' => '2', 'MN' => '2', 'MO' => '2', 'MQ' => '2', 'MR' => '2', 'MS' => '2', 'MT' => '1', 'MU' => '2', 'MV' => '2', 'MW' => '2', 'MX' => '2', 'MY' => '2', 'MZ' => '2', 'NA' => '2', 'NC' => '2', 'NE' => '2', 'NF' => '2', 'NG' => '2', 'NI' => '2', 'NL' => '1', 'NO' => '1', 'NP' => '2', 'NR' => '2', 'NZ' => '2', 'OM' => '2', 'PA' => '2', 'PE' => '2', 'PF' => '2', 'PG' => '2', 'PH' => '2', 'PK' => '2', 'PL' => '1', 'PM' => '2', 'PN' => '2', 'PT' => '1', 'PY' => '2', 'QA' => '2', 'RE' => '2', 'RO' => '1', 'RS' => '2', 'RU' => '1', 'RW' => '2', 'SA' => '2', 'SB' => '2', 'SC' => '2', 'SD' => '2', 'SE' => '1', 'SG' => '2', 'SH' => '2', 'SI' => '1', 'SK' => '1', 'SL' => '2', 'SM' => '1', 'SN' => '2', 'SO' => '2', 'SR' => '2', 'ST' => '2', 'SV' => '2', 'SY' => '2', 'SZ' => '2', 'TA' => '2', 'TC' => '2', 'TD' => '2', 'TG' => '2', 'TH' => '2', 'TJ' => '2', 'TL' => '2', 'TM' => '2', 'TN' => '2', 'TO' => '2', 'TR' => '1', 'TT' => '2', 'TV' => '2', 'TW' => '2', 'TZ' => '2', 'UA' => '1', 'UG' => '2', 'US' => '2', 'UY' => '2', 'UZ' => '2', 'VA' => '1', 'VC' => '2', 'VE' => '2', 'VI' => '2', 'VN' => '2', 'VU' => '2', 'WF' => '2', 'WS' => '2', 'YE' => '2', 'YT' => '2', 'ZA' => '2', 'ZM' => '2', 'ZW' => '2')
        );

        return $zones[$zone];
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
        return $response;
    }

    /**
     * Gets error message from shipping service server
     *
     * @param  string $resonse Reponse from Shipping service server
     * @return string Text of error or false if no errors
     */
    public function processErrors($response)
    {
        return null;
    }

    /**
     * Prepare request information
     *
     * @return null Always null (method not allowed)
     */
    public function getRequestData()
    {
        return null;
    }

    /**
     * Process simple request to shipping service server
     *
     * @return string Server response
     */
    public function getSimpleRates()
    {
        $return = array(
            'cost' => false,
            'error' => false,
        );

        $weight_data = fn_expand_weight($this->_shipping_info['package_info']['W']);
        $shipping_settings = $this->_shipping_info['service_params'];
        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];
        $code = $this->_shipping_info['service_code'];

        $path = Registry::get('config.dir.root') . '/app/Tygh/Shippings/Services/swisspost/' . $code;

        if (!empty($location['country']) && file_exists($path) == true) {
            $local_countries = array('CH', 'LI');

            if (in_array($location['country'], $local_countries) && strpos($code, 'int') !== false) {
                $return['error'] = __('ship_swisspost_error_private_delivery');

                return $return;

            } elseif (!in_array($location['country'], $local_countries) && strpos($code, 'prv') !== false) {
                $return['error'] = __('ship_swisspost_error_intl_delivery');

                return $return;
            }

            $delimiter = ';';
            $max_line_size = 65536 * 3;

            $fp = fopen($path, "r", true);

            $export_scheme = fgetcsv ($fp, $max_line_size, $delimiter);

            $num = count($export_scheme);

            $tariff_array = (($num - 1) == 2) ? $this->_getZones(self::ZONE_2) : ((($num - 1) == 5) ? $this->_getZones(self::ZONE_5) : array());
            $zone = 'zone' . ((!empty($tariff_array[$location['country']])) ? $tariff_array[$location['country']] : (!empty($tariff_array) ? 5 : 1));

            foreach ($export_scheme as $k => $v) {
                if (empty($v)) {
                    unset($export_scheme[$k]);
                }
            }

            $price = 0;
            $weight = $weight_data['plain'] * Registry::get('settings.General.weight_symbol_grams') / 1000;

            while ($data = fgetcsv($fp, $max_line_size, $delimiter)) {
                for ($c=0; $c < $num; $c++) {
                    $rates_data[$export_scheme[$c]] = @$data[$c];
                }
                if ($weight <= $rates_data['weight']) {
                    $price = $rates_data[$zone];
                    break;
                }
            }
            fclose($fp);

            if ($price == 0) {
                $errors[] = __('ship_swisspost_heavy_package');
            } else {
                // additional services
                $add_path = Registry::get('config.dir.root') . '/app/Tygh/Shippings/Services/swisspost/additional_services.csv';

                if (file_exists($add_path) == true) {

                    $add_fp = fopen($add_path, "r", true);

                    $export_scheme = fgetcsv ($add_fp, $max_line_size, $delimiter);
                    $num = count($export_scheme);
                    // check service type
                    if (strpos($code, 'letter') > 0) {
                        $type = 'l_';
                    } elseif (strpos($code, 'postpac') > 0) {
                        if (strpos($code, 'nt') > 0) {
                            $type = 'pp_';
                        } else {
                            $type = 'pc_';
                        }
                    } elseif (strpos($code, 'ur_goods') > 0) {
                        $type = 'ur_';
                    } elseif (strpos($code, 'bulky_goods') > 0) {
                        $type = 'pc_';
                    }

                    foreach ($export_scheme as $k => $v) {
                        if (empty($v)) {
                            unset($export_scheme[$k]);
                        }
                    }

                    while ($add_data = fgetcsv ($add_fp, $max_line_size, $delimiter)) {
                        for ($c=0; $c < $num; $c++) {
                            $add_rates_data[$export_scheme[$c]] = @$add_data[$c];
                        }

                        if (!empty($shipping_settings[$add_rates_data['service']]) && $shipping_settings[$add_rates_data['service']] == 'Y' && $add_rates_data['price'] > 0 && strpos($add_rates_data['service'], $type) === 0 && (($add_rates_data['service'] == 'pp_cash_on_delivery' && strpos($code, '_ec_') > 0) || ($add_rates_data['service'] != 'pp_cash_on_delivery'))) {
                            $price += $add_rates_data['price'];
                        }
                    }
                    fclose($add_fp);
                } else {
                    $errors[] = __('ship_swisspost_unable_to_open_additional_services');
                }
            }
        } else {
            $errors[] = __('ship_swisspost_unable_to_open_service', array('[code]' => $code));
        }

        if (!empty($price)) {
            $return['cost'] = $price;

        } else {
            $return['error'] = implode('. ', $errors);
        }

        return $return;
    }
}
