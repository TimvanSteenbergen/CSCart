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
    $params = array(
        'product_id' => $_REQUEST['product_id'],
    );
    
    list($templates, $search) = fn_get_ebay_templates($params, 0, DESCR_SL);
    Registry::get('view')->assign('ebay_templates', $templates);
} elseif ($mode == 'm_update') {
    $field_groups = Registry::get('view')->getTemplateVars('field_groups');
    $filled_groups = Registry::get('view')->getTemplateVars('filled_groups');

    $field_names = Registry::get('view')->getTemplateVars('field_names');

    if (!empty($field_names['ebay_template_id'])) {
        $params = array();
        unset($field_names['ebay_template_id']);
        $field_groups['S']['ebay_template_id'] = array(
            'name' => 'products_data',
            'variants' => fn_get_ebay_templates($params, 0, DESCR_SL, true)
        );
        $filled_groups['S']['ebay_template_id'] = __('ebay_templates');
    }
    Registry::get('view')->assign('field_groups', $field_groups);
    Registry::get('view')->assign('filled_groups', $filled_groups);

    Registry::get('view')->assign('field_names', $field_names);
}
