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
class Usps implements IService
{
    /**
     * Availability multithreading in this module
     *
     * @var array $_allow_multithreading
     */
    private $_allow_multithreading = true;

    /**
     * Flag to mark shipping as domestic/international
     *
     * @var boolean $_is_domestic
     */
    private $_is_domestic = true;

    /**
     * Gets Country name by 2-letter code
     *
     * @param  string $code 2-letter Country code
     * @return string Country name
     */
    private function _getCountry($code)
    {
        static $countries = array (
            'AD' => 'Andorra',
            'AE' => 'United Arab Emirates',
            'AF' => 'Afghanistan',
            'AG' => 'Antigua',
            'AI' => 'Anguilla',
            'AL' => 'Albania',
            'AM' => 'Armenia',
            'AN' => 'Netherlands Antilles',
            'AO' => 'Angola',
            'AR' => 'Argentina',
            'AS' => 'American Samoa',
            'AT' => 'Austria',
            'AU' => 'Australia',
            'AW' => 'Aruba',
            'AZ' => 'Azerbaijan',
            'BA' => 'Bosnia-Herzegovina',
            'BB' => 'Barbados',
            'BD' => 'Bangladesh',
            'BE' => 'Belgium',
            'BF' => 'Burkina Faso',
            'BG' => 'Bulgaria',
            'BH' => 'Bahrain',
            'BI' => 'Burundi',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BN' => 'Brunei Darussalam',
            'BO' => 'Bolivia',
            'BR' => 'Brazil',
            'BS' => 'Bahamas',
            'BT' => 'Bhutan',
            'BW' => 'Botswana',
            'BY' => 'Belarus',
            'BZ' => 'Belize',
            'CA' => 'Canada',
            'CC' => 'Cocos Island',
            'CF' => 'Central African Republic',
            'CG' => 'Congo, Democratic Republic of the',
            'CH' => 'Switzerland',
            'CI' => 'Cote d\'Ivoire',
            'CK' => 'Cook Islands',
            'CL' => 'Chile',
            'CM' => 'Cameroon',
            'CN' => 'China',
            'CO' => 'Colombia',
            'CR' => 'Costa Rica',
            'CU' => 'Cuba',
            'CV' => 'Cape Verde',
            'CX' => 'Christmas Island',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DE' => 'Germany',
            'DJ' => 'Djibouti',
            'DK' => 'Denmark',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'DZ' => 'Algeria',
            'EC' => 'Ecuador',
            'EE' => 'Estonia',
            'EG' => 'Egypt',
            'ER' => 'Eritrea',
            'ES' => 'Spain',
            'ET' => 'Ethiopia',
            'FI' => 'Finland',
            'FJ' => 'Fiji',
            'FK' => 'Falkland Islands',
            'FM' => 'Micronesia, Federated States of',
            'FO' => 'Faroe Islands',
            'FR' => 'France',
            'GA' => 'Gabon',
            'GB' => 'Great Britain and Northern Ireland',
            'GD' => 'Grenada',
            'GE' => 'Georgia, Republic of',
            'GF' => 'French Guiana',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GL' => 'Greenland',
            'GM' => 'Gambia',
            'GN' => 'Guinea',
            'GP' => 'Guadeloupe',
            'GQ' => 'Equatorial Guinea',
            'GR' => 'Greece',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GU' => 'Guam',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HK' => 'Hong Kong',
            'HN' => 'Honduras',
            'HR' => 'Croatia',
            'HT' => 'Haiti',
            'HU' => 'Hungary',
            'ID' => 'Indonesia',
            'IE' => 'Ireland',
            'IL' => 'Israel',
            'IN' => 'India',
            'IQ' => 'Iraq',
            'IR' => 'Iran',
            'IM' => 'Isle of Man',
            'IS' => 'Iceland',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JO' => 'Jordan',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'KE' => 'Kenya',
            'KG' => 'Kyrgyzstan',
            'KH' => 'Cambodia',
            'KI' => 'Kiribati',
            'KM' => 'Comoros',
            'KN' => 'Saint Christopher and Nevis',
            'KP' => 'Korea, Democratic People\'s Republic of',
            'KR' => 'Korea, Republic of (South Korea)',
            'KW' => 'Kuwait',
            'KY' => 'Cayman Islands',
            'KZ' => 'Kazakhstan',
            'LA' => 'Laos',
            'LB' => 'Lebanon',
            'LC' => 'Saint Lucia',
            'LI' => 'Liechtenstein',
            'LK' => 'Sri Lanka',
            'LR' => 'Liberia',
            'LS' => 'Lesotho',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'LV' => 'Latvia',
            'LY' => 'Libya',
            'MA' => 'Morocco',
            'MC' => 'Monaco',
            'MD' => 'Moldova',
            'MG' => 'Madagascar',
            'MH' => 'Marshall Islands',
            'MK' => 'Macedonia',
            'ML' => 'Mali',
            'MM' => 'Burma',
            'MN' => 'Mongolia',
            'MO' => 'Macao',
            'MP' => 'Northern Mariana Islands, Commonwealth',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MS' => 'Montserrat',
            'MT' => 'Malta',
            'MU' => 'Mauritius',
            'MV' => 'Maldives',
            'MW' => 'Malawi',
            'MX' => 'Mexico',
            'MY' => 'Malaysia',
            'MZ' => 'Mozambique',
            'NA' => 'Namibia',
            'NC' => 'New Caledonia',
            'NE' => 'Niger',
            'NF' => 'Norfolk Island',
            'NG' => 'Nigeria',
            'NI' => 'Nicaragua',
            'NL' => 'Netherlands',
            'NO' => 'Norway',
            'NP' => 'Nepal',
            'NR' => 'Nauru',
            'NU' => 'Niue',
            'NZ' => 'New Zealand',
            'OM' => 'Oman',
            'PA' => 'Panama',
            'PE' => 'Peru',
            'PF' => 'French Polynesia',
            'PG' => 'Papua New Guinea',
            'PH' => 'Philippines',
            'PK' => 'Pakistan',
            'PL' => 'Poland',
            'PM' => 'Saint Pierre and Miquelon',
            'PN' => 'Pitcairn Island',
            'PR' => 'Puerto Rico',
            'PT' => 'Portugal',
            'PW' => 'Palau',
            'PY' => 'Paraguay',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russia',
            'RW' => 'Rwanda',
            'SA' => 'Saudi Arabia',
            'SB' => 'Solomon Islands',
            'SC' => 'Seychelles',
            'SD' => 'Sudan',
            'SE' => 'Sweden',
            'SG' => 'Singapore',
            'SH' => 'Saint Helena',
            'SI' => 'Slovenia',
            'SK' => 'Slovak Republic',
            'SL' => 'Sierra Leone',
            'SM' => 'San Marino',
            'SN' => 'Senegal',
            'SO' => 'Somalia',
            'SR' => 'Suriname',
            'ST' => 'Sao Tome and Principe',
            'SV' => 'El Salvador',
            'SY' => 'Syrian Arab Republic',
            'SZ' => 'Swaziland',
            'TC' => 'Turks and Caicos Islands',
            'TD' => 'Chad',
            'TG' => 'Togo',
            'TH' => 'Thailand',
            'TJ' => 'Tajikistan',
            'TK' => 'Tokelau (Union) Group',
            'TM' => 'Turkmenistan',
            'TN' => 'Tunisia',
            'TO' => 'Tonga',
            'TP' => 'East Timor',
            'TR' => 'Turkey',
            'TT' => 'Trinidad and Tobago',
            'TV' => 'Tuvalu',
            'TW' => 'Taiwan',
            'TZ' => 'Tanzania',
            'UA' => 'Ukraine',
            'UG' => 'Uganda',
            'UK' => 'United Kingdom',
            'US' => 'United States',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VA' => 'Vatican City',
            'VC' => 'Saint Vincent and the Grenadines',
            'VE' => 'Venezuela',
            'VG' => 'British Virgin Islands',
            'VI' => 'Virgin Islands U.S.',
            'VN' => 'Vietnam',
            'VU' => 'Vanuatu',
            'WF' => 'Wallis and Futuna Islands',
            'WS' => 'Western Samoa',
            'YE' => 'Yemen',
            'YT' => 'Mayotte',
            'YU' => 'Yugoslavia',
            'ZA' => 'South Africa',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe'
        );

        return $countries[$code];
    }

    /**
     * Converts service name to needed format
     *
     * @param  string $service_name Shipping service name (E.g.: First Class Mail <i>tm</i>)
     * @return string Service name in upper case without extra symbols (E.g.: FIRST CLASS MAIL)
     */
    private function _prepareServiceName($service_name)
    {
        // Decode HTML entities and remove any text between the tags
        $service_name = preg_replace('/<.+?>.*?<\/.+?>/i', '', html_entity_decode($service_name));
        // Remove any symbols except the letters and space symbol
        $service_name = strtoupper(str_replace('-', ' ', $service_name));
        $service_name = preg_replace('/[^A-Z ]/i', '', html_entity_decode($service_name));

        return $service_name;
    }

    /**
     * Gets Extra services like: insurance, drop off, etc
     *
     * @return array List of services
     */
    private function _getExtraServices()
    {
        $extra_services = array(
            'domestic' => array(
                0 => 'domestic_service_certified',
                1 => 'domestic_service_insurance',
                3 => 'domestic_service_restricted_delivery',
                4 => 'domestic_service_registered_without_insurance',
                5 => 'domestic_service_registered_with_insurance',
                6 => 'domestic_service_collect_on_delivery',
                7 => 'domestic_service_return_receipt_for_merchandise',
                8 => 'domestic_service_return_receipt',
                9 => 'domestic_service_certificate_of_mailing_per_individual_article',
                10 => 'domestic_service_certificate_of_mailing_for_firm_mailing_books',
                11 => 'domestic_service_express_mail_insurance',
                13 => 'domestic_service_delivery_confirmation',
                15 => 'domestic_service_signature_confirmation',
                16 => 'domestic_service_return_receipt_electronic',

            ),
            'intl' => array(
                0 => 'intl_service_registered_mail',
                1 => 'intl_service_insurance',
                2 => 'intl_service_return_receipt',
                5 => 'intl_service_pick_up_on_demand',
                6 => 'intl_service_certificate_of_mailing',
                9 => 'intl_service_edelivery_confirmation',
            ),
        );

        return $extra_services;
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
        $code = $this->_prepareServiceName($code);

        $rates = $this->processRates($response, $this->_is_domestic, $this->_shipping_info['service_params']);

        if (isset($rates[$code])) {
            $return['cost'] = $rates[$code];
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
        $xml = @simplexml_load_string($response);
        $return = array();

        if (!empty($xml)) {
            if ($xml->getName() == 'Error') {
                $return[] = (string) $xml->Description;

            } elseif ($xml->Error) {
                $return[] = (string) $xml->Error->Description;
            } else {
                $packages = $xml->Package;
                for ($i = 0; $i < count($packages); $i++) {
                    if ($packages[$i]->Error) {
                        $return[] = (string) $packages[$i]->Error->Description;
                    }
                }
            }

            return implode(' / ', $return);
        }

        return false;
    }

    /**
     * Gets shipping service rate
     *
     * @param  string $resonse           Reponse from Shipping service server
     * @param  bool   $is_domestic       Flag of domestic delivery
     * @param  array  $shipping_settings
     * @return float  Shipping service rate of false of rate was not found
     */
    public function processRates($response, $is_domestic, $shipping_settings)
    {
        $xml = @simplexml_load_string($response);
        $return = array();

        $extra_services = $this->_getExtraServices();

        if (!empty($xml)) {
            if ($is_domestic == true) {
                $shipment = $xml->Package;
            } else {
                $shipment = $xml->Package;
                if (!empty($shipment)) {
                    $shipment = $shipment->Service;
                } else {
                    return false;
                }
            }

            for ($i = 0; $i < count($shipment); $i++) {
                $service_name = '';
                if ($is_domestic == true) {
                    if ($shipment[$i]->Postage) {
                        $service_name = (string) $shipment[$i]->Postage->MailService;
                        $service_name = $this->_prepareServiceName($service_name);

                        if ((string) $shipment[$i]->Postage->Rate == '0.00') {
                            $rate = (string) $shipment[$i]->Postage->CommercialRate;
                        } else {
                            $rate = (string) $shipment[$i]->Postage->Rate;
                        }
                        $services = $shipment[$i]->Postage->SpecialServices;

                        if (count($services) > 0) {
                            foreach ($services->SpecialService as $service) {

                                $availability = (string) $service->Available;
                                if (strtoupper($availability) == 'TRUE') {
                                    $service_id = (string) $service->ServiceID;

                                    if (isset($shipping_settings[$extra_services['domestic'][$service_id]]) && $shipping_settings[$extra_services['domestic'][$service_id]] == 'Y') {
                                        $rate += floatval((string) $service->Price);
                                    }

                                }
                            }
                        }

                        if (floatval($rate)) {
                            $is_machinable = (string) $shipment[$i]->Machinable;
                            if ($service_name == 'STANDARD POST') {
                                $service_name .= ($is_machinable == 'TRUE' ? ' M' : ' N');
                            } elseif (strpos($service_name, 'PRIORITY MAIL EXPRESS') !== false) {
                                $service_name = 'PRIORITY MAIL EXPRESS';
                            } elseif (strpos($service_name, 'PRIORITY MAIL REGIONAL RATE') !== false) {
                                $service_name = 'PRIORITY MAIL REGIONAL RATE';
                            } elseif (strpos($service_name, 'PRIORITY MAIL') !== false) {
                                $service_name = 'PRIORITY MAIL';
                            } elseif (strpos($service_name, 'FIRST CLASS MAIL') !== false) {
                                $service_name = 'FIRST CLASS MAIL';
                            }
                        }
                    }
                } else {
                    if ($shipment[$i]->Postage) {
                        $service_name = (string) $shipment[$i]->SvcDescription;

                        $service_name = $this->_prepareServiceName($service_name);

                        $rate = (string) $shipment[$i]->Postage;
                        $services = $shipment[$i]->ExtraServices;

                        if (count($services) > 0) {
                            foreach ($services->ExtraService as $service) {
                                $availability = (string) $service->Available;
                                if (strtoupper($availability) == 'TRUE') {
                                    $service_id = (string) $service->ServiceID;

                                    if (isset($shipping_settings[$extra_services['intl'][$service_id]]) && $shipping_settings[$extra_services['intl'][$service_id]] == 'Y') {
                                        $rate += floatval((string) $service->Price);
                                    }

                                }
                            }
                        }
                    }
                }

                if (empty($service_name)) {
                    continue;
                }

                $return[$service_name] = $rate;
            }

            return $return;
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
        $package_cost = $this->_shipping_info['package_info']['C'];

        $shipping_settings = $this->_shipping_info['service_params'];

        if (!empty($shipping_settings['test_mode']) && $shipping_settings['test_mode'] == 'Y') {
            $url = 'http://testing.shippingapis.com/ShippingAPI.dll';
        } else {
            $url = 'http://production.shippingapis.com/ShippingAPI.dll';
        }

        $username = !empty($shipping_settings['username']) ? $shipping_settings['username'] : '';

        $machinable = !empty($shipping_settings['machinable']) ? $shipping_settings['machinable'] : '';
        $container_priority = !empty($shipping_settings['container_priority']) ? strtoupper($shipping_settings['container_priority']) : '';
        $container_express = !empty($shipping_settings['container_express']) ? $shipping_settings['container_express'] : '';
        $mailtype = !empty($shipping_settings['mailtype']) ? $shipping_settings['mailtype'] : '';

        $package_size = !empty($shipping_settings['package_size']) ? $shipping_settings['package_size'] : '';
        $first_class_mail_type = !empty($shipping_settings['first_class_mail_type']) ? $shipping_settings['first_class_mail_type'] : '';

        $pounds = $weight_data['pounds'];
        $ounces = $weight_data['ounces'];

        $origination = $this->_shipping_info['package_info']['origination'];
        $location = $this->_shipping_info['package_info']['location'];

        // The zip code should be in 5 digit format so we cut all digits after "-"
        $origination_postal = preg_replace('/-\d*/i', '', trim($origination['zipcode']));
        $destination_postal = preg_replace('/-\d*/i', '', trim($location['zipcode']));

        $origination_country = $origination['country'];
        $destination_country = $location['country'];

        $size_parameters = '';
        if ($package_size == 'Large') {
            $_width = !empty($shipping_settings['priority_width']) ? $shipping_settings['priority_width'] : '0';
            $_length = !empty($shipping_settings['priority_length']) ? $shipping_settings['priority_length'] : '0';
            $_height = !empty($shipping_settings['priority_height']) ? $shipping_settings['priority_height'] : '0';
            $size_parameters = <<<EOT
                <Width>$_width</Width>
                <Length>$_length</Length>
                <Height>$_height</Height>
EOT;
            if ($container_priority == 'NONRECTANGULAR') {
                $_priority_girth = !empty($shipping_settings['priority_girth']) ? $shipping_settings['username'] : '';
                $size_parameters .= "<Girth>$_priority_girth</Girth>";
            }

        }


        $us_dependent_territories = array(
            'AS', // American Samoa
            'VI', // U.S. Virgin Islands
            'PR', // Puerto Rico
            'GU', // Guam
            'MP', // Northern Mariana Islands
            'FM', //Micronesia
            'MH', //Marshall Islands,
            'PW' //Palau
        );
        if (in_array($destination_country, $us_dependent_territories)) {
            $destination_country = 'US';
        }

        $ground_only = !empty($shipping_settings['ground_only']) && $shipping_settings['ground_only'] == 'Y' ? "<GroundOnly>true</GroundOnly>\n" : '';

        if ($origination_country == $destination_country) {
            $extra_services = $this->_getExtraServices();
            $_services = array();

            foreach ($shipping_settings as $service_id => $enabled) {
                if (array_search($service_id, $extra_services['domestic']) !== false && $enabled == 'Y') {
                    $_services[] = '<SpecialService>' . array_search($service_id, $extra_services['domestic']) . '</SpecialService>';
                }
            }

            if (!empty($_services)) {
                $_services = '<SpecialServices>' . implode("\n", $_services) . '</SpecialServices>';
            } else {
                $_services = '';
            }

            // Domestic rate calculation
            $query=<<<EOT
            <RateV4Request USERID="$username">
              <Revision>2</Revision>
              <Package ID="0">
                <Service>EXPRESS</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container>$container_express</Container>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
              </Package>
              <Package ID="1">
                <Service>FIRST CLASS</Service>
                <FirstClassMailType>$first_class_mail_type</FirstClassMailType>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container/>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
                <Machinable>$machinable</Machinable>
              </Package>
              <Package ID="2">
                <Service>PRIORITY</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container>$container_priority</Container>
                <Size>$package_size</Size>
                $size_parameters
                <Value>$package_cost</Value>
                $_services
                $ground_only
              </Package>
              <Package ID="3">
                <Service>PARCEL</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container/>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
                <Machinable>$machinable</Machinable>
              </Package>
              <Package ID="4">
                <Service>BPM</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container/>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
              </Package>
              <Package ID="5">
                <Service>LIBRARY</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container/>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
              </Package>
              <Package ID="6">
                <Service>MEDIA</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container/>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
              </Package>
              <Package ID="7">
                <Service>PRIORITY COMMERCIAL</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container>$container_priority</Container>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
                <Machinable>$machinable</Machinable>
              </Package>
              <Package ID="7">
                <Service>STANDART POST</Service>
                <ZipOrigination>$origination_postal</ZipOrigination>
                <ZipDestination>$destination_postal</ZipDestination>
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <Container>$container_priority</Container>
                <Size>$package_size</Size>
                <Value>$package_cost</Value>
                $_services
                $ground_only
                <Machinable>$machinable</Machinable>
              </Package>
            </RateV4Request>
EOT;

            $get = array (
                'API' => 'RateV4',
                'XML' => $query,
            );

            $is_domestic = true;

        } else {

            // International rate calculation
            $destination_country = $this->_getCountry($destination_country);
            if (empty($destination_country)) {
                return false;
            }

            $container = empty($shipping_settings['container']) ? '' : $shipping_settings['container'];
            $intl_package_size = empty($shipping_settings['intl_package_size']) ? 'REGULAR' : $shipping_settings['intl_package_size'];

            $intl_package_width = empty($shipping_settings['intl_package_width']) ? '0' : $shipping_settings['intl_package_width'];
            $intl_package_length = empty($shipping_settings['intl_package_length']) ? '0' : $shipping_settings['intl_package_length'];
            $intl_package_height = empty($shipping_settings['intl_package_height']) ? '0' : $shipping_settings['intl_package_height'];
            $intl_package_girth = empty($shipping_settings['intl_package_girth']) ? '0' : $shipping_settings['intl_package_girth'];

            $extra_services = $this->_getExtraServices();
            $_services = array();

            foreach ($shipping_settings as $service_id => $enabled) {
                if (array_search($service_id, $extra_services['intl']) !== false && $enabled == 'Y') {
                    $_services[] = '<ExtraService>' . array_search($service_id, $extra_services['intl']) . '</ExtraService>';
                }
            }

            if (!empty($_services)) {
                $_services = '<ExtraServices>' . implode("\n", $_services) . '</ExtraServices>';
            } else {
                $_services = '';
            }

            $query=<<<EOT
            <IntlRateV2Request USERID="$username">
              <Revision>2</Revision>
              <Package ID="0">
                <Pounds>$pounds</Pounds>
                <Ounces>$ounces</Ounces>
                <MailType>$mailtype</MailType>
                <ValueOfContents>$package_cost</ValueOfContents>
                <Country>$destination_country</Country>
                <Container>$container</Container>
                <Size>$intl_package_size</Size>
                <Width>$intl_package_width</Width>
                <Length>$intl_package_length</Length>
                <Height>$intl_package_height</Height>
                <Girth>$intl_package_girth</Girth>
                <CommercialFlag>N</CommercialFlag>
                $_services
              </Package>
            </IntlRateV2Request>
EOT;

            $get = array(
                'API' => 'IntlRateV2',
                'XML' => $query,
            );
            $is_domestic = false;
        }

        $this->_is_domestic = $is_domestic;

        $request_data = array(
            'method' => 'get',
            'url' => $url,
            'data' => $get,
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
