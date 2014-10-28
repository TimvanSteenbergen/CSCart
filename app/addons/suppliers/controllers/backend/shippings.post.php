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

if ($mode == 'update') {
    Registry::set('navigation.tabs.suppliers', array (
        'title' => __('suppliers'),
        'js' => true
    ));

    $shipping_data = Registry::get('view')->getTemplateVars('shipping');

    list($suppliers) = fn_get_suppliers();
    $linked_suppliers = fn_get_shippings_suppliers($shipping_data['shipping_id']);

    Registry::get('view')->assign('suppliers', $suppliers);
    Registry::get('view')->assign('linked_suppliers', $linked_suppliers);
}
