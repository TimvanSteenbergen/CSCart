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
    return;
}

if ($mode == 'manage') {

    $selected_fields = Registry::get('view')->getTemplateVars('selected_fields');

    $selected_fields[] = array(
        'name' => '[extra][seo_name]',
        'text' => __('seo_name')
    );

    Registry::get('view')->assign('selected_fields', $selected_fields);

} elseif ($mode == 'm_update') {

    $selected_fields = $_SESSION['selected_fields'];

    if (!empty($selected_fields['extra']['seo_name'])) {

        $field_groups = Registry::get('view')->getTemplateVars('field_groups');
        $filled_groups = Registry::get('view')->getTemplateVars('filled_groups');

        $field_groups['A']['seo_name'] = 'products_data';
        $filled_groups['A']['seo_name'] = __('seo_name');

        Registry::get('view')->assign('field_groups', $field_groups);
        Registry::get('view')->assign('filled_groups', $filled_groups);
    }
}
