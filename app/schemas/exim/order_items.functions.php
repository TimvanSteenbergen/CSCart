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

use Tygh\Registry;

/**
 * Get item extra information
 * @param array $data extra data
 * @return json-encoded data on success or empty string on failure
 */
function fn_exim_orders_get_extra($data)
{
    if (!empty($data)) {
        $data = @unserialize($data);
        return fn_exim_json_encode($data);
    }

    return '';
}

/**
 * Set extra information
 * @param array $ids item ids (order_id/item_id)
 * @param array $data data to set
 * @return bool true on success, false otherwise
 */
function fn_exim_orders_set_extra($data)
{
    $data = json_decode($data, true);

    if (!is_array($data)) {
        return '';
    }

    $data = serialize($data);

    return $data;
}

function fn_check_order_existence(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_record)
{
    $result = false;
    if ($object['order_id']) {
        $order_data = db_get_row("SELECT order_id, company_id FROM ?:orders WHERE order_id = ?i", $object['order_id']);
        if (!empty($order_data) && (Registry::get('runtime.simple_ultimate') || (!Registry::get('runtime.simple_ultimate') && (Registry::get('runtime.company_id') == $order_data['company_id'] || Registry::get('runtime.company_id') === 0)))) {
            $result = true;
        }
    }

    if (!$result) {
        $skip_record = true;
        $processed_data['S']++;
    }
}
