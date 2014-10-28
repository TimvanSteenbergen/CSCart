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

if ($mode == 'results') {
    $params = $_REQUEST;
    $params['objects'] = array_keys(fn_search_get_customer_objects());

    list($search_results, $search) = fn_search($params, Registry::get('settings.Appearance.products_per_page'));

    Registry::get('view')->assign('search_results', $search_results);
    Registry::get('view')->assign('search', $search);

    fn_add_breadcrumb(__('search_results'));
}
