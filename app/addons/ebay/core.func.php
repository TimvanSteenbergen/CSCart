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

// DO NOT MODIFY THIS LINE. FILE WILL BE OBFUSCATED!

use Tygh\Registry;
use Tygh\Helpdesk;
use Tygh\Session;
use Ebay\Ebay;

function fn_ebay_check_license($silent = false, $skip_messages = false)
{
    // addons.ebay.ebay_license_number
    $license_number = Registry::get(str_rot13('nqqbaf.ronl.ronl_yvprafr_ahzore'));
    $_SESSION['eauth'] = time();

    if (empty($license_number)) {
        if (!$silent) {
            // ebay_empty_license_number
            fn_set_notification('E', __('error'), __(str_rot13('ronl_rzcgl_yvprafr_ahzore')));
        }

        return false;
    } else {
        // Some HD checking code
        $data = Helpdesk::getLicenseInformation($license_number, array('edition' => 'EBAY'));
        list($license_status, , $messages) = Helpdesk::parseLicenseInformation($data, array(), false);

        if (!empty($messages) && !$skip_messages) {
            foreach ($messages as $message) {
                fn_set_notification($message['type'], $message['title'], $message['text']);
            }
        }

        if ($license_status == 'ACTIVE') {
            return 'A';

        } elseif ($license_status == '') {
            // Timeout
            fn_set_notification('E', __('error'), __('unable_to_check_license'));

            return 'T';

        } else {
            return 'I';
        }
    }
}

function fn_ebay_extend_addons()
{
    // eauth = rnhgu
    if (empty($_SESSION[str_rot13('rnhgu')])) {
        // eauth_timestamp = rnhgu_gvzrfgnzc
        $_SESSION[str_rot13('rnhgu_gvzrfgnzc')] = isset($_SESSION[str_rot13('rnhgu_gvzrfgnzc')]) ? $_SESSION[str_rot13('rnhgu_gvzrfgnzc')] + 1 : 1;

        if ($_SESSION[str_rot13('rnhgu_gvzrfgnzc')] > 10) {
            $_SESSION[str_rot13('rnhgu')] = time();

            if (fn_ebay_check_license() != 'A') {
                // ebay_addon_license_invalid
                fn_set_notification('W', __('warning'), __(str_rot13('ronl_nqqba_yvprafr_vainyvq')));

                fn_disable_addon('ebay', str_rot13('rnhgu'), false);
            }
        }
    }

    return false;
}

function fn_register_ebay_sites()
{
    $data = array(
        'timestamp' => TIME,
        'user_id' => $_SESSION['auth']['user_id'],
        'session_id' => Session::getId(),
        'status' => 'A',
        'type' => 'sites',
        'result' => '',
        'site_id' => 0
    );
    $transaction_id = db_query('INSERT INTO ?:ebay_cached_transactions ?e', $data);

    list(, $sites) = Ebay::instance()->GetEbayDetails();

    if (!empty($sites)) {
        db_query('DELETE FROM ?:ebay_sites WHERE 1');

        $data = array();
        foreach ($sites as $k => $v) {
            $data[] = array(
                'site_id' => $k,
                'site' => $v,
            );
        }
        if (!empty($data)) {
            db_query('INSERT INTO ?:ebay_sites ?m', $data);
            $data = array();
        }
        $data = array(
            'status' => 'C',
            'result' => count($sites)
        );
        db_query('UPDATE ?:ebay_cached_transactions SET ?u WHERE transaction_id = ?i', $data, $transaction_id);
    }

    return true;
}

function fn_register_ebay_shippings($site_id = 0)
{
    $data = array(
        'timestamp' => TIME,
        'user_id' => $_SESSION['auth']['user_id'],
        'session_id' => Session::getId(),
        'status' => 'A',
        'type' => 'shippings',
        'result' => '',
        'site_id' => $site_id
    );
    $transaction_id = db_query('INSERT INTO ?:ebay_cached_transactions ?e', $data);

    list(, $shippings) = Ebay::instance()->GetEbayDetails('ShippingServiceDetails');

    if (!empty($shippings)) {
        db_query('DELETE FROM ?:ebay_shippings WHERE site_id = ?i', $site_id);

        $data = array();
        foreach ($shippings as $shipping) {
            if (isset($shipping['ValidForSellingFlow']) && $shipping['ValidForSellingFlow'] == 'true') {
                $data[] = array(
                    'service_id' => (isset($shipping['ShippingServiceID'])) ? $shipping['ShippingServiceID'] : '' ,
                    'name' => (isset($shipping['ShippingService'])) ? $shipping['ShippingService'] : '',
                    'description' => (isset($shipping['Description'])) ? $shipping['Description'] : '',
                    'service_type' => (isset($shipping['ServiceType'])) ? ((is_array($shipping['ServiceType'])) ? implode(',', $shipping['ServiceType']) : $shipping['ServiceType']) : '',
                    'is_international' => (isset ($shipping['InternationalService']) && $shipping['InternationalService'] == 'true') ? 'Y' : 'N',
                    'category' => (isset($shipping['ShippingCategory'])) ? $shipping['ShippingCategory'] : '',
                    'ship_days_max' => (isset($shipping['ShippingTimeMax'])) ? $shipping['ShippingTimeMax'] : '',
                    'ship_days_min' => (isset($shipping['ShippingTimeMin'])) ? $shipping['ShippingTimeMin'] : '',
                    'package' => (isset($shipping['ShippingPackage'])) ? (is_array($shipping['ShippingPackage'])) ? implode(',', $shipping['ShippingPackage']) : $shipping['ShippingPackage'] : '',
                    'carrier' => (isset($shipping['ShippingCarrier'])) ? $shipping['ShippingCarrier'] : '',
                    'weight_required' => (isset($shipping['WeightRequired']) && $shipping['WeightRequired'] == 'true') ? 'Y' : 'N',
                    'selling_flow' => 'Y',
                    'dimensions_required' => (isset($shipping['DimensionsRequired']) && $shipping['DimensionsRequired'] == 'true') ? 'Y' : 'N',
                    'surcharge_applicable' => (isset($shipping['SurchargeApplicable']) && $shipping['SurchargeApplicable'] == 'true') ? 'Y' : 'N',
                    'expedited_service' => (isset($shipping['ExpeditedService']) && $shipping['ExpeditedService'] == 'true') ? 'Y' : 'N',
                    'detail_version' => (isset($shipping['DetailVersion'])) ? $shipping['DetailVersion'] : '',
                    'update_timestamp' => (isset($shipping['UpdateTime'])) ? strtotime($shipping['UpdateTime']) : '',
                    'site_id' => $site_id
                );
            }
        }
        if (!empty($data)) {
            db_query('INSERT INTO ?:ebay_shippings ?m', $data);
        }
        $_data = array(
            'status' => 'C',
            'result' => count($data)
        );
        db_query('UPDATE ?:ebay_cached_transactions SET ?u WHERE transaction_id = ?i', $_data, $transaction_id);
    }

    return true;
}
