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

    //
    // Updating existing countries
    //
    if ($mode == 'm_update') {
        foreach ($_REQUEST['country_data'] as $key => $value) {
            db_query("UPDATE ?:countries SET ?u WHERE code = ?s", $value, $key);
            db_query("UPDATE ?:country_descriptions SET ?u WHERE code = ?s AND lang_code = ?s", $value, $key, DESCR_SL);
        }
    }

    //
    // Delete selected countries
    //
    if ($mode == 'delete') {
        if (!empty($_REQUEST['delete'])) {
            foreach ($_REQUEST['delete'] as $k => $v) {
                db_query("DELETE FROM ?:countries WHERE code = ?s", $k);
                db_query("DELETE FROM ?:country_descriptions WHERE code = ?s", $k);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, "countries.manage");
}

if ($mode == 'manage') {

    list($countries, $search) = fn_get_countries($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);
    Registry::get('view')->assign('countries', $countries);
    Registry::get('view')->assign('search', $search);
}
