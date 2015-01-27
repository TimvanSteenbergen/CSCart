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

/**
 * Reverse IP filter
 */
function fn_settings_actions_addons_access_restrictions_admin_reverse_ip_access(&$new_value, $old_value)
{
    $ip = fn_get_ip(true);

    if ($new_value == 'Y') {

        $ip_data = db_get_row("SELECT item_id, status FROM ?:access_restriction WHERE ip_from = ?i AND ip_to = ?i AND type IN ('aas', 'aab', 'aar')", $ip['host'], $ip['host']);

        if (empty($ip_data) || empty($ip_data['item_id'])) {	// Add IP
            $restrict_ip = array (
                'ip_from' => $ip['host'],
                'ip_to' => $ip['host'],
                'type' => 'aas',
                'timestamp' => TIME,
                'expires' => '0',
                'status' => 'A'
            );

            $__data = array();
            $__data['item_id'] = db_query("REPLACE INTO ?:access_restriction ?e", $restrict_ip);
            $__data['type'] = 'aas';

            foreach (fn_get_translation_languages() as $__data['lang_code'] => $_v) {
                $__data['reason'] = __('store_admin', '', $__data['lang_code']);
                db_query("REPLACE INTO ?:access_restriction_reason_descriptions ?e", $__data);
            }

            fn_set_notification('W', __('warning'), __('your_ip_added', array(
                '[ip]' => long2ip($ip['host'])
            )));

        } elseif (empty($ip_data['status']) || $ip_data['status'] != 'A') { // Change IP status to available

            db_query("UPDATE ?:access_restriction SET ?u WHERE item_id = ?i", array('status' => 'A'), $ip_data['item_id']);
            fn_set_notification('W', __('warning'), __('your_ip_enabled', array(
                '[ip]' => long2ip($ip['host'])
            )));
        }

    } else {	// Delete IP

        $ips_data = db_get_array("SELECT item_id, type FROM ?:access_restriction WHERE ip_from <= ?i AND ip_to >= ?i AND type IN ('aas', 'aab', 'aar')", $ip['host'], $ip['host']);

        if (!empty($ips_data)) {
            foreach ($ips_data as $ip_data) {
                db_query("DELETE FROM ?:access_restriction WHERE item_id = ?i", $ip_data['item_id']);
                db_query("DELETE FROM ?:access_restriction_reason_descriptions WHERE item_id = ?i AND type = ?s", $ip_data['item_id'], $ip_data['type']);
            }
            fn_set_notification('W', __('warning'), __('your_ip_removed', array(
                '[ip]' => long2ip($ip['host'])
            )));
        }

    }

    return true;
}
