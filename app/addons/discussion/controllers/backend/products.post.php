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
    if ($mode == 'update') {
        if (!empty($_REQUEST['posts'])) {
            fn_update_discussion_posts($_REQUEST['posts']);
        }
    }

    return;
}

if ($mode == 'update') {

    $discussion = fn_get_discussion($_REQUEST['product_id'], 'P', true, $_REQUEST);

    if (!empty($discussion) && $discussion['type'] != 'D') {
        if (fn_allowed_for('MULTIVENDOR') || fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            Registry::set('navigation.tabs.discussion', array (
                'title' => __('discussion_title_product'),
                'js' => true
            ));

            Registry::get('view')->assign('discussion', $discussion);
        }
    }

} elseif ($mode == 'manage') {

    $selected_fields = Registry::get('view')->getTemplateVars('selected_fields');

    $selected_fields[] = array(
        'name' => '[products_data][discussion_type]',
        'text' => __('discussion_title_product')
    );

    Registry::get('view')->assign('selected_fields', $selected_fields);

} elseif ($mode == 'm_update') {

    $selected_fields = $_SESSION['selected_fields'];

    if (!empty($selected_fields['products_data'])) {

        $field_groups = Registry::get('view')->getTemplateVars('field_groups');
        $filled_groups = Registry::get('view')->getTemplateVars('filled_groups');

        $field_groups['S']['discussion_type'] = array(
            'name' => 'products_data',
                'variants' => array (
                    'D' => 'disabled',
                    'C' => 'communication',
                    'R' => 'rating',
                    'B' => 'all'
                )
        );

        $filled_groups['S']['discussion_type'] = __('discussion_title_product');

        Registry::get('view')->assign('field_groups', $field_groups);
        Registry::get('view')->assign('filled_groups', $filled_groups);
    }
}
