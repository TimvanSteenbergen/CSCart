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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!empty($_REQUEST['discussion']) && !empty($_REQUEST['discussion']['object_id']) && !empty($_REQUEST['discussion']['object_type'])) {
        $discussion = fn_get_discussion($_REQUEST['discussion']['object_id'], $_REQUEST['discussion']['object_type']);
        if (!empty($discussion['thread_id'])) {
            db_query('UPDATE ?:discussion SET ?u WHERE thread_id = ?i', $_REQUEST['discussion'], $discussion['thread_id']);
        } else {
            $_data = $_REQUEST['discussion'];
            if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
                $_data['company_id'] = Registry::get('runtime.company_id');
            }
            db_query("REPLACE INTO ?:discussion ?e", $_data);
        }
    }

    return;
}
