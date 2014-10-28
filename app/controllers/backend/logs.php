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

if ($mode == 'clean') {
    if (Registry::get('runtime.company_id')) {
        db_query('DELETE FROM ?:logs WHERE company_id = ?i', Registry::get('runtime.company_id'));
    } else {
        db_query('TRUNCATE TABLE ?:logs');
    }

    return array (CONTROLLER_STATUS_REDIRECT, "logs.manage");
}

if ($mode == 'manage') {

    list($logs, $search) = fn_get_logs($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('logs', $logs);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('log_types', fn_get_log_types());
}
