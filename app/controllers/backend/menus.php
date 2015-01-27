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

use Tygh\Menu;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Update menu
    //
    if (($mode == 'update') || ($mode == 'add')) {
        if (!empty($_REQUEST['menu_data'])) {
            $_REQUEST['menu_data']['lang_code'] = DESCR_SL;
            Menu::update($_REQUEST['menu_data']);
        }
    }

    //
    // Delete menu
    //
    if ($mode == 'delete') {
        if (!empty($_REQUEST['menu_id'])) {
            Menu::delete($_REQUEST['menu_id']);
        }
    }

    return array(CONTROLLER_STATUS_OK, "menus.manage");
}

// ---------------------- GET routines ---------------------------------------

if ($mode == 'manage') {

    $menus = Menu::getList('', DESCR_SL);

    Registry::get('view')->assign('menus', $menus);

} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['menu_id'])) {
        Menu::delete($_REQUEST['menu_id']);
    }

    return array(CONTROLLER_STATUS_OK, "menus.manage");

} elseif ($mode == 'update') {
    $menu_id = isset($_REQUEST['menu_data']['menu_id']) ? $_REQUEST['menu_data']['menu_id'] : 0;

    if (!empty($_REQUEST['menu_data'])) {
        $menu_data = $_REQUEST['menu_data'];
    } else {
        $menu_data = array();
    }

    // If edit block
    if ($menu_id > 0 && empty($_REQUEST['menu_data']['content'])) {
        $menu_data = current(Menu::getList(db_quote(' AND ?:menus.menu_id=?i', $menu_id), DESCR_SL));
    }

    Registry::get('view')->assign('menu_data', $menu_data);
}
