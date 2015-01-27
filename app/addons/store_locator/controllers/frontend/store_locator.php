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

if ($mode == 'search') {
    fn_add_breadcrumb(__('store_locator'));

    list($store_locations, $search) = fn_get_store_locations($_REQUEST);

    Registry::get('view')->assign('sl_settings', fn_get_store_locator_settings());
    Registry::get('view')->assign('store_locations', $store_locations);
    Registry::get('view')->assign('store_locator_search', $search);
}
