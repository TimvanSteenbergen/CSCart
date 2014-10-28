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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'initiate_discussion' && !empty($_REQUEST['order_id'])) {
    $_data = array (
        'object_id' => $_REQUEST['order_id'],
        'object_type' => 'O',
        'type' => 'C'
    );

    $discussion = fn_get_discussion($_REQUEST['order_id'], 'O');
    if (!empty($discussion['thread_id'])) {
        db_query("UPDATE ?:discussion SET ?u WHERE thread_id = ?i", $_data, $discussion['thread_id']);
    } else {
        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $_data['company_id'] = Registry::get('runtime.company_id');
        }
        db_query("REPLACE INTO ?:discussion ?e", $_data);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id=$_REQUEST[order_id]&selected_section=discussion");
}
