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

use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_ga_get_main_category($product_id, $lang_code = DESCR_SL)
{
    $category = '';
    if (!empty($product_id)) {
        $category = db_get_field("SELECT ?:category_descriptions.category FROM ?:category_descriptions RIGHT JOIN ?:products_categories ON ?:category_descriptions.category_id = ?:products_categories.category_id AND ?:products_categories.product_id = ?i AND link_type = 'M' WHERE lang_code = ?s", $product_id, $lang_code);
    }

    return $category;
}

function fn_ga_get_order_status_sign($order_status)
{
    $sign = '-';
    if (!empty($order_status)) {
        $paid_statuses = fn_get_order_paid_statuses();
        if (in_array($order_status, $paid_statuses)) {
            $sign = '';
        }
    }

    return $sign;
}

/**
 * Gets Google Analytics tracking code
 *
 * @param mixed $company_id Company identifier to get code for
 * @return string Google Analytics tracking code
 */
function fn_google_analytics_get_tracking_code($company_id = null)
{
    if (!fn_allowed_for('ULTIMATE')) {
        $company_id = null;
    }

    return Settings::instance()->getValue('tracking_code', 'google_analytics', $company_id);
}

function fn_google_analytics_get_order_items_info_post(&$order, &$v, &$k)
{
    $order['products'][$k]['ga_category_name'] = fn_ga_get_main_category($v['product_id'], $order['lang_code']);
}

function fn_google_analytics_change_order_status(&$status_to, &$status_from, &$order_info)
{
    if (Registry::get('addons.google_analytics.track_ecommerce') == 'N' || AREA != 'A') {
        return false;
    }

    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, true);

    if ($order_statuses[$status_to]['params']['inventory'] == 'D' && $order_statuses[$status_from]['params']['inventory'] == 'I') { // decrease amount
        fn_google_anaylitics_send(fn_google_analytics_get_tracking_code($order_info['company_id']), $order_info, false);

    } elseif ($order_statuses[$status_to]['params']['inventory'] == 'I' && $order_statuses[$status_from]['params']['inventory'] == 'D') { // increase amount

        fn_google_anaylitics_send(fn_google_analytics_get_tracking_code($order_info['company_id']), $order_info, true);

    }
}

function fn_google_anaylitics_send($account, $order_info, $refuse = false)
{
    $url = 'http://www.google-analytics.com/collect';
    $sign = ($refuse == true) ? '-' : '';

    //Common data which should be sent with any request
    $required_data = array(
        'v' => '1',
        'tid' => $account,
        'cid' => md5($order_info['email']),
        'ti' => $order_info['order_id'],
        'cu' => $order_info['secondary_currency']
    );

    $transaction = array(
        't' => 'transaction',
        'tr' => $sign . $order_info['total'],
        'ts' => $sign . $order_info['shipping_cost'],
        'tt' => $sign . $order_info['tax_subtotal'],
    );

    $result = Http::get($url, fn_array_merge($required_data, $transaction));

    foreach ($order_info['products'] as $item) {
        $item = array(
            't' => 'item',
            'in' => $item['product'],
            'ip' => fn_format_price($item['subtotal'] / $item['amount']),
            'iq' => $sign . $item['amount'],
            'ic' => $item['product_code'],
            'iv' => fn_ga_get_main_category($item['product_id'], $order_info['lang_code']),
        );
        $result = Http::get($url, fn_array_merge($required_data, $item));
    }
}
